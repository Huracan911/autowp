<?php

namespace Application\View\Helper\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use Application\Hydrator\Api\UserHydrator;
use Application\View\Helper\ApiData as Helper;

class ApiDataFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Helper
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $hydrators = $container->get('HydratorManager');
        $config = $container->get('Config');
        return new Helper(
            $hydrators->get(UserHydrator::class),
            $config['rollbar']
        );
    }
}
