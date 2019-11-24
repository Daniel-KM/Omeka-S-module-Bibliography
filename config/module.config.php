<?php
namespace Bibliography;

return [
    'data_types' => [
        'factories' => [
            /* DOI: Digital object identifiers */
            'valuesuggest:doi' => Service\DoiDataTypeFactory::class,
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
