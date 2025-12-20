<?php

return [
    /*
     * Enable or disable WireSpy.
     * By default, WireSpy will only be enabled in your development environment.
     */
    'enabled' => env('WIRE_SPY_ENABLED'),

    /**
     * The keybinding configuration option allows you to define a keyboard shortcut
     * using AlpineJS syntax. It accepts a string representing the desired key combination.
     *
     * Syntax:
     * - 'super' corresponds to the 'Cmd' key on macOS and the 'Ctrl' key on Windows/Linux.
     * - Combine with other keys using dot notation, like 'super.l' for 'Cmd+L' or 'Ctrl+L'.
     */
    'keybinding' => 'super.l',


    /**
     * By default WireSpy can only be triggered by keyboard shortcuts.
     * If you need to trigger the panel's visibility by a button either set the WIRE_SPY_BUTTON_ENABLED to true
     * in your .env file or publish the config file and set the value to true.
     */
    'button_enabled' => env('WIRE_SPY_BUTTON_ENABLED', false),
];
