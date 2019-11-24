<?php
namespace Bibliography\Service;

use Bibliography\DataType\OpenLibrary\OpenLibrary;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class OpenLibraryDataTypeFactory implements FactoryInterface
{
    protected $types = [
        'valuesuggest:isbn:id' => [
            'label' => 'ISBN: International standard book number (id)', // @translate
            'resource' => 'ISBN',
            'identifier' => true,
        ],
        'valuesuggest:lccn:id' => [
            'label' => 'LCCN: Library of Congress Control Number (id)', // @translate
            'resource' => 'LCCN',
            'identifier' => true,
        ],
        'valuesuggest:oclc:id' => [
            'label' => 'OCLC: Online computer library center (id)', // @translate
            'resource' => 'OCLC',
            'identifier' => true,
        ],
        'valuesuggest:olid:id' => [
            'label' => 'OLID: Open library id from Internet Archive (id)', // @translate
            'resource' => 'OLID',
            'identifier' => true,
        ],
    ];

    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $dataType = new OpenLibrary($services);
        return $dataType
            ->setName($requestedName)
            ->setLabel($this->types[$requestedName]['label'])
            ->setResource($this->types[$requestedName]['resource'])
            ->setIdentifier($this->types[$requestedName]['identifier'])
        ;
    }
}
