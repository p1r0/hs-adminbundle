<?php
namespace Heapstersoft\Base\AdminBundle\Menu;

//use Acme\DemoBundle\MenuEvents;
use Heapstersoft\Base\AdminBundle\Event\ConfigureMenuEvent;
use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class MainBuilder extends ContainerAware
{
    public function build(FactoryInterface $factory)
    {
        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav');
        $menu->setCurrent($this->container->get('request')->getRequestUri());
        $menu->addChild('Dashboard', array('route' => '_admin'));

        $this->container->get('event_dispatcher')->dispatch(ConfigureMenuEvent::CONFIGURE, new ConfigureMenuEvent($factory, $menu));

        return $menu;
    }
}