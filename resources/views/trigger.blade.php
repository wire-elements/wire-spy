<button
    x-data
    @click="$dispatch('wire-spy-toggle')"
    style="
        position: fixed;
        bottom: 16px;
        right: 16px;
        z-index: 99999999;

        display: flex;
        align-items: center;
        justify-content: center;

        width: 40px;
        height: 40px;

        border-radius: 9999px;
        background: #18181b;
        color: #e5e7eb;

        font-size: 18px;
        line-height: 1;

        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0,0,0,.4);
    "
    title="Toggle WireSpy"
>
    🐞
</button>
