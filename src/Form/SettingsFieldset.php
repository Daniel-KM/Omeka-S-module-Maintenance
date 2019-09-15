<?php
namespace Maintenance\Form;

use Omeka\Form\Element\CkeditorInline;
use Zend\Form\Element;
use Zend\Form\Fieldset;

class SettingsFieldset extends Fieldset
{
    protected $label = 'Maintenance'; // @translate

    public function init()
    {
        $this
            ->add([
                'name' => 'maintenance_status',
                'type' => Element\Checkbox::class,
                'options' => [
                    'label' => 'Set the public site under maintenance', // @translate
                ],
                'attributes' => [
                    'id' => 'maintenance-status',
                ],
            ])
            ->add([
                'name' => 'maintenance_text',
                'type' => CkeditorInline::class,
                'options' => [
                    'label' => 'Text to display', // @translate
                ],
                'attributes' => [
                    'id' => 'maintenance-text',
                    'rows' => 12,
                    'placeholder' => 'This site is down for maintenance. Please contact the site administrator for more information.', // @translate
                ],
            ]);
    }
}
