<?php

namespace Application\Controller\Api;

use Application\HostManager;
use Application\Hydrator\Api\ItemParentHydrator;
use Application\Model\Item;
use Application\Model\ItemParent;
use Application\Model\UserItemSubscribe;
use Application\Model\VehicleType;
use Application\Service\SpecificationsService;
use Autowp\Message\MessageService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ItemParentControllerFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ItemParentController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $hydrators = $container->get('HydratorManager');
        $filters = $container->get('InputFilterManager');
        return new ItemParentController(
            $hydrators->get(ItemParentHydrator::class),
            $filters->get('api_item_parent_list'),
            $filters->get('api_item_parent_item'),
            $filters->get('api_item_parent_post'),
            $filters->get('api_item_parent_put'),
            $container->get(ItemParent::class),
            $container->get(SpecificationsService::class),
            $container->get(HostManager::class),
            $container->get(MessageService::class),
            $container->get(UserItemSubscribe::class),
            $container->get(Item::class),
            $container->get(VehicleType::class)
        );
    }
}
