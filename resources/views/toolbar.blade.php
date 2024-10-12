<div x-data="wireSpy" x-on:keydown.window.prevent.ctrl.l="show = !show" x-on:keydown.window.prevent.super.l="show = !show" x-cloak :style="show ? `height: ${height}px;` : `height: 0`;" :class="isResizing ? '' : 'transition-all'" class="font-sans antialiased fixed z-[99999999] flex flex-col inset left-0 bottom-0 w-full bg-zinc-900 rounded-t-lg text-gray-300">
    @include('wire-spy::navbar')

    <div class="flex flex-1 overflow-hidden">
        @include('wire-spy::tabs.components')
        @include('wire-spy::tabs.requests')
        @include('wire-spy::tabs.events')
        @include('wire-spy::tabs.hot-reload')
    </div>

    <div x-ref="overlay" x-show="highlightComponentId" class="transition" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); backdrop-filter: blur(5px); z-index: 99999997;"></div>
</div>