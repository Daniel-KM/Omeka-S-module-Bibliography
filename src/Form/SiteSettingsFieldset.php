<?php declare(strict_types=1);

namespace Bibliography\Form;

use Bibliography\Service\TraitCslData;
use Common\Form\Element as CommonElement;
use Laminas\Form\Fieldset;

class SiteSettingsFieldset extends Fieldset
{
    use TraitCslData;

    /**
     * @var string
     */
    protected $label = 'Bibliography'; // @translate

    protected $elementGroups = [
        'bibliography' => 'Bibliography', // @translate
    ];

    public function init(): void
    {
        $this
            ->setAttribute('id', 'bibliography')
            ->setOption('element_groups', $this->elementGroups)
            ->add([
                'name' => 'bibliography_csl_style',
                'type' => CommonElement\OptionalSelect::class,
                'options' => [
                    'element_group' => 'bibliography',
                    'label' => 'Citation style', // @translate
                    'value_options' => $this->getCitationStyles(),
                    'empty_option' => '',
                ],
                'attributes' => [
                    'id' => 'bibliography_csl_style',
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Select your citation style…', // @translate
                ],
            ])
            ->add([
                'name' => 'bibliography_csl_locale',
                'type' => CommonElement\OptionalSelect::class,
                'options' => [
                    'element_group' => 'bibliography',
                    'label' => 'Citation locale', // @translate
                    'value_options' => $this->getCitationLocales(),
                    'empty_option' => '',
                ],
                'attributes' => [
                    'id' => 'bibliography_csl_locale',
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Select your locale for citation…', // @translate
                ],
            ])
            ->add([
                'name' => 'bibliography_placement_citation',
                'type' => CommonElement\OptionalMultiCheckbox::class,
                'options' => [
                    'element_group' => 'bibliography',
                    'label' => 'Display citation', // @translate
                    'value_options' => [
                        'block/items' => 'Items: Via resource block or custom theme', // @translate
                        'before/items' => 'Item: Top', // @translate
                        'after/items' => 'Item: Bottom', // @translate
                    ],
                ],
                'attributes' => [
                    'id' => 'bibliography_placement_citation',
                    'required' => false,
                ],
            ])
        ;
    }
}
