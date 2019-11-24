<?php
namespace Bibliography\Form;

use Zend\Form\Element;
use Zend\Form\Fieldset;

class SettingsFieldset extends Fieldset
{
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
        ;
    }
}
