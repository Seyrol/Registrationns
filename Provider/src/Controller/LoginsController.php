<?php


declare(strict_types=1);

namespace Provider\Controller;

use Laminas\Authentication\Adapter\DbTable\CredentialTreatmentAdapter;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Result;
use Laminas\Crypt\Password\Bcrypt;
use Laminas\Db\Adapter\Adapter;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Session\SessionManager;
use Laminas\View\Model\ViewModel;
use Provider\Form\LoginForm;
use Provider\Model\Table\ProviderTable;

class LoginsController extends AbstractActionController
{
    private $adapter;    # database adapter
    private $providerTable; # table we store data in

    public function __construct(Adapter $adapter, ProviderTable $providerTable)
    {
        $this->adapter = $adapter;
        $this->providerTable = $providerTable;
    }

    public function testAction()
    {
        $auth = new AuthenticationService();
        if ($auth->hasIdentity()) {
            return $this->redirect()->toRoute('home');
        }

        $loginForm = new LoginForm();
        $loginForm->get('returnUrl')->setValue(
            $this->getEvent()->getRouteMatch()->getParam('returnUrl')
        );

        $request = $this->getRequest();

        if ($request->isPost()) {
            $formData = $request->getPost()->toArray();
            $loginForm->setInputFilter($this->providerTable->getLoginFormFilter());
            $loginForm->setData($formData);

            if ($loginForm->isValid()) {
                $authAdapter = new CredentialTreatmentAdapter($this->adapter);
                $authAdapter->setTableName($this->providerTable->getTable())
                    ->setIdentityColumn('email')
                    ->setCredentialColumn('password')
                    ->getDbSelect()->where(['active' => 1]);

                # data from loginForm
                $data = $loginForm->getData();
                $returnUrl = $this->params()->fromPost('returnUrl');
                $authAdapter->setIdentity($data['email']);


                # password hashing class
                $hash = new Bcrypt();

                # well let us use the email address from the form to retrieve data for this user
                $info = $this->providerTable->fetchAccountByEmail($data['email']);

                # now compare password from form input with that already in the table
                if ($hash->verify($data['password'], $info->getPassword())) {
                    $authAdapter->setCredential($info->getPassword());
                } else {
                    $authAdapter->setCredential(''); # why? to gracefully handle errors
                }

                $authResult = $auth->authenticate($authAdapter);

                switch ($authResult->getCode()) {
                    case Result::FAILURE_IDENTITY_NOT_FOUND:
                        $this->flashMessenger()->addErrorMessage('Unknow email address!');
                        return $this->preserveUrl($returnUrl);
                        //return $this->redirect()->refresh(); # refresh the page to show error
                        break;

                    case Result::FAILURE_CREDENTIAL_INVALID:
                        $this->flashMessenger()->addErrorMessage('Incorrect Password!');
                        return $this->redirect()->refresh(); # refresh the page to show error
                        break;

                    case Result::SUCCESS:
                        if ($data['recall'] == 1) {
                            $ssm = new SessionManager();
                            $ttl = 1814400; # time for session to live
                            $ssm->rememberMe($ttl);
                        }

                        $storage = $auth->getStorage();
                        $storage->write($authAdapter->getResultRowObject(null, ['created', 'modified']));

                        if (!empty($returnUrl)) {
                            # will come back to later... if script does not work.
                            return $this->preserveUrl($returnUrl);
                        }

                        # let us now create the profile route and we will be done
                        return $this->redirect()->toRoute(
                            'profile',
                            [
                                'id' => $info->getUserId(),
                                'username' => mb_strtolower($info->getUsername())
                            ]
                        );

                        break;

                    default:
                        $this->flashMessenger()->addErrorMessage('Authentication failed. Try again');
                        return $this->preserveUrl($returnUrl);
                        //return $this->redirect()->refresh(); # refresh the page to show error
                        break;
                }
            }
        }

        return (new ViewModel(['form' => $loginForm]))->setTemplate('provider/provider/logins');
    }

    private function preserveUrl(string $returnUrl = null)
    {
        if (empty($returnUrl)) {
            return $this->redirect()->refresh();
        }

        # we have not yet created the UrlModel class. Let us do so now..
        return $this->redirect()->toUrl(UrlModel::decode($returnUrl));
    }
}
