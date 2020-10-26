<?php
namespace Maintenance\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class MaintenanceController extends AbstractActionController
{
    public function indexAction()
    {
        $settings = $this->settings();
        // Don't display the maintenance page when the site is on.
        if (!$settings->get('maintenance_status')) {
            // Except if the site is under maintenance of course.
            // See Omeka\Mvc\MvcListeners::redirectToMigration().
            $status = $this->status();
            if (!$status->needsVersionUpdate() && !$status->needsMigration()) {
                return $this->redirect()->toRoute('top');
            }
        }
        $view = new ViewModel();
        $view->setTemplate('omeka/maintenance/index');
        $view->setVariable('text', $settings->get('maintenance_text'));
        return $view;
    }
}
