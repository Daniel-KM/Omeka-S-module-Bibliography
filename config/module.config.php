<?php
namespace Bibliography;

return [
    'data_types' => [
        'factories' => [
            /* DOI: Digital object identifiers */
            // There are multiple times the same type to bypass a technical issue
            // in order to manage large query or query by id only, and to set
            // the label, that can be a name or id according to properties (for
            // example the title or the article for dcterms:title, but the id for bibo:doi.
            'valuesuggest:doi:works' => Service\DoiDataTypeFactory::class,
            'valuesuggest:doi:works:name' => Service\DoiDataTypeFactory::class,
            'valuesuggest:doi:works:reference' => Service\DoiDataTypeFactory::class,
            'valuesuggest:doi:works:id' => Service\DoiDataTypeFactory::class,
            'valuesuggest:doi:works:id:name' => Service\DoiDataTypeFactory::class,
            'valuesuggest:doi:works:id:reference' => Service\DoiDataTypeFactory::class,
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
            /* LCCN: Library of Congress control number */
            'valuesuggest:lccn:id' => Service\OpenLibraryDataTypeFactory::class,
            'valuesuggest:lccn:id:name' => Service\OpenLibraryDataTypeFactory::class,
            'valuesuggest:lccn:id:reference' => Service\OpenLibraryDataTypeFactory::class,
            /* OCLC: Online computer library center */
            'valuesuggest:oclc:id' => Service\OpenLibraryDataTypeFactory::class,
            'valuesuggest:oclc:id:name' => Service\OpenLibraryDataTypeFactory::class,
            'valuesuggest:oclc:id:reference' => Service\OpenLibraryDataTypeFactory::class,
            /* OLID: Open library id from Internet Archive */
            'valuesuggest:olid:id' => Service\OpenLibraryDataTypeFactory::class,
            'valuesuggest:olid:id:name' => Service\OpenLibraryDataTypeFactory::class,
            'valuesuggest:olid:id:reference' => Service\OpenLibraryDataTypeFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'citation' => View\Helper\Citation::class,
            'convertIntoCsl' => View\Helper\ConvertIntoCsl::class,
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
                'append_access_date' => false,
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
];
