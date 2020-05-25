<?php
namespace Bibliography;

return [
    'data_types' => [
        'factories' => [
            // There are multiple times the same type to bypass a technical issue
            // in order to manage large query or query by id only, and to set
            // the label, that can be a name or id according to properties (for
            // example the title or the article for dcterms:title, but the id
            // for bibo:doi of the reference for dcterms:bibliographicCitation.

            /* DOI: Digital object identifiers */
            'valuesuggest:doi:works' => Service\DoiDataTypeFactory::class,
            'valuesuggest:doi:works:name' => Service\DoiDataTypeFactory::class,
            'valuesuggest:doi:works:reference' => Service\DoiDataTypeFactory::class,
            'valuesuggest:doi:works:record' => Service\DoiDataTypeFactory::class,
            'valuesuggest:doi:works:id' => Service\DoiDataTypeFactory::class,
            'valuesuggest:doi:works:id:name' => Service\DoiDataTypeFactory::class,
            'valuesuggest:doi:works:id:reference' => Service\DoiDataTypeFactory::class,
            'valuesuggest:doi:works:id:record' => Service\DoiDataTypeFactory::class,

            'valuesuggest:doi:journals' => Service\DoiDataTypeFactory::class,
            'valuesuggest:doi:journals:name' => Service\DoiDataTypeFactory::class,
            'valuesuggest:doi:journals:id' => Service\DoiDataTypeFactory::class,
            'valuesuggest:doi:journals:id:name' => Service\DoiDataTypeFactory::class,

            'valuesuggest:doi:funders' => Service\DoiDataTypeFactory::class,
            'valuesuggest:doi:funders:name' => Service\DoiDataTypeFactory::class,
            'valuesuggest:doi:funders:id' => Service\DoiDataTypeFactory::class,
            'valuesuggest:doi:funders:id:name' => Service\DoiDataTypeFactory::class,

            'valuesuggest:doi:members' => Service\DoiDataTypeFactory::class,
            'valuesuggest:doi:members:name' => Service\DoiDataTypeFactory::class,
            'valuesuggest:doi:members:id' => Service\DoiDataTypeFactory::class,
            'valuesuggest:doi:members:id:name' => Service\DoiDataTypeFactory::class,

            'valuesuggest:doi:licenses' => Service\DoiDataTypeFactory::class,

            'valuesuggest:doi:types' => Service\DoiDataTypeFactory::class,

            /* ISBN: International standard book numbers */
            'valuesuggest:isbn:id' => Service\OpenLibraryDataTypeFactory::class,
            'valuesuggest:isbn:id:name' => Service\OpenLibraryDataTypeFactory::class,
            'valuesuggest:isbn:id:reference' => Service\OpenLibraryDataTypeFactory::class,
            'valuesuggest:isbn:id:record' => Service\OpenLibraryDataTypeFactory::class,

            /* LCCN: Library of Congress control number */
            'valuesuggest:lccn:id' => Service\OpenLibraryDataTypeFactory::class,
            'valuesuggest:lccn:id:name' => Service\OpenLibraryDataTypeFactory::class,
            'valuesuggest:lccn:id:reference' => Service\OpenLibraryDataTypeFactory::class,
            'valuesuggest:lccn:id:record' => Service\OpenLibraryDataTypeFactory::class,

            /* OCLC: Online computer library center */
            'valuesuggest:oclc:id' => Service\OpenLibraryDataTypeFactory::class,
            'valuesuggest:oclc:id:name' => Service\OpenLibraryDataTypeFactory::class,
            'valuesuggest:oclc:id:reference' => Service\OpenLibraryDataTypeFactory::class,
            'valuesuggest:oclc:id:record' => Service\OpenLibraryDataTypeFactory::class,

            /* OLID: Open library id from Internet Archive */
            'valuesuggest:olid:id' => Service\OpenLibraryDataTypeFactory::class,
            'valuesuggest:olid:id:name' => Service\OpenLibraryDataTypeFactory::class,
            'valuesuggest:olid:id:reference' => Service\OpenLibraryDataTypeFactory::class,
            'valuesuggest:olid:id:record' => Service\OpenLibraryDataTypeFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
        'template_map' => [
            'bibliography/bibliography/output' => dirname(__DIR__) . '/view/common/no-view.phtml',
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'citation' => View\Helper\Citation::class,
            'rdfToBibtex' => View\Helper\RdfToBibtex::class,
            'rdfToCsl' => View\Helper\RdfToCsl::class,
            'rdfToCsv' => View\Helper\RdfToCsv::class,
            'rdfToRis' => View\Helper\RdfToRis::class,
        ],
        'factories' => [
            'cslToRdf' => Service\ViewHelper\CslToRdfFactory::class,
        ],
    ],
    'block_layouts' => [
        'invokables' => [
            'bibliography' => Site\BlockLayout\Bibliography::class,
        ],
    ],
    'form_elements' => [
        'invokables' => [
            Form\BibliographyBlockFieldset::class => Form\BibliographyBlockFieldset::class,
            Form\SettingsFieldset::class => Form\SettingsFieldset::class,
            Form\SiteSettingsFieldset::class => Form\SiteSettingsFieldset::class,
        ],
    ],
    'controllers' => [
        'invokables' => [
            'Bibliography\Controller\Bibliography' => Controller\BibliographyController::class,
        ],
    ],
    'router' => [
        'routes' => [
            'site' => [
                'child_routes' => [
                    'resource-id' => [
                        'may_terminate' => true,
                        'child_routes' => [
                            'output' => [
                                'type' => \Zend\Router\Http\Segment::class,
                                'options' => [
                                    'route' => '.:output',
                                    'constraints' => [
                                        'action' => 'item',
                                        'output' => 'bibtex|bib|csv|tsv|ris|json',
                                    ],
                                    'defaults' => [
                                        '__NAMESPACE__' => 'Bibliography\Controller',
                                        'controller' => 'Bibliography',
                                        'action' => 'output',
                                        'output' => 'json',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => dirname(__DIR__) . '/language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
    'bibliography' => [
        'settings' => [
            'bibliography_crossref_email' => '',
            'bibliography_csl_style' => 'chicago-fullnote-bibliography',
            'bibliography_csl_locale' => '',
        ],
        'site_settings' => [
            'bibliography_csl_style' => 'chicago-fullnote-bibliography',
            'bibliography_csl_locale' => '',
        ],
        'block_settings' => [
            'bibliography' => [
                'heading' => '',
                'style' => 'chicago-fullnote-bibliography',
                'locale' => '',
                'query' => '',
                'append_site' => false,
                'append_date' => false,
                'template' => '',
            ],
        ],
    ],
    'blocksdisposition' => [
        'views' => [
            'item_show' => [
                'Bibliography',
            ],
        ],
    ],
    'formatters' => [
        'factories' => [
            Formatter\Bibtex::class => \BulkExport\Service\Formatter\FormatterFactory::class,
            Formatter\Csl::class => \BulkExport\Service\Formatter\FormatterFactory::class,
            Formatter\Ris::class => \BulkExport\Service\Formatter\FormatterFactory::class,
        ],
        'aliases' => [
            'bibtex' => Formatter\Bibtex::class,
            'csl' => Formatter\Csl::class,
            'ris' => Formatter\Ris::class,
        ],
    ],
];
