<?php
namespace Bibliography\Service;

use Bibliography\DataType\OpenLibrary\OpenLibrary;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class OpenLibraryDataTypeFactory implements FactoryInterface
{
    protected $types = [
        'valuesuggest:isbn:id' => [
            'label' => 'ISBN: International standard book number (by id)', // @translate
            'options' => [
                'resource' => 'ISBN',
                'identifier' => true,
                'uri_label' => 'id',
            ],
        ],
        'valuesuggest:isbn:id:name' => [
            'label' => 'ISBN: International standard book number (by id, name)', // @translate
            'options' => [
                'resource' => 'ISBN',
                'identifier' => true,
                'uri_label' => 'name',
            ],
        ],
        'valuesuggest:isbn:id:reference' => [
            'label' => 'ISBN: International standard book number (by id, reference)', // @translate
            'options' => [
                'resource' => 'ISBN',
                'identifier' => true,
                'uri_label' => 'reference',
            ],
        ],
        'valuesuggest:isbn:id:record' => [
            'label' => 'ISBN: International standard book number (collected record)', // @translate
            'options' => [
                'resource' => 'ISBN',
                'identifier' => true,
                'uri_label' => 'record',
            ],
        ],

        'valuesuggest:lccn:id' => [
            'label' => 'LCCN: Library of Congress Control Number (by id)', // @translate
            'options' => [
                'resource' => 'LCCN',
                'identifier' => true,
                'uri_label' => 'id',
            ],
        ],
        'valuesuggest:lccn:id:name' => [
            'label' => 'LCCN: Library of Congress Control Number (by id, name)', // @translate
            'options' => [
                'resource' => 'LCCN',
                'identifier' => true,
                'uri_label' => 'name',
            ],
        ],
        'valuesuggest:lccn:id:reference' => [
            'label' => 'LCCN: Library of Congress Control Number (by id, reference)', // @translate
            'options' => [
                'resource' => 'LCCN',
                'identifier' => true,
                'uri_label' => 'reference',
            ],
        ],
        'valuesuggest:lccn:id:record' => [
            'label' => 'LCCN: Library of Congress Control Number (collected record)', // @translate
            'options' => [
                'resource' => 'LCCN',
                'identifier' => true,
                'uri_label' => 'record',
            ],
        ],

        'valuesuggest:oclc:id' => [
            'label' => 'OCLC: Online computer library center (by id)', // @translate
            'options' => [
                'resource' => 'OCLC',
                'identifier' => true,
                'uri_label' => 'id',
            ],
        ],
        'valuesuggest:oclc:id:name' => [
            'label' => 'OCLC: Online computer library center (by id, name)', // @translate
            'options' => [
                'resource' => 'OCLC',
                'identifier' => true,
                'uri_label' => 'name',
            ],
        ],
        'valuesuggest:oclc:id:reference' => [
            'label' => 'OCLC: Online computer library center (by id, reference)', // @translate
            'options' => [
                'resource' => 'OCLC',
                'identifier' => true,
                'uri_label' => 'reference',
            ],
        ],
        'valuesuggest:oclc:id:record' => [
            'label' => 'OCLC: Online computer library center (collected record)', // @translate
            'options' => [
                'resource' => 'OCLC',
                'identifier' => true,
                'uri_label' => 'record',
            ],
        ],

        'valuesuggest:olid:id' => [
            'label' => 'OLID: Open library id from Internet Archive (by id)', // @translate
            'options' => [
                'resource' => 'OLID',
               'identifier' => true,
                'uri_label' => 'id',
            ],
        ],
        'valuesuggest:olid:id:name' => [
            'label' => 'OLID: Open library id from Internet Archive (by id, name)', // @translate
            'options' => [
                'resource' => 'OLID',
                'identifier' => true,
                'uri_label' => 'name',
            ],
        ],
        'valuesuggest:olid:id:reference' => [
            'label' => 'OLID: Open library id from Internet Archive (by id, reference)', // @translate
            'options' => [
                'resource' => 'OLID',
                'identifier' => true,
                'uri_label' => 'reference',
            ],
        ],
        'valuesuggest:olid:id:record' => [
            'label' => 'OLID: Open library id from Internet Archive (collected record)', // @translate
            'options' => [
                'resource' => 'OLID',
                'identifier' => true,
                'uri_label' => 'record',
            ],
        ],
    ];

    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $dataType = new OpenLibrary($services);
        return $dataType
            ->setName($requestedName)
            ->setLabel($this->types[$requestedName]['label'])
            ->setOptions($this->types[$requestedName]['options'])
        ;
    }
}
