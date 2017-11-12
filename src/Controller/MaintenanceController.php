<?php
namespace Maintenance\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class MaintenanceController extends AbstractActionController
{
    public function indexAction()
    {
        $settings = $this->settings();

        // Don't display the maintenance page when the site is on.
        if (!$this->settings()->get('maintenance_status')) {
            return $this->redirect()->toRoute('top');
        }

        $view = new ViewModel();
        $view->setTemplate('omeka/maintenance/index');
        $view->setVariable('text', $this->settings()->get('maintenance_text'));
        return $view;
    }
}
