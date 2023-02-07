<?php

namespace App\Admin;

use App\Entity\EventRegistrations;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;

class EventRegistrationAdmin extends Admin
{
    const EVENT_REGISTRATION_LIST_VIEW = 'app.event_registration_list';
    public function __construct(private readonly ViewBuilderFactoryInterface $viewBuilderFactory)
    {
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        // add navigation items
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        $listView = $this->viewBuilderFactory->createListViewBuilder(static::EVENT_REGISTRATION_LIST_VIEW, '/events-registrations')
            ->setResourceKey(EventRegistrations::RESOURCE_KEY)
            ->setListKey('event_registrations')
            ->addListAdapters(['table']);
        $viewCollection->add($listView);
    }

}
