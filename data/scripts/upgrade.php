<?php declare(strict_types=1);

namespace Maintenance;

use Omeka\Module\Exception\ModuleCannotInstallException;
use Omeka\Stdlib\Message;

/**
 * @var Module $this
 * @var \Laminas\ServiceManager\ServiceLocatorInterface $services
 * @var string $newVersion
 * @var string $oldVersion
 *
 * @var \Omeka\Api\Manager $api
 * @var \Omeka\Settings\Settings $settings
 * @var \Doctrine\DBAL\Connection $connection
 * @var \Doctrine\ORM\EntityManager $entityManager
 * @var \Omeka\Mvc\Controller\Plugin\Messenger $messenger
 */
$plugins = $services->get('ControllerPluginManager');
$api = $plugins->get('api');
$settings = $services->get('Omeka\Settings');
$connection = $services->get('Omeka\Connection');
$messenger = $plugins->get('messenger');
$entityManager = $services->get('Omeka\EntityManager');

if (version_compare($oldVersion, '3.3.3.2', '<')) {
    $message = new Message(
        'This module is deprecated and has been superceded by %1$sEasy Admin%2$s. The upgrade from it is automatic.', // @translate
        '<a href="https://gitlab.com/Daniel-KM/Omeka-S-module-EasyAdmin" target="_blank">',
        '</a>'
    );
    $message->setEscapeHtml(false);
    $messenger->addWarning($message);
}
