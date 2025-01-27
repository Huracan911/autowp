<?php

namespace Application\Validator\Attr;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class UnitIdFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return UnitId
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new UnitId(array_replace(is_array($options) ? $options : [], [
            'table' => $container->get('TableManager')->get('attrs_units')
        ]));
    }
}
