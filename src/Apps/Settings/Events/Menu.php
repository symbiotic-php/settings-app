<?php


namespace Symbiotic\Apps\Settings\Events;


use Symbiotic\Routing\UrlGeneratorInterface;

class Menu
{
    public function __construct(protected UrlGeneratorInterface $url)
    {
    }

    public function handle(\Symbiotic\UIBackend\Events\MainSidebar $menu)
    {
        $menu->addItem('settings::Settings', $this->url->adminRoute('settings::index'),' <i class="ti-settings"></i>');
    }
}