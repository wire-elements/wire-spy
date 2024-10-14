<?php

return [
    /*
     * Enable or disable WireSpy.
     */
    'enabled' => env('WIRESPY_ENABLED', true),
    
    /**
    * The keybinding configuration option allows you to define a keyboard shortcut
    * using AlpineJS syntax. It accepts a string representing the desired key combination.
    *
    * Syntax:
    * - 'super' corresponds to the 'Cmd' key on macOS and the 'Ctrl' key on Windows/Linux.
    * - Combine with other keys using dot notation, like 'super.l' for 'Cmd+L' or 'Ctrl+L'.
    */
    'keybinding' => 'super.l',
];
