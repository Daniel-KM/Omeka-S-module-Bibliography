<?php declare(strict_types=1);

namespace Bibliography\Form;

use Bibliography\Service\TraitCslData;
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
        'export' => 'Export', // @translate
    ];

    public function init(): void
    {
        $this
            ->setAttribute('id', 'bibliography')
            ->setOption('element_groups', $this->elementGroups)
            ->add([
                'name' => 'bibliography_crossref_email',
                'type' => Element\Email::class,
                'options' => [
                    'element_group' => 'export',
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
                'type' => Element\Select::class,
                'options' => [
                    'element_group' => 'export',
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
                'type' => Element\Select::class,
                'options' => [
                    'element_group' => 'export',
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
