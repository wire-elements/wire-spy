<div x-show="activeTab === 'Hot Reload'" class="flex flex-1 overflow-y-auto">
    <div x-show="!hotReload" class="flex w-full justify-center items-center">
        <button @click="hotReload = !hotReload" class="flex justify-center items-center gap-2 rounded transition bg-white/5 shadow-sm hover:bg-white/10 hover:text-gray-100 px-2 py-1 text-gray-400 text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>

            Enable hot reload
        </button>
    </div>
    <div x-show="hotReload" class="inline-block min-w-full py-2 align-middle">
        <table class="min-w-full divide-y divide-gray-700">
            <thead>
            <tr>
                <th scope="col" class="py-2 pl-4 pr-3 text-left text-sm font-semibold">Changed File</th>
                <th scope="col" class="py-2 pl-4 pr-3 text-left text-sm font-semibold">Reloaded Components</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
            <template x-for="event in hotReloadEvents.reverse()" :key="index">
                <tr>
                    <td class="whitespace-nowrap align-top py-4 pl-4 pr-3 text-xs text-gray-300" x-text="event.file"></td>
                    <td class="whitespace-nowrap align-top px-3 py-4 text-xs text-gray-300">
                        <div x-text="event.components.map(component => `<${component}>`).join(', ')"></div>
                    </td>
                </tr>
            </template>
            </tbody>
        </table>
    </div>
</div>