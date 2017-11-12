<?php
namespace Maintenance;

return [
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'form_elements' => [
        'invokables' => [
            Form\ConfigForm::class => Form\ConfigForm::class,
        ],
    ],
    'controllers' => [
        'invokables' => [
            'Omeka\Controller\Maintenance' => Controller\MaintenanceController::class,
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
    'maintenance' => [
        'settings' => [
            'maintenance_status' => false,
            'maintenance_text' => 'This site is down for maintenance. Please contact the site administrator for more information.', // @translate
        ],
    ],
];
