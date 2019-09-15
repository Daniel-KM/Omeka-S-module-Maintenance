<?php
namespace Maintenance;

if (!class_exists(\Generic\AbstractModule::class)) {
    require file_exists(dirname(__DIR__) . '/Generic/AbstractModule.php')
    ? dirname(__DIR__) . '/Generic/AbstractModule.php'
        : __DIR__ . '/src/Generic/AbstractModule.php';
}

use Generic\AbstractModule;
use Omeka\Stdlib\Message;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\Event;

/**
 * Maintenance
 *
 * Add a setting to set the site under maintenance for the public.
 *
 * @copyright Daniel Berthereau, 2017-2019
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt
 */
class Module extends AbstractModule
{
    const NAMESPACE = __NAMESPACE__;

    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);
        if ($this->checkMaintenanceStatus($event)) {
            $this->siteUnderMaintenance($event);
        }
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach(
            \Omeka\Form\SettingForm::class,
            'form.add_elements',
            [$this, 'handleMainSettings']
        );
    }

    public function handleMainSettings(Event $event)
    {
        $ckEditorHelper = $this->getServiceLocator()->get('ViewHelperManager')->get('ckEditor');
        $ckEditorHelper();

        parent::handleMainSettings($event);
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
    protected function siteUnderMaintenance(MvcEvent $event)
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
            $messenger->addWarning($message); // @translate
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
