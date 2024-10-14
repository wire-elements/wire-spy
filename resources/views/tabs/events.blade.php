<div x-show="activeTab === 'Events'" class="flex flex-1 overflow-y-auto">
    <div x-show="!getEvents().length" class="flex w-full justify-center items-center text-slate-500 text-sm">
            You don't have any events yet
    </div>
    <div x-show="getEvents().length" class="w-full">
        <div class="inline-block min-w-full py-2 align-middle">
            <table class="min-w-full divide-y divide-gray-700">
                <thead>
                <tr>
                    <th scope="col" class="py-2 pl-4 pr-3 text-left text-sm font-semibold">Name</th>
                    <th scope="col" class="py-2 pl-4 pr-3 text-left text-sm font-semibold">Dispatched by</th>
                    <th scope="col" class="px-3 py-2 text-left text-sm font-semibold">Params</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                <template x-for="event in getEvents()" :key="event.id">
                    <tr>
                        <td class="whitespace-nowrap align-top py-4 pl-4 pr-3 text-sm text-white" x-text="event.name"></td>
                        <td class="whitespace-nowrap align-top py-4 pl-4 pr-3 text-sm text-white">
                            <button class="flex items-center gap-3"
                                    x-show="event.component.snapshot.memo.name"
                                    x-data="wireSpyComponentHighlighter(event.component.snapshot.memo.id)">
                                <span x-text="event.component.snapshot.memo.name"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </button>
                        </td>
                        <td class="whitespace-nowrap align-top px-3 py-4 text-gray-300">
                            <div x-data="wireSpyJsonViewer(event.params)"></div>
                        </td>
                    </tr>
                </template>
                </tbody>
            </table>
        </div>
    </div>
</div>