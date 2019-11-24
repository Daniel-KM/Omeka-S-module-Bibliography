<?php
namespace Bibliography\Service;

use Bibliography\DataType\Doi\Doi;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class DoiDataTypeFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new Doi($services);
    }
}
