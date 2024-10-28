<?php declare(strict_types=1);

namespace Bibliography\Form;

use Bibliography\Service\TraitCslData;
use Common\Form\Element as CommonElement;
use Laminas\Form\Element;
use Laminas\Form\Fieldset;

class SettingsFieldset extends Fieldset
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
                'name' => 'bibliography_crossref_email',
                // TODO Use Common element optional email in next version of Common 3.4.64.
                'type' => Element\Email::class,
                'options' => [
                    'element_group' => 'bibliography',
                    'label' => 'Crossref account email', // @translate
                    'info' => 'This email allows to be connected to better servers of crossref.', // @translate
                    'documentation' => 'https://github.com/CrossRef/rest-api-doc#etiquette',
                ],
                'attributes' => [
                    'id' => 'bibliography_crossref_email',
                ],
            ])
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
        ;
    }
}
