<?php

namespace Application\Validator\Attr;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class TypeIdFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return TypeId
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new TypeId(array_replace(is_array($options) ? $options : [], [
            'table' => $container->get('TableManager')->get('attrs_types')
        ]));
    }
}
