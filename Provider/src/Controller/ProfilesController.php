<?php

declare(strict_types=1);

namespace Provider\Controller;

use Laminas\Authentication\AuthenticationService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\View;

class ProfilesController extends AbstractActionController
{
    public function testAction()
    {
        $auth = new AuthenticationService();
        if ($auth->hasIdentity()) {
            # if user has session take them some else
            return new ViewModel();
        } else {
            return $this->redirect()->toRoute('errors');
        }
    }
}