<div class="grow-0">
    <div class="sm:block relative">
        <div class="absolute top-0 left-0 h-1 flex justify-center w-full group select-none cursor-row-resize"
             @mousedown="startResize"
             @mouseup="stopResize"
        >
            <div class="absolute top-2 bg-gray-800/90 transition-colors group-hover:bg-gray-700 h-1 w-14 rounded-full"></div>
        </div>

        <div class="border-b border-gray-800 px-4 flex justify-between">
            <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                <template x-for="(tab, index) in tabs">
                    <button @click="activeTab = tab"
                            x-text="tab"
                            :class="{ '!ml-2': index === 0, 'text-indigo-500 border-indigo-500 text-indigo-500': activeTab == tab, 'border-transparent text-slate-500': activeTab != tab }"
                            class="whitespace-nowrap transition-colors border-b px-1 py-2 text-sm font-medium"></button>
                </template>
            </nav>

            <div class="flex gap-3">
                <button title="Hot reload" class="px-1 py-2" :class="hotReload ? 'text-indigo-500 animate-spin' : ''" @click="hotReload = !hotReload">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                </button>
                <button class="px-1 py-2" @click="show = false">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>