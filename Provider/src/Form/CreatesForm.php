<?php


declare(strict_types=1);

namespace Provider\Form;

use Laminas\Form\Form;
use Laminas\Form\Element;

class CreatesForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('new_account');
        $this->setAttribute('method', 'post');

        # username input field
        $this->add([
            'type' => Element\Text::class,
            'name' => 'username',
            'options' => [
                'label' => 'Username'
            ],
            'attributes' => [
                'required' => true,
                'size' => 40,
                'maxlength' => 25,
                'pattern' => '^[a-zA-Z0-9]+$',  # enforcing what type of data we accept
                'data-toggle' => 'tooltip',
                'class' => 'form-control',   # styling the text field
                'title' => 'Username must consist of alphanumeric characters only',
                'placeholder' => 'Enter Your Username'
            ]
        ]);

        # gender select field
        $this->add([
            'type' => Element\Select::class,
            'name' => 'gender',
            'options' => [
                'label' => 'Gender',
                'empty_option' => 'Select...',
                'value_options' => [
                    'Female' => 'Female',
                    'Male' => 'Male',
                    'Other' => 'Other'
                ],
            ],
            'attributes' => [
                'required' => true,
                'class' => 'custom-select', # styling
            ]
        ]);

        # email address input field
        $this->add([
            'type' => Element\Email::class,
            'name' => 'email',
            'options' => [
                'label' => 'Email Address'
            ],
            'attributes' => [
                'required' => true,
                'size' => 40,
                'maxlength' => 128,
                'pattern' => '^[a-zA-Z0-9+_.-]+@[a-zA-Z0-9.-]+$',
                'autocomplete' => false,
                'data-toggle' => 'tooltip',
                'class' => 'form-control',
                'title' => 'Provide a valid and working email address',
                'placeholder' => 'Enter Your Email Address'
            ]
        ]);

        # confirm email address
        $this->add([
            'type' => Element\Email::class,
            'name' => 'confirm_email',
            'options' => [
                'label' => 'Verify Email Address'
            ],
            'attributes' => [
                'required' => true,
                'size' => 40,
                'maxlength' => 128,
                'pattern' => '^[a-zA-Z0-9+_.-]+@[a-zA-Z0-9.-]+$',
                'autocomplete' => false,
                'data-toggle' => 'tooltip',
                'class' => 'form-control',
                'title' => 'Email address must match that provided above',
                'placeholder' => 'Enter Your Email Address Again'
            ]
        ]);

        # birth day select field
        $this->add([
            'type' => Element\DateSelect::class,
            'name' => 'birthday',
            'options' => [
                'label' => 'Select Your Date of Birth',
                'create_empty_option' => true,
                'max_year' => date('Y') - 13, # here we want users over the age of 13 only
                'render_delimiters' => false,
                'year_attributes' => [
                    'class' => 'custom-select w-30'
                ],
                'month_attributes' => [
                    'class' => 'custom-select w-30'
                ],
                'day_attributes' => [
                    'class' => 'custom-select w-30',
                    'id' => 'day'
                ],
            ],
            'attributes' => [
                'required' => true
            ]
        ]);

        # password input field
        $this->add([
            'type' => Element\Password::class,
            'name' => 'password',
            'options' => [
                'label' => 'Password'
            ],
            'attributes' => [
                'required' => true,
                'size' => 40,
                'maxlength' => 25,
                'autocomplete' => false,
                'data-toggle' => 'tooltip',
                'class' => 'form-control',   # styling
                'title' => 'Password must have between 8 and 25 characters',
                'placeholder' => 'Enter Your Password'
            ]
        ]);

        # confirm password input field
        $this->add([
            'type' => Element\Password::class,
            'name' => 'confirm_password',
            'options' => [
                'label' => 'Verify Password'
            ],
            'attributes' => [
                'required' => true,
                'size' => 40,
                'maxlength' => 25,
                'autocomplete' => false,
                'data-toggle' => 'tooltip',
                'class' => 'form-control',   # styling
                'title' => 'Password must match that provided above',
                'placeholder' => 'Enter Your Password Again'
            ]
        ]);

        # cross-site-request forgery (csrf) field
        $this->add([
            'type' => Element\Csrf::class,
            'name' => 'csrf',
            'options' => [
                'csrf_options' => [
                    'timeout' => 600,  # 5 minutes
                ]
            ]
        ]);

        # submit button
        $this->add([
            'type' => Element\Submit::class,
            'name' => 'create_account',
            'attributes' => [
                'value' => 'Create Account',
                'class' => 'btn btn-primary'
            ]
        ]);
    }
}

