<?php namespace Ajgallego\Helpers\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class HelpUser
{
	/**
     * Get the currently authenticated user or null.
     * @return User|null
     */
    public static function user()
    {
        return Auth::user();
    }

    /**
     * Determine if the user is already logged into your application.
     * @return bool
     */
    public static function check()
    {
        return Auth::check();
    }

    /**
     * Check if no user is currently logged in.
     * @return bool True if the users is not logged in and false otherwise.
     */
    public static function guest()
    {
    	return Auth::guest();
    }

	/**
     * Log the user out of the application.
     */
    public static function logout()
    {
        return Auth::logout();
    }

    /**
     * Attempt to log a user into the application with password and
     * identity field(s), usually email or username.
     * @param array $input           Array containing at least 'username' or 'email' and 'password'.
     *                               Optionally the 'remember' boolean.
     * @param bool  $mustBeConfirmed If true, the user must have confirmed his email account in order to log-in.
     * @return bool Success.
     */
    public static function login(array $input, $mustBeConfirmed = true)
    {
        $remember = ( isset($input['remember']) ? $input['remember'] : false );
        $loginAttribute = self::prv_extractIdentityFromArray( $input );

        if( ! self::prv_loginThrottling( $loginAttribute ) ) {
            return false;
        }

        $user = self::getUserByIdentity( $loginAttribute );

        if( $user ) 
        {
            if (! $user->confirmed && $mustBeConfirmed) {
                return false;
            }

            if( ! isset($input['password']) ) {
            	$input['password'] = null;
        	}

            if( ! Hash::check( $input['password'], $user->password ) ) {
                return false;
            }

            Auth::login($user, $remember);

            return true;
        }

        return false;
    }

    /**
     * Checks if the credentials has been throttled by too much failed login attempts
     * @param mixed $identity The login identity.
     * @return boolean True if the identity has reached the throttle_limit.
     */
    public static function isThrottled( array $identity )
    {
      	$loginAttribute = self::prv_extractIdentityFromArray( $identity );

        // Get the current count (but not increments it)
        $count = self::prv_countThrottle( $loginAttribute, 0 );

        return $count >= Config::get('helpers::auth.throttle_limit');
    }

    /**
     * Checks if the given credentials correponds to a user that exists but
     * is not confirmed
     * @param array $credentials Array containing the credentials (email/username and password)
     * @return boolean True if exists but is not confirmed
     */
    public static function existsButNotConfirmed( array $identity )
    {
        $loginAttribute = self::prv_extractIdentityFromArray( $identity );

        $user = self::getUserByIdentity( $loginAttribute );

        if ($user) 
        {
            return ( ! $user->confirmed );
        }

        return false;
    }

    /**
     * Register a new account with the given parameters
     * @param array $input Array containing the user data as the login attribute, password, etc.
     * @return User object that may or may not be saved successfully. Check the id to make sure.
     */
/*    public static function register( array $input )
    {
    	$login_attribute = Config::get('helpers::auth.login_attribute');

        $user = new \HelpUserModel;
        $user->{$login_attribute} = isset( $input[$login_attribute] ) ? $input[$login_attribute] : ''; 
        $user->password = array_get($input, 'password');

        // The password confirmation will be removed from model before saving. 
        // This field will be used in Ardent's auto validation.
        $user->password_confirmation = array_get($input, 'password_confirmation');

        // Generate a random confirmation code
        $user->confirmation_code     = self::prv_generateToken();

        // Save if valid. Password field will be hashed before save
        $user->save();

        return $user;
    }*/

	/**
     * Sets the 'confirmed' field of the user with the matching code to true.
     * @param string $code
     * @return bool Success
     */
    public static function confirm( $code )
    {
    	$user = \HelpUserModel::where( 'confirmation_code', '=', $code )->get()->first();

        if ($user) {
            return $user->confirm();
        } else {
            return false;
        }
    }


    //-------------------------------------------------------------------------
    //-------------------------------------------------------------------------
    // FORGOT PASSWORD ACTIONS
    //-------------------------------------------------------------------------
    //-------------------------------------------------------------------------

    /**
     * If an user with the given email exists then generate a token for password
     * change and saves it in the 'password_reminders' table with the email
     * of the user.<br/>
     * It also sends and email with information about password reset
     * @param string $email
     * @return bool
     */
    public static function forgotPassword( $email )
    {
        $user = self::getUserByEmail($email);

        if( ! $user) {
            return false;
        }

        $email = $user->getReminderEmail();
        $token = self::prv_generateToken();

        $values = array(
            'email'=> $email,
            'token'=> $token,
            'created_at'=> new \DateTime
        );

        \DB::table('password_reminders')->insert( $values );

//TODO......................................................................................
        $view = Config::get('helpers::auth.email_reset_password');

        $this->sendEmail($user, $token); //???
        $this->sendEmail( 'helpers::auth.email.password_reset.subject', 
        				  $view, array('user'=>$this, 'token'=>$token) );

        return true;
    }
//TODO......................................................................................
     /**
     * Sends an email containing the reset password link with the
     * given token to the user.
     *
     * @param RemindableInterface $user  An existent user.
     * @param string              $token Password reset token.
     *
     * @return void
     */
    private static function prv_sendEmailOfResetPassword($user, $token)
    {
        $this->app['mailer']->queueOn(
            \Config::get('helpers::email_queue'),
            \Config::get('helpers::email_reset_password'),
            compact('user', 'token'),
            function ($message) use ($user, $token, $lang) {
                $message
                    ->to($user->email, $user->username)
                    ->subject( trans('helpers::confide.email.password_reset.subject'));
            }
        );
    }
//TODO......................................................................................
    /**
     * Send email using the lang sentence as subject and the viewname
     *
     * @param mixed $subject_translation
     * @param mixed $view_name
     * @param array $params
     * @return voi.
     */
    private static function prv_sendEmail( $subject_translation, $view_name, $params = array() )
    {
        if ( static::$app['config']->getEnvironment() == 'testing' )
            return;

//        static::fixViewHint();

        $user = $this;

        static::$app['mailer']->send($view_name, $params, function($m) use ($subject_translation, $user)
        {
            $m->to( $user->email )
                ->subject( ConfideUser::$app['translator']->get($subject_translation) );
        });
    }

    /**
     * Change user password
     *
     * @param  $params
     * @return string
     */
    public function resetPassword( $params )
    {
        $password = array_get($params, 'password', '');
        $passwordConfirmation = array_get($params, 'password_confirmation', '');

        $passwordValidators = array(
            'password' => static::$rules['password'],
            'password_confirmation' => static::$rules['password_confirmation'],
        );
        $user = static::$app['confide.repository']->model();
        $user->unguard();
        $user->fill(array(
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ));
        $user->reguard();
        $validationResult = static::$app['confide.repository']->validate($user, $passwordValidators);

        if ( $validationResult )
        {
            return static::$app['confide.repository']
                ->changePassword( $this, static::$app['hash']->make($password) );
        }
        else{
            return false;
        }
    }

    /**
     * Delete the record of the given token from 'password_reminders' table.
     * @param string $token Token retrieved from a forgotPassword.
     * @return boolean Success.
     */
    public static function deleteReminderToken( $token )
    {        
        $affected = \DB::table('password_reminders')
			            ->where('token', '=', $token)
			            ->delete();

        return $affected > 0;
    }

    //-------------------------------------------------------------------------
    //-------------------------------------------------------------------------
    // FIND USER HELPERS
    //-------------------------------------------------------------------------
    //-------------------------------------------------------------------------

    /**
     * Find a user by the logint attribute (email, username) specified on the config file.
     * @param string $identity Identity to search for
     * @return User|null
     */
    public static function getUserByIdentity( $identityString )
    {
    	if( empty($identityString) || ! is_string($identityString) ) 
    	{
            return null;
        }

    	$login_attribute = Config::get('helpers::auth.login_attribute');

        return \HelpUserModel::where( $login_attribute, '=', $identityString )->get()->first();
    }

    /**
     * Find a user by email
     * @param string $email Email to search for
     * @return User|null
     */
    public static function getUserByEmail( $email )
    {
    	if( empty($email) || ! is_string($email) ) 
    	{
            return null;
        }

        return \HelpUserModel::where( 'email', '=', $email )->get()->first();
    }

    /**
     * Returns a user that corresponds to the given reset password token or
     * false if there is no user with the given token.
     * @param string $token
     * @return mised User|false
     */
    public static function getUserByReminderToken( $token )
    {
    	$oldestValidDate = \Carbon::now()
            			->subHours( Config::get('helpers::auth.password_reset_expiration', 7) )
            			->toDateTimeString();

        $email = \DB::table('password_reminders')
            			->select('email')
            			->where('token', '=', $token)
            			->where('created_at', '>=', $oldestValidDate )
            			->get()->first();

        if ($email) {
            return self::getUserByEmail( $email );
        }

        return false;
    }

    //-------------------------------------------------------------------------
    //-------------------------------------------------------------------------
    // FIND ROLES HELPERS
    //-------------------------------------------------------------------------
    //-------------------------------------------------------------------------

    /**
     * Get all roles
     * @return Roles|null
     */
    public static function getAllRoles()
    {
        return \HelpRoleModel::all();
    }

    /**
     * Find a role by its name
     * @param string $Name Role name to search for
     * @return Role|null
     */
    public static function getRoleByName( $name )
    {
        return \HelpRoleModel::where( 'name', '=', $name )->get()->first();
    }

    /**
     * Find a role by its id.
     * @param integer $id Id to search for
     * @return Role|null
     */
    public static function getRoleById( $id )
    {
        return \HelpRoleModel::find( $id );
    }


    //-------------------------------------------------------------------------
    //-------------------------------------------------------------------------
    // ROLES
    //-------------------------------------------------------------------------
    //-------------------------------------------------------------------------

    /**
     * Checks if the current user has a Role by its name
     * @param string $name Role name.
     * @return bool
     */
    public static function hasRole($role)
    {
        if( $user = self::user() ) {
            return $user->hasRole($role);
        }

        return false;
    }

    /**
     * Check if the current user has a permission by its name
     * @param string $permission Permission string.
     * @return bool
     */
    public static function can( $permission )
    {
        if( $user = self::user() ) {
            return $user->can( $permission );
        }

        return false;
    }

    /**
     * Filters a route for the name Role.
     * If the third parameter is null then return 403.
     * Overwise the $result is returned.
     * @param string       $route      Route pattern. i.e: "admin/*"
     * @param array|string $roles      The role(s) needed.
     * @param mixed        $result     i.e: Redirect::to('/')
     * @param bool         $cumulative Must have all roles.
     * @return mixed
     */
    public static function routeNeedsRole($route, $roles, $result = null, $cumulative=true)
    {
        if (!$result instanceof Closure) 
        {
            $result = function () use ($roles, $result, $cumulative) 
            {
            	if( $user = self::user() )
            	{
            		if (!is_array($roles)) {
			            $roles = array($roles);
			        }

	                $hasARole = array();
	                foreach ($roles as $role) {
	                    if( $user->hasRole($role) ) {
	                        $hasARole[] = true;
	                    } else {
	                        $hasARole[] = false;
	                    }
	                }
	            }

                // Check to see if it is false and then
                // check additive flag and that the array only contains false.
                if( !$user || in_array(false, $hasARole) && ($cumulative || count(array_unique($hasARole)) == 1) ) {
                    if(! $result)
                        Facade::getFacadeApplication()->abort(403);

                    return $result;
                }
            };
        }

        $filter_name = implode('_',$roles).'_'.substr(md5($route),0,6);

        // Registers a new filter
        Route::filter($filter_name, $result);

        // Assigns a route pattern to the
        // previously created filter.
        Route::when( $route, $filter_name );
    }

    /**
     * Filters a route for the permission.
     * If the third parameter is null then return 403.
     * Overwise the $result is returned.
     * @param string       $route       Route pattern. i.e: "admin/*"
     * @param array|string $permissions The permission needed.
     * @param mixed        $result      i.e: Redirect::to('/')
     * @param bool         $cumulative  Must have all permissions
     * @return mixed
     */
    public static function routeNeedsPermission($route, $permissions, $result = null, $cumulative=true)
    {
        if (!$result instanceof Closure) {

            $result = function () use ($permissions, $result, $cumulative) 
            {
            	if( $user = self::user() )
            	{
	            	if (!is_array($permissions)) {
			            $permissions = array($permissions);
			        }

	                $hasAPermission = array();
	                foreach ($permissions as $permission) 
	                {
	                    if ($user->can($permission)) {
	                        $hasAPermission[] = true;
	                    } else {
	                        $hasAPermission[] = false;
	                    }
	                }
	            }

                // Check to see if it is false and then
                // check additive flag and that the array only contains false.
                if( !$user || in_array(false, $hasAPermission) && ($cumulative || count(array_unique($hasAPermission)) == 1) ) {
                    if(! $result)
                        Facade::getFacadeApplication()->abort(403);

                    return $result;
                }
            };
        }

        $filter_name = implode('_',$permissions).'_'.substr(md5($route),0,6);

        // Registers a new filter
        Route::filter($filter_name, $result);

        // Assigns a route pattern to the previously created filter.
        Route::when( $route, $filter_name );
    }

    /**
     * Filters a route for the permission.
     * If the third parameter is null then return 403.
     * Overwise the $result is returned.
     * @param string       $route       Route pattern. i.e: "admin/*"
     * @param array|string $roles       The role(s) needed.
     * @param array|string $permissions The permission needed.
     * @param mixed        $result      i.e: Redirect::to('/')
     * @param bool         $cumulative  Must have all permissions
     * @return void
     */
    public static function routeNeedsRoleOrPermission($route, $roles, $permissions, $result = null, $cumulative=false)
    {
        if (!is_array($roles)) {
            $roles = array($roles);
        }
        if (!is_array($permissions)) {
            $permissions = array($permissions);
        }

        if (!$result instanceof Closure) 
        {
            $result = function () use ($roles, $permissions, $result, $cumulative) 
            {
            	if( $user = self::user() )
            	{
	                $hasARole = array();
	                foreach ($roles as $role) {
	                    if ($user->hasRole($role)) {
	                        $hasARole[] = true;
	                    } else {
	                        $hasARole[] = false;
	                    }
	                }

	                $hasAPermission = array();
	                foreach ($permissions as $permission) {
	                    if ($user->can($permission)) {
	                        $hasAPermission[] = true;
	                    } else {
	                        $hasAPermission[] = false;
	                    }
	                }
	            }

                // Check to see if it is false and then
                // check additive flag and that the array only contains false.
                if( !$user || ((in_array(false, $hasARole) || in_array(false, $hasAPermission))) && ($cumulative || count(array_unique(array_merge($hasARole, $hasAPermission))) == 1 )) {
                    if(! $result)
                        Facade::getFacadeApplication()->abort(403);

                    return $result;
                }
            };
        }

        $filter_name = implode('_',$roles).'_'.implode('_',$permissions).'_'.substr(md5($route),0,6);

        // Registers a new filter
        Route::filter($filter_name, $result);

        // Assigns a route pattern to the previously created filter.
        Route::when( $route, $filter_name );
    }


    //-------------------------------------------------------------------------
    //-------------------------------------------------------------------------
    // PRIVATE HELPERS
    //-------------------------------------------------------------------------
    //-------------------------------------------------------------------------

    /**
     * Extracts the login attribute of the given array.
     * @param array $input An array containing the key used as login attribute.
     * @return mixed
     */
    private static function prv_extractIdentityFromArray(array $input)
    {
    	$login_attribute = Config::get('helpers::auth.login_attribute');

        if( isset( $input[ $login_attribute ] ) )
        {
            return $input[ $login_attribute ];
        }

        return null;
    }

    /**
     * Generates a random token.
     * @return string
     */
    private static function prv_generateToken()
    {
        return md5(uniqid(mt_rand(), true));
    }


    //-------------------------------------------------------------------------
    //-------------------------------------------------------------------------
    // T H R O T T L E
    //-------------------------------------------------------------------------
    //-------------------------------------------------------------------------

    /**
     * Calls throttleIdentity of the loginThrottler and returns false
     * if the throttleCount is grater than the 'throttle_limit' config.
     * Also sleeps a little in order to avoid dicionary attacks.
     * @param string $identity.
     * @return boolean False if the identity has reached the 'throttle_limit'.
     */
    private static function prv_loginThrottling( $identityString )
    {
    	// Increments and also retuns the current count
        $count = self::prv_countThrottle( $identityString );

        if ($count >= Config::get('helpers::auth.throttle_limit')) {
            return false;
        }

        // Throttling delay!
        // See: http://www.codinghorror.com/blog/2009/01/dictionary-attacks-101.html
        if ($count > 2) {
            usleep(($count-1) * 400000);
        }

        return true;
    }

    /**
     * Increments the count for the given string by one stores
     * it into cache and returns the current value for that identity.
     * @param string $identity  The login identity as string (username or email).
     * @param int    $increments     Amount that is going to be added to the throttling attemps for the given identity.
     * @return int   How many times that same string was used.
     */
    private static function prv_countThrottle( $identityString, $increments = 1 )
    {
        $count = Cache::get('login_throttling:'.md5($identityString), 0);
        $count = $count + $increments;

        if( $increments > 0 ) {
        	$tst = Config::get('helpers::auth.throttle_suspension_time');
        	Cache::put('login_throttling:'.md5($identityString), $count, $tst);
        }

        return $count;
    }
}