<?php
namespace Bibliography;

return [
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'citation' => View\Helper\Citation::class,
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
        ],
        'factories' => [
            Form\SiteSettingsFieldset::class => Service\Form\SiteSettingsFieldsetFactory::class,
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
        'site_settings' => [
            'bibliography_csl_style' => '',
            'bibliography_csl_locale' => '',
        ],
        'block_settings' => [
            'bibliography' => [
                'heading' => '',
                'format' => 'Chicago',
                'query' => '',
                'append_site' => false,
                'append_access_date' => false,
                'bibliographic' => false,
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
