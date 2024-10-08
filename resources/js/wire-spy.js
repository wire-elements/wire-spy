import JsonEditor from '@redgoose/json-editor'

document.addEventListener('alpine:init', () => {

    const canTransition = 'startViewTransition' in document

    Alpine.data('wireSpy', function() {
        return {
            show: this.$persist(true),

            tabs: ['Components', 'Events', 'Hot Reload'],
            activeTab: this.$persist('Components'),

            commits: [],
            activeComponentId: null,
            activeComponentCommitId: null,
            jsonEditor: null,

            hotReload: this.$persist(false),
            hotReloadEvents: [],
            hotReloadEventSource: null,

            highlightComponentId: null,

            height: this.$persist(400),
            initialY: 0,
            initialHeight: 0,
            isResizing: false,

            init() {
                this.registerEditor()
                this.storeInitialCommit()
                this.registerKeybindings()
                this.registerCommitListener()
                this.registerHotReloadHandler()
            },

            /**
             * Get all Livewire components on the page except for 'wire-spy'.
             *
             * @return {array}
             */
            getComponents() {
                return Livewire.all().filter(component => component.name !== 'wire-spy')
            },

            /**
             * Get a Livewire component by its identifier.
             *
             * @param {string} componentId The Livewire component identifier.
             * @return {component} The Livewire component object
             */
            getComponentById(componentId) {
                return this.getComponents().find(component => component.snapshot.memo.id === componentId)
            },

            /**
             * Track which component is active within the components tab.
             *
             * @param {string} componentId The Livewire component identifier.
             * @return void
             */
            setActiveComponent(componentId) {
                // We use the transition callback to add smooth transitions if supported by the browser.
                this.transition(() => {
                    // Set the active component id.
                    this.activeComponentId = componentId

                    // Update the json editor with the snapshot data for the newly chosen component.
                    window.jsonEditor.replace(this.activeComponent('snapshot.data'), null, false)
                })
            },

            /**
             * Return the current active component or property
             *
             * @param {?string} path The property path to get.
             * @return {string|component} Return the entire component object or the request path.
             */
            activeComponent(path = null) {
                let component = this.getComponents().find(component => component.snapshot.memo.id === this.activeComponentId)

                return path ? this.getter(component, path) : component
            },


            /**
             * Get all Events that have been dispatched by the Livewire components.
             *
             * @return {array}
             */
            getEvents() {
                // We start by grabbing out all commits that have dispatched events.
                return this.commits.filter(commit => commit.effects?.dispatches)
                    // Next we map over each commit and the component object for easy reference.
                    .map(commit => commit.effects.dispatches.map(dispatch => ({
                        ...dispatch,
                        commitId: commit.id,
                        component: this.getComponentById(commit.componentId),
                    })))
                    // Finally we flatten the array to make it easier to loop over in the template.
                    .flat()
                    .reverse()
            },

            /**
             * Get all commits for the active Livewire component.
             *
             * @return {array}
             */
            activeComponentCommits() {
                return this.getComponentCommitsById(this.activeComponentId)
            },

            /**
             * Get all commits that have occurred.
             *
             * @return {array}
             */
            getComponentCommitsById(componentId) {
                return this.commits.filter(commit => commit.componentId === componentId).reverse()
            },

            /**
             * Get the latest commit for a given Livewire component.
             *
             * @return {?commit}
             */
            getLastComponentCommitById(componentId) {
                return this.getComponentCommitsById(componentId)[0] ?? null
            },

            /**
             * Get the active commit (current state) for a given Livewire component.
             *
             * @return {?commit}
             */
            getComponentActiveCommit(componentId) {
                let commits = this.getComponentCommitsById(componentId)

                return commits.find(commit => commit.current === true) ?? null
            },

            /**
             * Highlight a given Livewire component on the page.
             *
             * @return void
             */
            highlightComponent(componentId) {
                // Let's move the overlay outside the Livewire component to ensure it's not contained.
                document.body.appendChild(this.$refs.overlay)

                // Keep track of the component that is highlighted.
                this.highlightComponentId = componentId

                // Get the Livewire component element that should be excluded from the overlay.
                const excludedElement = this.getComponentById(componentId).el

                if (excludedElement) {
                    // Let's move the component above the overlay using a relative position and a higher index.
                    excludedElement.style.position = 'relative'
                    excludedElement.style.zIndex = '99999998'
                }
            },

            /**
             * Unhighlight the currently highlighted Livewire component.
             *
             * @return void
             */
            unhighlightComponent() {
                this.highlightComponentId = null
            },

            /**
             * Travel to a specific commit for the active Livewire component.
             *
             * @param {string} commitId The commit to time travel to.
             * @return void
             */
            travel(commitId) {
                // Let's find the commit we want to travel to.
                let commit = this.commits.find(commit => commit.id === commitId)

                // Next, we will merge the snapshot of the commit into the Livewire component.
                this.activeComponent().mergeNewSnapshot(commit.snapshot, commit.effects, commit.updates)

                this.transition(() => {
                    // Let's make sure the front-end state is updated as well.
                    for (const [key, value] of Object.entries(JSON.parse(commit.snapshot).data)) {
                        this.activeComponent().$wire.$set(key, value, false)
                    }

                    // Next, we will process the effects of the commit (triggers dom changes).
                    this.activeComponent().processEffects(this.activeComponent().effects)

                    // And update the JSON editor with the new snapshot data.
                    this.updateEditorState(this.activeComponent('snapshot.data'))
                })

                // Finally, we will mark the commit as the current one.
                this.setComponentCurrentCommit(commit.id)
            },

            updateEditorState(json) {
                window.jsonEditor.replace(json, null, false)
            },

            /**
             * Register the hot reload handler.
             *
             * @return void
             */
            registerHotReloadHandler() {
                // Start hot reloading automatically if persisted value is true
                if(this.hotReload) {
                    this.createEventSource()
                }

                // Let's watch the hot reload property and start the event source if it's enabled.
                this.$watch('hotReload', (enable) => {
                    if(enable) {
                        this.createEventSource()
                    } else {
                        this.hotReloadEventSource.close()
                    }
                })
            },

            createEventSource() {
                // Let's create a new event source to listen for hot reload events.
                this.hotReloadEventSource = new EventSource("/wire-spy/hot-reload")

                // When we receive a message from the event source we will process the data.
                this.hotReloadEventSource.onmessage = (event) => {
                    const data = JSON.parse(event.data)

                    // If the event contains components we will refresh them.
                    if(data.components) {
                        let pageComponents = this.getComponents()

                        // Loop over each component and refresh them if they are part of the hot reload event.
                        data.components.forEach((component) => {
                            pageComponents.filter(c => c.snapshot.memo.name === component)
                                .forEach((c) => {
                                    // We will add the hot reload event to the list of events.
                                    this.hotReloadEvents.push({
                                        file: data.file,
                                        components: data.components
                                    })

                                    // Refresh the component while maintaining the current state.
                                    c.$wire.$refresh()
                                })
                        })
                    }
                }

                // If the event source encounters an error we will close it and disable hot reload.
                this.hotReloadEventSource.onerror = (e) => {
                    if (e.readyState === EventSource.CLOSED) {
                        this.hotReloadEventSource.close();
                        this.hotReload = false;
                    }
                }
            },

            /**
             * Store the initial commit for all Livewire components on the page.
             *
             * @return void
             */
            storeInitialCommit() {
                this.getComponents().forEach(component => {
                    this.commits.push({
                        id: this.randomId(),
                        initial: true,
                        current: true,
                        componentId: component.snapshot.memo.id,
                        calls: null,
                        size: null,
                        snapshot: component.snapshotEncoded,
                        effects: {
                            returns: [],
                            html: component.el.outerHTML.toString(),
                        },
                        updates: null,
                        duration: null,
                    })
                })
            },

            /**
             * Register the commit listener to track all Livewire commits.
             *
             * @return void
             */
            registerCommitListener() {
                Livewire.hook('commit', ({ component, commit, succeed }) => {
                    // Start measuring commit request duration
                    const commitStart = performance.now()

                    // If the commit succeeded we will store the snapshot and effects.
                    succeed(({ snapshot, effects }) => {
                        // Calculate the snapshot and effect size
                        let size =  Math.round(new Blob([JSON.stringify({ snapshot, effects })]).size / 1024 * 100) / 100

                        // Measure when request was completed
                        const commitEnd = performance.now()

                        // Generate a random id for tracking
                        const commitId = this.randomId()

                        // Next we add the current commit and mark it as the current one
                        this.commits.push({
                            id: commitId,
                            initial: false,
                            current: false,
                            componentId: component.snapshot.memo.id,
                            calls: commit.calls,
                            size: size,
                            snapshot: snapshot,
                            effects: effects,
                            updates: commit.updates,
                            duration: Math.round(commitEnd - commitStart),
                        })

                        // Refresh json editor state if a commit occurred for the current active component
                        if(component.snapshot.memo.id === this.activeComponentId) {
                            this.updateEditorState(this.activeComponent('snapshot.data'))
                        }

                        // Mark the commit as current
                        this.setComponentCurrentCommit(commitId)
                    })
                })
            },

            /**
             * Register the toolbar keybindings.
             *
             * @return void
             */
            registerKeybindings() {
                this.tabs.forEach((tab, index) => {
                    window.addEventListener('keydown', (e) => {
                        if (e.key === (index+1).toString() && e.metaKey && this.show) {
                            e.preventDefault()
                            this.activeTab = tab
                        }
                    });
                })
            },

            /**
             * Register the commit listener to track all Livewire commits.
             *
             * @param {string} commitId The commit that should be marked as current.
             * @return void
             */
            setComponentCurrentCommit(commitId) {
                // Find the commit
                let commit = this.commits.find(commit => commit.id === commitId)

                // Lookup the associated component
                let componentId = commit.componentId

                // Mark the commit as current
                this.commits.forEach(commit => {
                    if(commit.componentId === componentId) {
                        commit.current = commit.id === commitId
                    }
                })
            },

            /**
             * Register the JSON editor.
             *
             * @return void
             */
            registerEditor() {
                // Binding to window object as binding to component instance results into problems.
                window.jsonEditor = new JsonEditor(this.$refs.editor, {
                    live: true,
                    theme: 'dark',
                    edit: 'all',
                    node: {},
                    openDepth: 2,
                })

                // Add event listener to monitor any change that are made
                window.jsonEditor.el.wrap.get(0).addEventListener('update', ({detail}) => {
                    // If the snapshot data is the same as the current component data we will skip the update.
                    if (JSON.stringify(this.activeComponent('snapshot.data')) === JSON.stringify(detail)) {
                        return
                    }

                    // We will update the component data with the new snapshot data.
                    for (const [key, value] of Object.entries(detail)) {
                        // We will only update the component data if it's different from the current value.
                        if(this.activeComponent().$wire.$get(key) !== value) {
                            // We will update the component data and trigger a re-render.
                            this.activeComponent().$wire.$set(key, value)
                        }
                    }
                })
            },

            /**
             * Transition to a new view if supported by the browser.
             *
             * @param {callback} callback The callback to execute after the transition has completed.
             * @return void
             */
            transition(callback) {
                if (canTransition) {
                    document.startViewTransition(callback)
                    return
                }

                callback()
            },

            /**
             * Start resizing the toolbar.
             *
             * @param {event} e The event object
             * @return void
             */
            startResize(e) {
                this.isResizing = true
                this.initialY = e.clientY
                this.initialHeight = this.height

                window.addEventListener('pointermove', (e) => this.resize(e))
            },

            /**
             * Resize the toolbar height.
             *
             * @param {event} e The event object
             * @return void
             */
            resize(e) {
                if (!this.isResizing) return
                const dy = this.initialY - e.clientY
                this.height = Math.max(50, this.initialHeight + dy)
            },

            /**
             * Stop resizing the toolbar.
             *
             * @return void
             */
            stopResize() {
                this.isResizing = false
                window.removeEventListener('pointermove', (e) => this.resize(e))
            },

            /**
             * Get a nested property from an object.
             *
             * @return void
             */
            getter(obj, path) {
                const keys = path.split('.')
                let result = obj

                for (let key of keys) {
                    if (result == null || typeof result !== 'object') {
                        return undefined
                    }
                    result = result[key]
                }

                return result
            },

            /**
             * Generate a random id
             *
             * @return string
             */
            randomId() {
                return Math.random().toString(36).substring(7);
            }
        }
    })

    Alpine.data('wireSpyComponentHighlighter', (componentId) => ({
        init() {
            Alpine.bind(this.$el, {
                'x-on:mouseover': () => {
                    this.highlightComponent(componentId)
                },
                'x-on:mouseleave': () => {
                    this.unhighlightComponent(componentId)
                },
            });
        }
    }))

    Alpine.data('wireSpyJsonViewer', (json) => ({
        init() {
            new JsonEditor(this.$el, {
                live: false,
                theme: 'dark',
                edit: 'none',
                node: json,
                openDepth: 2,
            })
        }
    }))
})
