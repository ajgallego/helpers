<?php namespace Zizaco\Confide;

use Zizaco\Confide\Facade as ConfideFacade;
use Illuminate\Support\Facades\App as App;

/**
 * This is a trait containing a initial implementation of the
 * methods declared in the ConfideUserInterface.
 *
 * @see \Zizaco\Confide\ConfideUserInterface
 * @license MIT
 * @package Zizaco\Confide
 */
trait ConfideUser
{
    /**
     * A MessageBag object that store any error regarding the confide User.
     *
     * @var \Illuminate\Support\MessageBag
     */
    public $errors;


    /**
     * Checks if the current user is valid using the ConfideUserValidator.
     *
     * @return bool
     */
    public function isValid()
    {
        // Instantiate the Zizaco\Confide\UserValidator and calls the
        // validate method. Feel free to use your own validation
        // class.
        $validator = App::make('confide.user_validator');

        // If the model already exists in the database we call validate with
        // the update ruleset
        if ($this->exists) {
            return $validator->validate($this, 'update');
        }

        return $validator->validate($this);
    }

    

    /**
     * Returns a MessageBag object that store any error
     * regarding the user validation.
     *
     * @return \Illuminate\Support\MessageBag
     */
    public function errors()
    {
        return $this->errors ?: $this->errors = App::make('Illuminate\Support\MessageBag');
    }

}
