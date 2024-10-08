<?php

namespace WireElements\WireSpy;

use Livewire\ComponentHook;

class SupportProfilling extends ComponentHook
{
    public function dehydrate($context)
    {
        $context->addMemo('wirespy', true);
    }
}
