<?php

namespace Application\Controller\Api;

use Application\Hydrator\Api\ItemParentLanguageHydrator;
use Application\Model\ItemParent;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ItemParentLanguageControllerFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ItemParentLanguageController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $tables = $container->get('TableManager');
        $hydrators = $container->get('HydratorManager');
        $filters = $container->get('InputFilterManager');

        return new ItemParentLanguageController(
            $tables->get('item_parent_language'),
            $hydrators->get(ItemParentLanguageHydrator::class),
            $container->get(ItemParent::class),
            $filters->get('api_item_parent_language_put')
        );
    }
}
