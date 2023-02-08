<?php

namespace App\Controller\Admin;

use App\Entity\Location;
use Exception;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use HandcraftedInTheAlps\RestRoutingBundle\Controller\Annotations\RouteResource;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactoryInterface;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;
use Sulu\Component\Rest\RestHelperInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @RouteResource("location")
 */
class LocationController implements ClassResourceInterface
{
    /**
     * @param ViewHandlerInterface $viewHandler
     * @param FieldDescriptorFactoryInterface $fieldDescriptorFactory
     * @param DoctrineListBuilderFactoryInterface $listBuilderFactory
     * @param RestHelperInterface $restHelper
     */
    public function __construct(
      private readonly ViewHandlerInterface $viewHandler,
      private readonly FieldDescriptorFactoryInterface $fieldDescriptorFactory,
      private readonly DoctrineListBuilderFactoryInterface $listBuilderFactory,
      private readonly RestHelperInterface $restHelper
    ){}

    /**
     * @throws Exception
     */
    public function cgetAction(): Response
    {
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors(Location::RESOURCE_KEY);
        if ($fieldDescriptors === null) {
            throw new Exception('Field descriptors not found.');
        }
        $listBuilder = $this->listBuilderFactory->create(Location::class);
        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        $listRepresentation = new PaginatedRepresentation(
            $listBuilder->execute(),
            Location::RESOURCE_KEY,
            $listBuilder->getCurrentPage(),
            $listBuilder->getLimit(),
            $listBuilder->count()
        );

        return $this->viewHandler->handle(View::create($listRepresentation));
    }
}
