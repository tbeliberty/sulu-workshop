<?php

namespace App\Admin;

use App\Entity\Location;
use FOS\RestBundle\View\ViewHandlerInterface;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;

class LocationAdmin extends Admin
{
    const LOCATION_LIST_VIEW = 'app.location_list';
    public function __construct(
        private readonly ViewBuilderFactoryInterface $viewBuilderFactory
    ){}

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        $locationNavItem = new NavigationItem('app.location');
        $locationNavItem->setView(static::LOCATION_LIST_VIEW);
        $navigationItemCollection->add($locationNavItem);

    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        $listView = $this->viewBuilderFactory->createListViewBuilder(static::LOCATION_LIST_VIEW, '/locations')
            ->setResourceKey(Location::RESOURCE_KEY)
            ->setListKey('location')
            ->addListAdapters(['table']);

        $viewCollection->add($listView);
    }

}
