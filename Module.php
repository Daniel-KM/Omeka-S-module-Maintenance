<?php declare(strict_types=1);

namespace Maintenance;

if (!class_exists(\Generic\AbstractModule::class)) {
    require file_exists(dirname(__DIR__) . '/Generic/AbstractModule.php')
        ? dirname(__DIR__) . '/Generic/AbstractModule.php'
        : __DIR__ . '/src/Generic/AbstractModule.php';
}

use Generic\AbstractModule;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Omeka\Stdlib\Message;

/**
 * Maintenance
 *
 * Add a setting to set the site under maintenance for the public.
 *
 * @copyright Daniel Berthereau, 2017-2023
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt
 */
class Module extends AbstractModule
{
    const NAMESPACE = __NAMESPACE__;

    public function install(ServiceLocatorInterface $services): void
    {
        parent::install($services);
        $this->warnDeprecation($services);
    }

    protected function warnDeprecation($services): void
    {
        $messenger = $services->get('ControllerPluginManager')->get('messenger');
        $message = new Message(
            'This module is deprecated and has been superceded by %1$sEasy Admin%2$s. The upgrade from it is automatic.', // @translate
            '<a href="https://gitlab.com/Daniel-KM/Omeka-S-module-EasyAdmin" target="_blank">',
            '</a>'
        );
        $message->setEscapeHtml(false);
        $messenger->addWarning($message);
    }

    public function onBootstrap(MvcEvent $event): void
    {
        parent::onBootstrap($event);
        if ($this->checkMaintenanceStatus($event)) {
            $this->siteUnderMaintenance($event);
        }
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager): void
    {
        $sharedEventManager->attach(
            \Omeka\Form\SettingForm::class,
            'form.add_elements',
            [$this, 'handleMainSettings']
        );
    }

    /**
     * Check if the maintenance is set on or off.
     *
     * @param MvcEvent $event
     * @return bool
     */
    protected function checkMaintenanceStatus(MvcEvent $event)
    {
        return $event->getApplication()
            ->getServiceManager()
            ->get('Omeka\Settings')
            ->get('maintenance_status', false);
    }

    /**
     * Redirect to maintenance for public pages and warn on admin pages.
     *
     * @param MvcEvent $event
     */
    protected function siteUnderMaintenance(MvcEvent $event): void
    {
        static $done;

        $services = $event->getApplication()->getServiceManager();
        if ($this->isAdminRequest($event)) {
            if ($done) {
                return;
            }
            $done = true;
            $basePath = $services->get('ViewHelperManager')->get('basePath');
            $url = $basePath() . '/admin/setting';
            $messenger = $services->get('ControllerPluginManager')->get('messenger');
            $message = new Message(
                'Site is under %smaintenance%s.', // @translate
                sprintf('<a href="%s">', htmlspecialchars($url)),
                '</a>'
            );
            $message->setEscapeHtml(false);
            $messenger->addWarning($message);
            return;
        }

        $urlHelper = $services->get('ViewHelperManager')->get('url');
        $request = $event->getRequest();
        $requestUri = $request->getRequestUri();
        $maintenanceUri = $urlHelper('maintenance');
        $loginUri = $urlHelper('login');
        $logoutUri = $urlHelper('logout');
        $migrateUri = $urlHelper('migrate');
        if (in_array($requestUri, [$maintenanceUri, $loginUri, $logoutUri, $migrateUri])) {
            return;
        }

        $response = $event->getResponse();
        $response->getHeaders()->addHeaderLine('Location', $maintenanceUri);
        $response->setStatusCode(302);
        $response->sendHeaders();
        exit;
    }

    /**
     * Check if a request is public.
     *
     * @param MvcEvent $event
     * @return bool
     */
    protected function isAdminRequest(MvcEvent $event)
    {
        // TODO Use isAdminRequest(), but keep compatibility wth Omeka 1.0.0.
        $request = $event->getRequest();
        return strpos($request->getRequestUri(), $request->getBasePath() . '/admin') === 0;
    }
}
