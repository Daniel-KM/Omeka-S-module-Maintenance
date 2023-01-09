<?php declare(strict_types=1);
namespace Maintenance\Form;

use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Omeka\Form\Element\CkeditorInline;

class SettingsFieldset extends Fieldset
{
    protected $label = 'Maintenance'; // @translate

    public function init(): void
    {
        $this
            ->setAttribute('id', 'maintenance')
            ->add([
                'name' => 'maintenance_status',
                'type' => Element\Checkbox::class,
                'options' => [
                    'label' => 'Set the public site under maintenance', // @translate
                    'info' => 'When Omeka is not under maintenance, it is recommended to disable the module to avoid a check on each page.', // @translate
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
