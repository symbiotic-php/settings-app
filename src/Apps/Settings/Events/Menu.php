<?php


namespace Symbiotic\Apps\Settings\Events;


class Menu
{
    public function handle(\Symbiotic\UIBackend\Events\MainSidebar $menu)
    {
        $menu->addItem('Settings', \_S\route('backend:settings::index'),' <i class="ti-settings"></i>');
    }
}