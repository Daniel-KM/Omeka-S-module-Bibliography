<?php
namespace Citation\Form;

use Zend\Form\Element;
use Zend\Form\Fieldset;

class BibliographyBlockFieldset extends Fieldset
{
    public function init()
    {
        $this
            ->add([
                'name' => 'o:block[__blockIndex__][o:data][heading]',
                'type' => Element\Text::class,
                'options' => [
                    'label' => 'Block title', // @translate
                    'info' => 'Heading for the block, if any.', // @translate
                ],
            ])
            ->add([
                'name' => 'o:block[__blockIndex__][o:data][format]',
                'type' => Element\Select::class,
                'options' => [
                    'label' => 'Citation format', // @translate
                    'value_options' => [
                        'Chicago' => 'Chicago', // @translate
                    ],
                ],
                'attributes' => [
                    'class' => 'chosen-select',
                    'value' => 'Chicago',
                ],
            ])
            ->add([
                'name' => 'o:block[__blockIndex__][o:data][query]',
                'type' => Element\Text::class,
                'options' => [
                    'label' => 'Query', // @translate
                    'info' => 'Display items using this search query', // @translate
                    'documentation' => 'https://omeka.org/s/docs/user-manual/sites/site_pages/#browse-preview',
                ],
            ])
            ->add([
                'name' => 'o:block[__blockIndex__][o:data][append_site]',
                'type' => Element\Checkbox::class,
                'options' => [
                    'label' => 'Append the site', // @translate
                ],
            ])
            ->add([
                'name' => 'o:block[__blockIndex__][o:data][append_access_date]',
                'type' => Element\Checkbox::class,
                'options' => [
                    'label' => 'Append access date', // @translate
                ],
            ])
            ->add([
                'name' => 'o:block[__blockIndex__][o:data][bibliographic]',
                'type' => Element\Checkbox::class,
                'options' => [
                    'label' => 'Bibliographic', // @translate
                    'info' => 'Indicate if the items are records of external books, articles, etc., and not Omeka common resources.', // @translate
                ],
            ]);

        if (class_exists('BlockPlus\Form\Element\PartialSelect')) {
            $this
                ->add([
                    'name' => 'o:block[__blockIndex__][o:data][partial]',
                    'type' => \BlockPlus\Form\Element\PartialSelect::class,
                    'options' => [
                        'label' => 'Partial to display', // @translate
                        'info' => 'Partials are in folder "common/block-layout" of the theme and should start with "bibliography".', // @translate
                        'partial' => 'common/block-layout/bibliography',
                    ],
                    'attributes' => [
                        'class' => 'chosen-select',
                    ],
                ]);
        }
    }
}
