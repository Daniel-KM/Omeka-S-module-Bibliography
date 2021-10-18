<?php declare(strict_types=1);

namespace Bibliography\Form;

use Bibliography\Service\TraitCslData;
use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Omeka\Form\Element as OmekaElement;

class BibliographyBlockFieldset extends Fieldset
{
    use TraitCslData;

    public function init(): void
    {
        $this
            ->add([
                'name' => 'o:block[__blockIndex__][o:data][heading]',
                'type' => Element\Text::class,
                'options' => [
                    'label' => 'Block title', // @translate
                    'info' => 'Heading for the block, if any.', // @translate
                ],
                'attributes' => [
                    'id' => 'bibliography-heading',
                ],
            ])
            ->add([
                'name' => 'o:block[__blockIndex__][o:data][style]',
                'type' => Element\Select::class,
                'options' => [
                    'label' => 'Citation style', // @translate
                    'value_options' => $this->getCitationStyles(),
                ],
                'attributes' => [
                    'id' => 'bibliography-style',
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Select your citation style…', // @translate
                ],
            ])
            ->add([
                'name' => 'o:block[__blockIndex__][o:data][locale]',
                'type' => Element\Select::class,
                'options' => [
                    'label' => 'Citation locale', // @translate
                    'value_options' => $this->getCitationLocales(),
                ],
                'attributes' => [
                    'id' => 'bibliography-locale',
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Select your locale for citation…', // @translate
                ],
            ])
            ->add([
                'name' => 'o:block[__blockIndex__][o:data][query]',
                'type' => OmekaElement\Query::class,
                'options' => [
                    'label' => 'Query', // @translate
                    'info' => 'Display resources using this search query', // @translate
                    'query_resource_type' => null,
                    'query_partial_excludelist' => ['common/advanced-search/site'],
                ],
                'attributes' => [
                    'id' => 'bibliography-query',
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
                'name' => 'o:block[__blockIndex__][o:data][append_date]',
                'type' => Element\Checkbox::class,
                'options' => [
                    'label' => 'Append access date', // @translate
                ],
                'attributes' => [
                    'id' => 'bibliography-append-date',
                ],
            ])
        ;

        if (class_exists('BlockPlus\Form\Element\TemplateSelect')) {
            $this
                ->add([
                    'name' => 'o:block[__blockIndex__][o:data][template]',
                    'type' => \BlockPlus\Form\Element\TemplateSelect::class,
                    'options' => [
                        'label' => 'Template to display', // @translate
                        'info' => 'Templates are in folder "common/block-layout" of the theme and should start with "bibliography".', // @translate
                        'template' => 'common/block-layout/bibliography',
                    ],
                    'attributes' => [
                        'id' => 'bibliography-template',
                        'class' => 'chosen-select',
                    ],
                ]);
        }
    }
}
