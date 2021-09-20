<?php


declare(strict_types=1);

namespace Provider\Controller;

use Laminas\Authentication\AuthenticationService;
use Laminas\Mvc\Controller\AbstractActionController;

class LogoutsController extends AbstractActionController
{
    public function testAction()
    {
        $auth = new AuthenticationService();
        if ($auth->hasIdentity()) {
            $auth->clearIdentity();
        }

        return $this->redirect()->toRoute('logins');
    }
}
