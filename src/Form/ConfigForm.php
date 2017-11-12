<?php
namespace Maintenance\Form;

use Zend\Form\Form;

class ConfigForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'maintenance_status',
            'type' => 'Checkbox',
            'options' => [
                'label' => 'Set the public site under maintenance', // @translate
            ],
        ]);
    }
}
