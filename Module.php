<?php
namespace Maintenance;

use Maintenance\Form\ConfigForm;
use Omeka\Module\AbstractModule;
use Zend\Mvc\Controller\AbstractController;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Renderer\PhpRenderer;

/**
 * Maintenance
 *
 * Add a button to set the site under maintenance for the public.
 *
 * @copyright Daniel Berthereau, 2017
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt
 */
class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);
        if ($this->checkMaintenanceStatus($event)) {
            $this->siteUnderMaintenance($event);
        }
    }

    public function install(ServiceLocatorInterface $serviceLocator)
    {
        $settings = $serviceLocator->get('Omeka\Settings');
        $config = require __DIR__ . '/config/module.config.php';
        $defaultSettings = $config[strtolower(__NAMESPACE__)]['settings'];
        foreach ($defaultSettings as $name => $value) {
            $settings->set($name, $value);
        }
    }

    public function uninstall(ServiceLocatorInterface $serviceLocator)
    {
        $settings = $serviceLocator->get('Omeka\Settings');
        $config = require __DIR__ . '/config/module.config.php';
        $defaultSettings = $config[strtolower(__NAMESPACE__)]['settings'];
        foreach ($defaultSettings as $name => $value) {
            $settings->delete($name);
        }
    }

    public function getConfigForm(PhpRenderer $renderer)
    {
        $services = $this->getServiceLocator();
        $config = $services->get('Config');
        $settings = $services->get('Omeka\Settings');
        $formElementManager = $services->get('FormElementManager');

        $data = [];
        $defaultSettings = $config[strtolower(__NAMESPACE__)]['settings'];
        foreach ($defaultSettings as $name => $value) {
            $data[$name] = $settings->get($name);
        }

        $form = $formElementManager->get(ConfigForm::class);
        $form->init();
        $form->setData($data);
        $html = $renderer->formCollection($form);
        return $html;
    }

    public function handleConfigForm(AbstractController $controller)
    {
        $services = $this->getServiceLocator();
        $config = $services->get('Config');
        $settings = $services->get('Omeka\Settings');

        $params = $controller->getRequest()->getPost();

        $form = $this->getServiceLocator()->get('FormElementManager')
            ->get(ConfigForm::class);
        $form->init();
        $form->setData($params);
        if (!$form->isValid()) {
            $controller->messenger()->addErrors($form->getMessages());
            return false;
        }

        $defaultSettings = $config[strtolower(__NAMESPACE__)]['settings'];
        foreach ($params as $name => $value) {
            if (isset($defaultSettings[$name])) {
                $settings->set($name, $value);
            }
        }
    }

    /**
     * Check if the maintenance is set on or off.
     *
     * @param MvcEvent $event
     * @return boolean
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
        $services = $event->getApplication()->getServiceManager();
        if ($this->isAdminRequest($event)) {
            $messenger = $services->get('ControllerPluginManager')->get('messenger');
            $messenger->addWarning('Site is under maintenance.'); // @translate
            return;
        }

        $urlHelper = $services->get('ViewHelperManager')->get('url');
        $request = $event->getRequest();
        $requestUri = $request->getRequestUri();
        $maintenanceUri = $urlHelper('maintenance');
        $loginUri = $urlHelper('login');
        $logoutUri = $urlHelper('logout');
        if (in_array($requestUri, [$maintenanceUri, $loginUri, $logoutUri])) {
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
        $request = $event->getRequest();
        return strpos($request->getRequestUri(), $request->getBasePath() . '/admin') === 0;
    }
}
