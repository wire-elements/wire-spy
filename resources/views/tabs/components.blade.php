<div x-show="activeTab === 'Components'" class="flex w-full">
    <div class="border-r border-gray-800 w-[300px] shrink-0 p-4 overflow-y-auto">
        <ul>
            <template x-for="(component, index) in getComponents()">
                <li
                        @click="setActiveComponent(component.snapshot.memo.id)"
                        :class="activeComponent('snapshot.memo.id') == component.snapshot.memo.id ? 'border-indigo-500 rounded' : 'border-transparent'"
                        class="cursor-pointer transition-colors p-2 border">
                    <h4
                            class="text-sm"
                            x-bind:class="{ 'text-white': activeComponent('snapshot.memo.id') == component.snapshot.memo.id }">
                        &lt;<span x-text="component.snapshot.memo.name" ></span>&gt;
                    </h4>

                    <div class="text-xs text-slate-500"
                         x-data="{ commit: () => getLastComponentCommitById(component.snapshot.memo.id) }"
                         x-text="commit().duration
                                            ? `Response time: ${commit().duration}ms - Payload: ${commit().size}kb`
                                            : 'Initial render'">
                    </div>
                </li>
            </template>
        </ul>
    </div>

    <div class="w-full grid grid-cols-3">
        <div x-show="!activeComponentId" class="flex text-sm col-span-3 justify-center items-center text-slate-500">
            Select component
        </div>

        <div x-show="activeComponentId" class="border-r border-gray-800 col-span-1 overflow-y-auto overflow-x-hidden relative">
            <h2 class="font-bold mb-4 border-b border-gray-800 px-4 py-2 text-sm sticky top-0 z-10 w-full bg-zinc-900/90 backdrop-blur-sm">Component data</h2>
            <div class="px-4" x-ref="editor"></div>
        </div>

        <div x-show="activeComponentId" class="flow-root col-span-2 overflow-y-auto overflow-x-hidden relative">
            <h2 class="font-bold border-b border-gray-800 px-4 py-2 text-sm sticky top-0 z-10 w-full bg-zinc-900/90 backdrop-blur-sm">Component updates</h2>
            <div class="-my-2 overflow-x-auto">
                <div class="inline-block min-w-full py-2 align-middle">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead>
                        <tr>
                            <th scope="col" class="py-2 pl-4 pr-3 text-left text-sm font-semibold">Updates</th>
                            <th scope="col" class="px-3 py-2 text-left text-sm font-semibold">Calls</th>
                            <th scope="col" class="px-3 py-2 text-left text-sm font-semibold">Size</th>
                            <th scope="col" class="px-3 py-2 text-left text-sm font-semibold">Duration</th>
                            <th scope="col" class="py-2 pl-3 pr-4 text-right text-sm">
                                Time Travel
                            </th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                        <template x-for="(commit in activeComponentCommits()" :key="commit.id">
                            <tr>
                                <td x-show="commit.initial" colspan="4" class="whitespace-nowrap py-4 pl-4 pr-3 text-xs font-medium text-white">
                                    Initial state
                                </td>
                                <td x-show="!commit.initial" class="whitespace-nowrap align-top py-4 pl-4 pr-3 font-medium text-white">
                                    <div x-data="wireSpyJsonViewer(commit.updates)"></div>
                                </td>
                                <td x-show="!commit.initial" class="whitespace-nowrap align-top px-3 py-4 text-gray-300">
                                    <div x-data="wireSpyJsonViewer(commit.calls)"></div>
                                </td>
                                <td x-show="!commit.initial" class="whitespace-nowrap px-3 py-4 text-xs text-gray-300" x-text="commit.size ? commit.size + 'kb' : '-'"></td>
                                <td x-show="!commit.initial" class="whitespace-nowrap px-3 py-4 text-xs text-gray-300" x-text="commit.duration ? commit.duration + 'ms' : '-'"></td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-xs font-medium">
                                    <button class="transition-colors" :class="{ 'text-indigo-500': commit.current }" @click="travel(commit.id)">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>