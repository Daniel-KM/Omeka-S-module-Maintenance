<?php
namespace Maintenance\Form;

use Zend\Form\Element\Textarea;
use Zend\Form\Form;

class ConfigForm extends Form
{
    public function init()
    {
        $this->setAttribute('id', 'config-form');

        $this->add([
            'name' => 'maintenance_status',
            'type' => 'Checkbox',
            'options' => [
                'label' => 'Set the public site under maintenance', // @translate
            ],
        ]);

        $this->add([
            'name' => 'maintenance_text',
            'type' => Textarea::class,
            'options' => [
                'label' => 'Text to display', // @translate
            ],
            'attributes' => [
                'rows' => 12,
                'placeholder' => 'This site is down for maintenance. Please contact the site administrator for more information.', // @translate
            ],
        ]);
    }
}
