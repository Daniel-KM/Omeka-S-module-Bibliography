<?php declare(strict_types=1);
namespace Bibliography\Form;

use Bibliography\Service\TraitCslData;
use Laminas\Form\Element;
use Laminas\Form\Fieldset;

class SiteSettingsFieldset extends Fieldset
{
    use TraitCslData;

    /**
     * @var string
     */
    protected $label = 'Bibliography'; // @translate

    public function init(): void
    {
        $this
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
