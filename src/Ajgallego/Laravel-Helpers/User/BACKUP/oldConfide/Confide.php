<?php namespace Zizaco\Confide;

use Illuminate\View\Environment;
use Illuminate\Config\Repository;
use InvalidArgumentException;
use Zizaco\Confide\ObjectProvider;

class Confide
{
   

    /**
     * Attempt to log a user into the application with
     * password and identity field(s), usually email or username.
     *
     * @param  array $credentials
     * @param  bool $confirmed_only
     * @param  mixed $identity_columns
     * @return boolean Success
     */
    public function logAttempt( $credentials, $confirmed_only = false, $identity_columns = array() )
    {
        // If identity columns is not provided, use all columns of credentials
        // except password and remember.
        if(empty($identity_columns))
        {
            $identity_columns = array_diff(
                array_keys($credentials),
                array('password','remember')
            );
        }

        // Check for throttle limit then log-in
        if(! $this->reachedThrottleLimit( $credentials ) )
        {
            $user = $this->repo->getUserByIdentity($credentials, $identity_columns);

            if(
                $user &&
                ($user->confirmed || ! $confirmed_only ) &&
                $this->app['hash']->check(
                    $credentials['password'],
                    $user->password
                )
            )
            {
                $remember = isset($credentials['remember']) ? $credentials['remember'] : false;

                $this->app['auth']->login( $user, $remember );
                return true;
            }
        }

        $this->throttleCount( $credentials );

        return false;
    }

    /**
     * Send email with information about password reset
     *
     * @param string  $email
     * @return bool
     */
    public function forgotPassword( $email )
    {
        $user = $this->repo->getUserByMail( $email );
        if( $user )
        {
            $user->forgotPassword();
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Checks to see if the user has a valid token.
     * 
     * @param $token
     * @return bool
     */
    public function isValidToken( $token )
    {
        $count = $this->repo->getPasswordRemindersCount( $token );

        return ($count != 0);
    }

    /**
     * Change user password
     *
     * @return string
     */
    public function resetPassword( $params )
    {
        $token = array_get($params, 'token', '');
        $email = $this->repo->getEmailByReminderToken( $token );
        $user = $this->repo->getUserByMail( $email );

        if( $user )
        {
            if($user->resetPassword( $params ))
            {
                // Password reset success, remove token from database
                $this->repo->deleteEmailByReminderToken( $token );

                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }


    /**
     * Check whether the controller's action exists.
     * Returns the url if it does. Otherwise false.
     * @param $controllerAction
     * @return string
     */
    public function checkAction( $action, $parameters = array(), $absolute = true )
    {
        try {
            $url = $this->app['url']->action($action, $parameters, $absolute);
        } catch( InvalidArgumentException $e ) {
            return false;
        }

        return $url;
    }

  
}
