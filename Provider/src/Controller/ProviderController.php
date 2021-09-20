<?php


declare(strict_types=1);

namespace Provider\Controller;

use Laminas\Authentication\AuthenticationService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use RuntimeException;
use Provider\Form\CreatesForm;
use Provider\Model\Table\ProviderTable;

class ProviderController extends AbstractActionController
{
    private $providerTable;

    public function __construct(ProviderTable $providerTable)
    {
        $this->providerTable = $providerTable;
    }

    public function createsAction()
    {
        # make sure only visitors with no session access this page
        $auth = new AuthenticationService();
        if ($auth->hasIdentity()) {
            # if user has session take them some else
            return $this->redirect()->toRoute('logins');
        }

        $createsForm = new CreatesForm();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $formData = $request->getPost()->toArray();
            $createsForm->setInputFilter($this->providerTable->getCreateFormFilter());
            $createsForm->setData($formData);

            if ($createsForm->isValid()) {
                try {
                    $data = $createsForm->getData();
                    $this->providerTable->saveAccount($data);

                    $this->flashMessenger()->addSuccessMessage('Account successfully createsd. You can now login');

                    return $this->redirect()->toRoute('logins');
                } catch (RuntimeException $exception) {
                    $this->flashMessenger()->addErrorMessage($exception->getMessage());
                    return $this->redirect()->refresh(); # refresh this page to view errors
                }
            }
        }

        return new ViewModel(['form' => $createsForm]);
    }

    public function errorsAction()
    {
        return new ViewModel;
    }
}