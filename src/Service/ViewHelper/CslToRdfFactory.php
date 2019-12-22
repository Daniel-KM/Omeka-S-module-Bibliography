<?php
namespace Bibliography\Service\ViewHelper;

use Bibliography\View\Helper\CslToRdf;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class CslToRdfFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $api = $services->get('Omeka\ApiManager');

        $propertyIds = [];
        $properties = $api->search('properties', [], ['responseContent' => 'resource'])->getContent();
        foreach ($properties as $property) {
            $term = $property->getVocabulary()->getPrefix() . ':' . $property->getLocalName();
            $propertyIds[$term] = $property->getId();
        }

        $resourceClassIds = [];
        $resourceClasses = $api->search('resource_classes', [], ['responseContent' => 'resource'])->getContent();
        foreach ($resourceClasses as $resourceClass) {
            $term = $resourceClass->getVocabulary()->getPrefix() . ':' . $resourceClass->getLocalName();
            $resourceClassIds[$term] = $resourceClass->getId();
        }

        return new CslToRdf($propertyIds, $resourceClassIds);
    }
}
