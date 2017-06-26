<?php

namespace Application\Controller\Api\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use Application\Controller\Api\ItemParentController as Controller;

class ItemParentControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $hydrators = $container->get('HydratorManager');
        $filters = $container->get('InputFilterManager');
        return new Controller(
            $hydrators->get(\Application\Hydrator\Api\ItemParentHydrator::class),
            $filters->get('api_item_parent_list'),
            $filters->get('api_item_parent_item'),
            $filters->get('api_item_parent_post'),
            $filters->get('api_item_parent_put'),
            $container->get(\Application\Model\BrandVehicle::class),
            $container->get(\Application\Service\SpecificationsService::class),
            $container->get(\Application\HostManager::class),
            $container->get(\Autowp\Message\MessageService::class)
        );
    }
}
