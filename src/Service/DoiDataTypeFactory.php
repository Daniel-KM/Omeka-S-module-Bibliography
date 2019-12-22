<?php
namespace Bibliography\Service;

use Bibliography\DataType\Doi\Doi;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class DoiDataTypeFactory implements FactoryInterface
{
    protected $types = [
        'valuesuggest:doi:works' => [
            'label' => 'DOI: Works', // @translate
            'options' => [
                'resource' => 'works',
                'identifier' => false,
                'uri_label' => 'id',
            ],
        ],
        'valuesuggest:doi:works:name' => [
            'label' => 'DOI: Works (name)', // @translate
            'options' => [
                'resource' => 'works',
                'identifier' => false,
                'uri_label' => 'name',
            ],
        ],
        'valuesuggest:doi:works:reference' => [
            'label' => 'DOI: Works (reference)', // @translate
            'options' => [
                'resource' => 'works',
                'identifier' => false,
                'uri_label' => 'reference',
            ],
        ],
        'valuesuggest:doi:works:record' => [
            'label' => 'DOI: Works (collected record)', // @translate
            'options' => [
                'resource' => 'works',
                'identifier' => false,
                'uri_label' => 'record',
            ],
        ],
        'valuesuggest:doi:works:id' => [
            'label' => 'DOI: Works (by id)', // @translate
            'options' => [
                'resource' => 'works',
                'identifier' => true,
                'uri_label' => 'id',
            ],
        ],
        'valuesuggest:doi:works:id:name' => [
            'label' => 'DOI: Works (by id, name)', // @translate
            'options' => [
                'resource' => 'works',
                'identifier' => true,
                'uri_label' => 'name',
            ],
        ],
        'valuesuggest:doi:works:id:reference' => [
            'label' => 'DOI: Works (by id, reference)', // @translate
            'options' => [
                'resource' => 'works',
                'identifier' => true,
                'uri_label' => 'reference',
            ],
        ],
        'valuesuggest:doi:works:id:record' => [
            'label' => 'DOI: Works (by id, collected record)', // @translate
            'options' => [
                'resource' => 'works',
                'identifier' => true,
                'uri_label' => 'record',
            ],
        ],

        'valuesuggest:doi:journals' => [
            'label' => 'DOI: Journals', // @translate
            'options' => [
                'resource' => 'journals',
                'identifier' => false,
                'uri_label' => 'id',
            ],
        ],
        'valuesuggest:doi:journals:name' => [
            'label' => 'DOI: Journals (name)', // @translate
            'options' => [
                'resource' => 'journals',
                'identifier' => false,
                'uri_label' => 'name',
            ],
        ],
        'valuesuggest:doi:journals:id' => [
            'label' => 'DOI: Journals (by id)', // @translate
            'options' => [
                'resource' => 'journals',
                'identifier' => true,
                'uri_label' => 'id',
            ],
        ],
        'valuesuggest:doi:journals:id:name' => [
            'label' => 'DOI: Journals (by id, name)', // @translate
            'options' => [
                'resource' => 'journals',
                'identifier' => true,
                'uri_label' => 'name',
            ],
        ],

        'valuesuggest:doi:funders' => [
            'label' => 'DOI: Funders', // @translate
            'options' => [
                'resource' => 'funders',
                'identifier' => false,
                'uri_label' => 'id',
            ],
        ],
        'valuesuggest:doi:funders:name' => [
            'label' => 'DOI: Funders (name)', // @translate
            'options' => [
                'resource' => 'funders',
                'identifier' => false,
                'uri_label' => 'name',
            ],
        ],
        'valuesuggest:doi:funders:id' => [
            'label' => 'DOI: Funders (by id)', // @translate
            'options' => [
                'resource' => 'funders',
                'identifier' => true,
                'uri_label' => 'id',
            ],
        ],
        'valuesuggest:doi:funders:id:name' => [
            'label' => 'DOI: Funders (by id, name)', // @translate
            'options' => [
                'resource' => 'funders',
                'identifier' => true,
                'uri_label' => 'name',
            ],
        ],

        'valuesuggest:doi:members' => [
            'label' => 'DOI: Members', // @translate
            'options' => [
                'resource' => 'members',
                'identifier' => false,
                'uri_label' => 'id',
            ],
        ],
        'valuesuggest:doi:members:name' => [
            'label' => 'DOI: Members (name)', // @translate
            'options' => [
                'resource' => 'members',
                'identifier' => false,
                'uri_label' => 'name',
            ],
        ],
        'valuesuggest:doi:members:id' => [
            'label' => 'DOI: Member (by id)', // @translate
            'options' => [
                'resource' => 'members',
                'identifier' => true,
                'uri_label' => 'id',
            ],
        ],
        'valuesuggest:doi:members:id:name' => [
            'label' => 'DOI: Member (by id, name)', // @translate
            'options' => [
                'resource' => 'members',
                'identifier' => true,
                'uri_label' => 'name',
            ],
        ],

        'valuesuggest:doi:licenses' => [
            'label' => 'DOI: Licenses', // @translate
            'options' => [
                'resource' => 'licenses',
                'identifier' => false,
                'uri_label' => null,
            ],
        ],
        'valuesuggest:doi:licenses:id' => [
            'label' => 'DOI: License (by id)', // @translate
            'options' => [
                'resource' => 'licenses',
                'identifier' => true,
                'uri_label' => null,
            ],
        ],

        'valuesuggest:doi:types' => [
            'label' => 'DOI: Types', // @translate
            'options' => [
                'resource' => 'types',
                'identifier' => false,
                'uri_label' => null,
            ],
        ],
    ];

    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $dataType = new Doi($services);
        return $dataType
            ->setName($requestedName)
            ->setLabel($this->types[$requestedName]['label'])
            ->setOptions($this->types[$requestedName]['options'])
        ;
    }
}
