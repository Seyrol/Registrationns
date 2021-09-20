<?php


declare(strict_types=1);

namespace Provider\Model\Table;

use Laminas\Crypt\Password\Bcrypt;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Filter;
use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\I18n;
use Laminas\InputFilter;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Paginator\Paginator;
use Laminas\Validator;
use Provider\Model\Entity\ProviderEntity;

class ProviderTable extends AbstractTableGateway
{
    protected $adapter;          # adapter to use to connect to the database
    protected $table = 'provider';  # our table. one we want to store data in

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }

    public function deleteAccount(int $userId)
    {
        #$sqlQuery = $this->sql->update()->set(['active' => 0])->where(['user_id' => $userId]);
        $sqlQuery = $this->sql->delete()->where(['user_id' => $userId]);
        $sqlStmt = $this->sql->prepareStatementForSqlObject($sqlQuery);

        return $sqlStmt->execute();
    }

    public function fetchAllAccounts($paginated = false)
    {
        $sqlQuery = $this->sql->select()->join('roles', 'roles.role_id=' . $this->table . '.role_id',
            ['role'])
            ->where(['active' => 1])
            ->order('created ASC');

        if ($paginated) {
            $classMethod = new ClassMethodsHydrator();
            $entity = new ProviderEntity();
            $resultSet = new HydratingResultSet($classMethod, $entity);

            $paginatorAdapter = new DbSelect(
                $sqlQuery,
                $this->adapter,
                $resultSet
            );

            $paginator = new Paginator($paginatorAdapter);

            return $paginator;
        }


        $sqlStmt = $this->sql->prepareStatementForSqlObject($sqlQuery);
        $handler = $sqlStmt->execute();

        $classMethod = new ClassMethodsHydrator();
        $entity = new ProviderEntity();
        $resultSet = new HydratingResultSet($classMethod, $entity);

        $resultSet->initialize($handler);

        return $resultSet;
    }

    public function fetchAccountById(int $userId)
    {
        $sqlQuery = $this->sql->select()
            ->join('roles', 'roles.role_id=' . $this->table . '.role_id', ['role_id', 'role'])
            ->where(['user_id' => $userId]);
        $sqlStmt = $this->sql->prepareStatementForSqlObject($sqlQuery);
        $handler = $sqlStmt->execute()->current();

        if (!$handler) {
            return null;
        }

        $classMethod = new ClassMethodsHydrator();
        $entity = new ProviderEntity();
        $classMethod->hydrate($handler, $entity);

        return $entity;
    }

    public function fetchAccountByEmail(string $email)
    {
        $sqlQuery = $this->sql->select()
            ->join('roles', 'roles.role_id=' . $this->table . '.role_id', ['role_id', 'role'])
            ->where(['email' => $email]);
        $sqlStmt = $this->sql->prepareStatementForSqlObject($sqlQuery);
        $handler = $sqlStmt->execute()->current();

        if (!$handler) {
            return null;
        }

        $classMethod = new ClassMethodsHydrator();
        $entity = new ProviderEntity();
        $classMethod->hydrate($handler, $entity);

        return $entity;
    }

    # filter and validate data from emailForm# filter and validate data from emailForm
    public function getEmailFormFilter()
    {
        # for those typing in let us view contents of this method slowly
        $inputFilter = new InputFilter\InputFilter();
        $factory = new InputFilter\Factory();

        # current email address
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'current_email',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class],  # removes html tags
                        ['name' => Filter\StringTrim::class],
                        //['name' => Filter\StringToLower::class],
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        [
                            'name' => Validator\StringLength::class,
                            'options' => [
                                'min' => 6,
                                'max' => 128,
                                'messages' => [
                                    Validator\StringLength::TOO_SHORT => 'Email address must have at least 6 characters!',
                                    Validator\StringLength::TOO_LONG => 'Email address must have at most 128 characters!',
                                ],
                            ],
                        ],
                        ['name' => Validator\EmailAddress::class],
                        [
                            'name' => Validator\Db\RecordExists::class,
                            'options' => [
                                'table' => $this->table,
                                'field' => 'email',
                                'adapter' => $this->adapter,
                            ],
                        ],
                    ],
                ]
            )
        );

        # new_email address field
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'new_email',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class],  # removes html tags
                        ['name' => Filter\StringTrim::class],
                        //['name' => Filter\StringToLower::class],
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        [
                            'name' => Validator\StringLength::class,
                            'options' => [
                                'min' => 6,
                                'max' => 128,
                                'messages' => [
                                    Validator\StringLength::TOO_SHORT => 'Email address must have at least 6 characters!',
                                    Validator\StringLength::TOO_LONG => 'Email address must have at most 128 characters!',
                                ],
                            ],
                        ],
                        ['name' => Validator\EmailAddress::class],
                        [
                            'name' => Validator\Db\NoRecordExists::class,
                            'options' => [
                                'table' => $this->table,
                                'field' => 'email',
                                'adapter' => $this->adapter,
                            ],
                        ],
                    ],
                ]
            )
        );

        #confirm_new_email address field
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'confirm_new_email',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class],  # removes html tags
                        ['name' => Filter\StringTrim::class],
                        //['name' => Filter\StringToLower::class],
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        [
                            'name' => Validator\StringLength::class,
                            'options' => [
                                'min' => 6,
                                'max' => 128,
                                'messages' => [
                                    Validator\StringLength::TOO_SHORT => 'Email address must have at least 6 characters!',
                                    Validator\StringLength::TOO_LONG => 'Email address must have at most 128 characters!',
                                ],
                            ],
                        ],
                        ['name' => Validator\EmailAddress::class],
                        [
                            'name' => Validator\Db\NoRecordExists::class,
                            'options' => [
                                'table' => $this->table,
                                'field' => 'email',
                                'adapter' => $this->adapter,
                            ],
                        ],
                        [
                            'name' => Validator\Identical::class,
                            'options' => [
                                'token' => 'new_email',  # field to compare against
                                'messages' => [
                                    Validator\Identical::NOT_SAME => 'New Email addresses do not match!',
                                ],

                            ]
                        ],
                    ],
                ]
            )
        );

        # csrf
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'csrf',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class],  # removes html tags
                        ['name' => Filter\StringTrim::class],
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        [
                            'name' => Validator\Csrf::class,
                            'options' => [
                                'messages' => [
                                    Validator\Csrf::NOT_SAME => 'Oops! Refill the form and try again',
                                ],
                            ],
                        ],
                    ],
                ]
            )
        );

        # be sure to return the input filter
        return $inputFilter;
    }

    public function getForgotFormFilter()
    {
        $inputFilter = new InputFilter\InputFilter();
        $factory = new InputFilter\Factory();

        # filter and validate email
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'email',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class],  # removes html tags
                        ['name' => Filter\StringTrim::class],
                        ['name' => Filter\StringToLower::class],
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        [
                            'name' => Validator\StringLength::class,
                            'options' => [
                                'min' => 6,
                                'max' => 128,
                                'messages' => [
                                    Validator\StringLength::TOO_SHORT => 'Email address must have at least 6 characters!',
                                    Validator\StringLength::TOO_LONG => 'Email address must have at most 128 characters!',
                                ],
                            ],
                        ],
                        ['name' => Validator\EmailAddress::class],
                        [
                            'name' => Validator\Db\RecordExists::class,
                            'options' => [
                                'table' => $this->table,
                                'field' => 'email',
                                'adapter' => $this->adapter,
                            ],
                        ],
                    ],
                ]
            )
        );

        # csrf
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'csrf',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class],  # removes html tags
                        ['name' => Filter\StringTrim::class],
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        [
                            'name' => Validator\Csrf::class,
                            'options' => [
                                'messages' => [
                                    Validator\Csrf::NOT_SAME => 'Oops! Refill the form and try again',
                                ],
                            ],
                        ],
                    ],
                ]
            )
        );

        # be sure to return the input filter
        return $inputFilter;

    }

    # sanitizes our loginForm
    public function getLoginFormFilter()
    {
        $inputFilter = new InputFilter\InputFilter();
        $factory = new InputFilter\Factory();

        # filter and validate email
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'email',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class],  # removes html tags
                        ['name' => Filter\StringTrim::class],
                        //['name' => Filter\StringToLower::class],
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        [
                            'name' => Validator\StringLength::class,
                            'options' => [
                                'min' => 6,
                                'max' => 128,
                                'messages' => [
                                    Validator\StringLength::TOO_SHORT => 'Email address must have at least 6 characters!',
                                    Validator\StringLength::TOO_LONG => 'Email address must have at most 128 characters!',
                                ],
                            ],
                        ],
                        ['name' => Validator\EmailAddress::class],
                        [
                            'name' => Validator\Db\RecordExists::class,
                            'options' => [
                                'table' => $this->table,
                                'field' => 'email',
                                'adapter' => $this->adapter,
                            ],
                        ],
                    ],
                ]
            )
        );

        # filter and validate password
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'password',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class],  # removes html tags
                        ['name' => Filter\StringTrim::class],
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        [
                            'name' => Validator\StringLength::class,
                            'options' => [
                                'min' => 8,
                                'max' => 25,
                                'messages' => [
                                    Validator\StringLength::TOO_SHORT => 'Password must have at least 8 characters!',
                                    Validator\StringLength::TOO_LONG => 'Password must have at most 25 characters!',
                                ],
                            ],
                        ],
                    ],
                ]
            )
        );

        # recall checkbox
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'recall',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class],  # removes html tags
                        ['name' => Filter\StringTrim::class],
                        ['name' => Filter\ToInt::class],
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        ['name' => I18n\Validator\IsInt::class],
                        [
                            'name' => Validator\InArray::class,
                            'options' => [
                                'haystack' => [0, 1]
                            ],
                        ],
                    ],
                ]
            )
        );

        # csrf
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'csrf',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class],  # removes html tags
                        ['name' => Filter\StringTrim::class],
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        [
                            'name' => Validator\Csrf::class,
                            'options' => [
                                'messages' => [
                                    Validator\Csrf::NOT_SAME => 'Oops! Refill the form and try again',
                                ],
                            ],
                        ],
                    ],
                ]
            )
        );

        # be sure to return the input filter
        return $inputFilter;
    }

    # sanitizes our form data
    public function getCreateFormFilter()
    {
        $inputFilter = new InputFilter\InputFilter();
        $factory = new InputFilter\Factory();

        # filter and validate username input field
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'username',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class], # stips html tags
                        ['name' => Filter\StringTrim::class], # removes empty spaces
                        ['name' => I18n\Filter\Alnum::class], # allows only [a-zA-Z0-9] characters
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        [
                            'name' => Validator\StringLength::class,
                            'options' => [
                                'min' => 2,
                                'max' => 25,
                                'messages' => [
                                    Validator\StringLength::TOO_SHORT => 'Username must have at least 2 characters',
                                    Validator\StringLength::TOO_LONG => 'Username must have at most 25 characters',
                                ],
                            ],
                        ],
                        [
                            'name' => I18n\Validator\Alnum::class,
                            'options' => [
                                'messages' => [
                                    I18n\Validator\Alnum::NOT_ALNUM => 'Username must consist of alphanumeric characters only',
                                ],
                            ],
                        ],
                        [
                            'name' => Validator\Db\NoRecordExists::class,
                            'options' => [
                                'table' => $this->table,
                                'field' => 'username',
                                'adapter' => $this->adapter,
                            ],
                        ],
                    ],
                ]
            )
        );

        # filter and validate gender select field
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'gender',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class], # stips html tags
                        ['name' => Filter\StringTrim::class], # removes empty spaces
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        [
                            'name' => Validator\InArray::class,
                            'options' => [
                                'haystack' => ['Female', 'Male', 'Other'],
                            ],
                        ],
                    ],
                ]
            )
        );

        # filter and validate email input field
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'email',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class],
                        ['name' => Filter\StringTrim::class],
                        ['name' => Filter\StringToLower::class], #comment this line out
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        ['name' => Validator\EmailAddress::class],
                        [
                            'name' => Validator\StringLength::class,
                            'options' => [
                                'min' => 6,
                                'max' => 128,
                                'messages' => [
                                    Validator\StringLength::TOO_SHORT => 'Email address must have at least 6 characters',
                                    Validator\StringLength::TOO_LONG => 'Email address must have at most 128 characters',
                                ],
                            ],
                        ],
                        [
                            'name' => Validator\Db\NoRecordExists::class,
                            'options' => [
                                'table' => $this->table,
                                'field' => 'email',
                                'adapter' => $this->adapter,
                            ],
                        ],
                    ],
                ]
            )
        );

        # filter and validate confirm_email input field
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'confirm_email',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class], # stips html tags
                        ['name' => Filter\StringTrim::class], # removes empty spaces
                        ['name' => Filter\StringToLower::class],/* as well as this one*/
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        ['name' => Validator\EmailAddress::class],
                        [
                            'name' => Validator\StringLength::class,
                            'options' => [
                                'min' => 6,
                                'max' => 128,
                                'messages' => [
                                    Validator\StringLength::TOO_SHORT => 'Email address must have at least 6 characters',
                                    Validator\StringLength::TOO_LONG => 'Email address must have at most 128 characters',
                                ],
                            ],
                        ],
                        [
                            'name' => Validator\Db\NoRecordExists::class,
                            'options' => [
                                'table' => $this->table,
                                'field' => 'email',
                                'adapter' => $this->adapter,
                            ],
                        ],
                        [
                            'name' => Validator\Identical::class,
                            'options' => [
                                'token' => 'email',  # field to compare against
                                'messages' => [
                                    Validator\Identical::NOT_SAME => 'Email addresses do not match!',
                                ],
                            ],
                        ],
                    ],
                ]
            )
        );

        # filter and validate birthday dateselect field
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'birthday',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class], # stips html tags
                        ['name' => Filter\StringTrim::class], # removes empty spaces
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        [
                            'name' => Validator\Date::class,
                            'options' => [
                                'format' => 'Y-m-d',
                            ],
                        ],
                    ],
                ]
            )
        );

        # filter and validate password input field
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'password',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class], # stips html tags
                        ['name' => Filter\StringTrim::class], # removes empty spaces
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        [
                            'name' => Validator\StringLength::class,
                            'options' => [
                                'min' => 8,
                                'max' => 25,
                                'messages' => [
                                    Validator\StringLength::TOO_SHORT => 'Password must have at least 8 characters',
                                    Validator\StringLength::TOO_LONG => 'Password must have at most 25 characters',
                                ],
                            ],
                        ],
                    ],
                ]
            )
        );

        # filter and validate confirm_password field
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'confirm_password',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class], # stips html tags
                        ['name' => Filter\StringTrim::class], # removes empty spaces
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        [
                            'name' => Validator\StringLength::class,
                            'options' => [
                                'min' => 8,
                                'max' => 25,
                                'messages' => [
                                    Validator\StringLength::TOO_SHORT => 'Password must have at least 8 characters',
                                    Validator\StringLength::TOO_LONG => 'Password must have at most 25 characters',
                                ],
                            ],
                        ],
                        [
                            'name' => Validator\Identical::class,
                            'options' => [
                                'token' => 'password',
                                'messages' => [
                                    Validator\Identical::NOT_SAME => 'Passwords do not match!',
                                ],
                            ],
                        ],
                    ],
                ]
            )
        );

        # csrf field
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'csrf',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class], # stips html tags
                        ['name' => Filter\StringTrim::class], # removes empty spaces
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        [
                            'name' => Validator\Csrf::class,
                            'options' => [
                                'messages' => [
                                    Validator\Csrf::NOT_SAME => 'Oops! Refill the form.',
                                ],
                            ],
                        ],
                    ],
                ]
            )
        );

        return $inputFilter;
    }

    public function getResetFormFilter()
    {
        $inputFilter = new InputFilter\InputFilter();
        $factory = new InputFilter\Factory();

        # filter and validate password input field
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'new_password',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class], # stips html tags
                        ['name' => Filter\StringTrim::class], # removes empty spaces
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        [
                            'name' => Validator\StringLength::class,
                            'options' => [
                                'min' => 8,
                                'max' => 25,
                                'messages' => [
                                    Validator\StringLength::TOO_SHORT => 'New Password must have at least 8 characters',
                                    Validator\StringLength::TOO_LONG => 'New Password must have at most 25 characters',
                                ],
                            ],
                        ],
                    ],
                ]
            )
        );

        # filter and validate confirm_new_password field
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'confirm_new_password',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class], # stips html tags
                        ['name' => Filter\StringTrim::class], # removes empty spaces
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        [
                            'name' => Validator\StringLength::class,
                            'options' => [
                                'min' => 8,
                                'max' => 25,
                                'messages' => [
                                    Validator\StringLength::TOO_SHORT => 'New Password must have at least 8 characters',
                                    Validator\StringLength::TOO_LONG => 'New Password must have at most 25 characters',
                                ],
                            ],
                        ],
                        [
                            'name' => Validator\Identical::class,
                            'options' => [
                                'token' => 'new_password',
                                'messages' => [
                                    Validator\Identical::NOT_SAME => 'Passwords do not match!',
                                ],
                            ],
                        ],
                    ],
                ]
            )
        );

        # csrf field
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'csrf',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class], # stips html tags
                        ['name' => Filter\StringTrim::class], # removes empty spaces
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        [
                            'name' => Validator\Csrf::class,
                            'options' => [
                                'messages' => [
                                    Validator\Csrf::NOT_SAME => 'Oops! Refill the form.',
                                ],
                            ],
                        ],
                    ],
                ]
            )
        );

        return $inputFilter;
    }

    public function getPasswordFormFilter()
    {
        $inputFilter = new InputFilter\InputFilter();
        $factory = new InputFilter\Factory();

        # filter and validate current password input field
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'current_password',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class], # stips html tags
                        ['name' => Filter\StringTrim::class], # removes empty spaces
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        [
                            'name' => Validator\StringLength::class,
                            'options' => [
                                'min' => 8,
                                'max' => 25,
                                'messages' => [
                                    Validator\StringLength::TOO_SHORT => 'New Password must have at least 8 characters',
                                    Validator\StringLength::TOO_LONG => 'New Password must have at most 25 characters',
                                ],
                            ],
                        ],
                    ],
                ]
            )
        );

        #filter and validate new password input field
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'new_password',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class], # stips html tags
                        ['name' => Filter\StringTrim::class], # removes empty spaces
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        [
                            'name' => Validator\StringLength::class,
                            'options' => [
                                'min' => 8,
                                'max' => 25,
                                'messages' => [
                                    Validator\StringLength::TOO_SHORT => 'New Password must have at least 8 characters',
                                    Validator\StringLength::TOO_LONG => 'New Password must have at most 25 characters',
                                ],
                            ],
                        ],
                    ],
                ]
            )
        );

        # filter and validate confirm_new_password field
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'confirm_new_password',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class], # stips html tags
                        ['name' => Filter\StringTrim::class], # removes empty spaces
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        [
                            'name' => Validator\StringLength::class,
                            'options' => [
                                'min' => 8,
                                'max' => 25,
                                'messages' => [
                                    Validator\StringLength::TOO_SHORT => 'New Password must have at least 8 characters',
                                    Validator\StringLength::TOO_LONG => 'New Password must have at most 25 characters',
                                ],
                            ],
                        ],
                        [
                            'name' => Validator\Identical::class,
                            'options' => [
                                'token' => 'new_password',
                                'messages' => [
                                    Validator\Identical::NOT_SAME => 'Passwords do not match!',
                                ],
                            ],
                        ],
                    ],
                ]
            )
        );

        # csrf field
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'csrf',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class], # stips html tags
                        ['name' => Filter\StringTrim::class], # removes empty spaces
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        [
                            'name' => Validator\Csrf::class,
                            'options' => [
                                'messages' => [
                                    Validator\Csrf::NOT_SAME => 'Oops! Refill the form.',
                                ],
                            ],
                        ],
                    ],
                ]
            )
        );

        return $inputFilter;
    }


    public function getUsernameFormFilter()
    {
        $inputFilter = new InputFilter\InputFilter();
        $factory = new InputFilter\Factory();

        # filter and validate current_username input field
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'current_username',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class], # stips html tags
                        ['name' => Filter\StringTrim::class], # removes empty spaces
                        ['name' => I18n\Filter\Alnum::class], # allows only [a-zA-Z0-9] characters
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        [
                            'name' => Validator\StringLength::class,
                            'options' => [
                                'min' => 2,
                                'max' => 25,
                                'messages' => [
                                    Validator\StringLength::TOO_SHORT => 'Username must have at least 2 characters',
                                    Validator\StringLength::TOO_LONG => 'Username must have at most 25 characters',
                                ],
                            ],
                        ],
                        [
                            'name' => I18n\Validator\Alnum::class,
                            'options' => [
                                'messages' => [
                                    I18n\Validator\Alnum::NOT_ALNUM => 'Username must consist of alphanumeric characters only',
                                ],
                            ],
                        ],
                        [
                            'name' => Validator\Db\RecordExists::class,
                            'options' => [
                                'table' => $this->table,
                                'field' => 'username',
                                'adapter' => $this->adapter,
                            ],
                        ],
                    ],
                ]
            )
        );

        # filter and validate username input field
        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'new_username',
                    'required' => true,
                    'filters' => [
                        ['name' => Filter\StripTags::class], # stips html tags
                        ['name' => Filter\StringTrim::class], # removes empty spaces
                        ['name' => I18n\Filter\Alnum::class], # allows only [a-zA-Z0-9] characters
                    ],
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        [
                            'name' => Validator\StringLength::class,
                            'options' => [
                                'min' => 2,
                                'max' => 25,
                                'messages' => [
                                    Validator\StringLength::TOO_SHORT => 'Username must have at least 2 characters',
                                    Validator\StringLength::TOO_LONG => 'Username must have at most 25 characters',
                                ],
                            ],
                        ],
                        [
                            'name' => I18n\Validator\Alnum::class,
                            'options' => [
                                'messages' => [
                                    I18n\Validator\Alnum::NOT_ALNUM => 'Username must consist of alphanumeric characters only',
                                ],
                            ],
                        ],
                        [
                            'name' => Validator\Db\NoRecordExists::class,
                            'options' => [
                                'table' => $this->table,
                                'field' => 'username',
                                'adapter' => $this->adapter,
                            ],
                        ],
                    ],
                ]
            )
        );

        return $inputFilter;
    }

    public function getUploadFormFilter()
    {
        $inputFilter = new InputFilter\InputFilter();
        $factory = new InputFilter\Factory();

        $inputFilter->add(
            $factory->createInput(
                [
                    'name' => 'photo',
                    'required' => true,
                    # note for files we start with validators before we use filters
                    'validators' => [
                        ['name' => Validator\NotEmpty::class],
                        ['name' => Validator\File\IsImage::class],
                        /*[
                            'name' => Validator\File\MimeType::class,
                            'options' => [
                                'mimeType' => 'image/png. image/jpeg, image/jpg, image/gif'
                            ],
                        ],*/ # just uncomment this one. I forgot. It always gives issues.
                        [
                            'name' => Validator\File\Size::class,
                            'options' => [
                                'min' => '3kB',
                                'max' => '15MB'
                            ],
                        ],
                    ],
                    # we can now filter
                    'filters' => [
                        ['name' => Filter\StripTags::class],
                        ['name' => Filter\StringTrim::class],
                        [
                            'name' => Filter\File\RenameUpload::class,
                            'options' => [
                                'target' => DOOR . DS . 'images' . DS . 'temporary' . DS . 'pic',
                                'use_upload_name' => false,
                                'use_upload_extension' => true,
                                'overwrite' => false,
                                'randomize' => true
                            ]
                        ]
                    ]
                ]
            )
        );

        return $inputFilter;
    }


    public function saveAccount(array $data)
    {
        $timeNow = date('Y-m-d H:i:s');
        $values = [
            'username' => ucfirst($data['username']),
            'email' => mb_strtolower($data['email']),
            'password' => (new Bcrypt())->create($data['password']), # encrypt/hash password
            'birthday' => $data['birthday'],
            'gender' => $data['gender'],
            'role_id' => $this->assignRoleId(),
            'created' => $timeNow,
            'modified' => $timeNow,
        ];

        $sqlQuery = $this->sql->insert()->values($values);
        $sqlStmt = $this->sql->prepareStatementForSqlObject($sqlQuery);

        return $sqlStmt->execute();
    }

    public function updateEmail(string $email, int $userId)
    {
        $values = [
            'email' => mb_strtolower($email),
            'modified' => date('Y-m-d H:i:s')
        ];

        $sqlQuery = $this->sql->update()->set($values)->where(['user_id' => $userId]);
        $sqlStmt = $this->sql->prepareStatementForSqlObject($sqlQuery);

        return $sqlStmt->execute();
    }

    public function updatePassword(string $password, int $userId)
    {
        $values = [
            'password' => (new Bcrypt())->create($password),
            'modified' => date('Y-m-d H:i:s')
        ];

        $sqlQuery = $this->sql->update()->set($values)->where(['user_id' => $userId]);
        $sqlStmt = $this->sql->prepareStatementForSqlObject($sqlQuery);

        return $sqlStmt->execute();
    }

    public function updatePhoto(string $photo, int $userId)
    {
        $sqlQuery = $this->sql->update()->set(['photo' => $photo])->where(['user_id' => $userId]);
        $sqlStmt = $this->sql->prepareStatementForSqlObject($sqlQuery);

        return $sqlStmt->execute();
    }

    public function updateUsername(string $username, int $userId)
    {
        $values = [
            'username' => ucfirst($username),
            'modified' => date('Y-m-d H:i:s')
        ];

        $sqlQuery = $this->sql->update()->set($values)->where(['user_id' => $userId]);
        $sqlStmt = $this->sql->prepareStatementForSqlObject($sqlQuery);

        return $sqlStmt->execute();
    }

    private function assignRoleId()
    {
        $sqlQuery = $this->sql->select()->where(['role_id' => 1]);
        $sqlStmt = $this->sql->prepareStatementForSqlObject($sqlQuery);
        $handler = $sqlStmt->execute()->current();

        return (!$handler) ? 1 : 2;
    }
}
