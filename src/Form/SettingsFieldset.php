<?php
namespace Bibliography\Form;

use Bibliography\Service\TraitCslData;
use Zend\Form\Element;
use Zend\Form\Fieldset;

class SettingsFieldset extends Fieldset
{
    use TraitCslData;

    /**
     * @var string
     */
    protected $label = 'Bibliography'; // @translate

    public function init()
    {
        $this
            ->add([
                'name' => 'bibliography_crossref_email',
                'type' => Element\Email::class,
                'options' => [
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
