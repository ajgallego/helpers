<?php namespace Ajgallego\Helpers\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
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
     * @param array $credentials     Array containing at least 'username' or 'email' and 'password'.
     *                               Optionally the 'remember' boolean.
     * @param bool  $mustBeConfirmed If true, the user must have confirmed his email account in order to log-in.
     * @return bool Success.
     */
    public static function login(array $credentials, $mustBeConfirmed = true)
    {
        $remember = ( isset($credentials['remember']) ? $credentials['remember'] : false );
        $loginAttribute = self::prv_extractIdentityFromArray( $credentials );

        if( ! self::prv_loginThrottling( $loginAttribute ) ) {
            return false;
        }

        $user = self::getUserByIdentity( $loginAttribute );

        if( $user ) 
        {
            if( ! $user->confirmed && $mustBeConfirmed ) {
                return false;
            }

            if( ! isset($credentials['password']) ) {
            	$credentials['password'] = null;
        	}

            if( ! Hash::check( $credentials['password'], $user->password ) ) {
                return false;
            }

            Auth::login($user, $remember);

            return true;
        }

        return false;
    }

    /**
     * Checks if the credentials has been throttled by too much failed login attempts
     * @param array $credentials Array with the user identity (email/username)
     * @return boolean True if the identity has reached the throttle_limit.
     */
    public static function isThrottled( array $credentials )
    {
    	$loginAttribute = self::prv_extractIdentityFromArray( $credentials );

        // Get the current count (but not increments it)
        $count = self::prv_countThrottle( $loginAttribute, 0 );

        return $count >= Config::get('laravel-helpers::auth.throttle_limit');
    }

    /**
     * Checks if the given credentials correponds to a user that exists but is not confirmed
     * @param array $credentials Array with the user identity (email/username)
     * @return boolean True if exists but is not confirmed
     */
    public static function existsButNotConfirmed( array $credentials )
    {
    	$loginAttribute = self::prv_extractIdentityFromArray( $credentials );

        $user = self::getUserByIdentity( $loginAttribute );

        if( $user )
        {
			$correctPassword = Hash::check(
	                isset( $credentials['password']) ? $credentials['password'] : null,
	                $user->password
            );

            return ( ! $user->confirmed && $correctPassword );
        }

        return false;
    }

	/**
     * Sets the 'confirmed' field of the user with the matching code to true.
     * @param string $code
     * @return bool Success
     */
    public static function confirm( $code )
    {
    	$rules = \HelpUserModel::$rules;
        $rules = array_diff(array_keys($rules), array('password_confirmation'));

		$user = \HelpUserModel::where( 'confirmation_code', '=', $code )->get()->first();
        $user->confirmed = true;

        return $user->updateUniques( $rules );
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

        DB::table('password_reminders')->insert( $values );

//TODO use Mail::queue o Mail::queueOn 
            Mail::send(
//            Config::get('laravel-helpers::auth.email_queue_name'),			// Queue name
            Config::get('laravel-helpers::auth.email_view_reset_password'),	// Email view name
            compact('user', 'token'),								// Email data
            function ($message) use ($user) {
                $message
                    ->to( $user->email )
                    ->subject( Lang::get('laravel-helpers::auth.email.password_reset.subject') );
            }
        );

        return true;
    }

    /**
     * Resets user password.
     * @param  array $input Array containing 'token', 'password' and 'password_confirmation' keys.
     * @param  \MessageBag $errors Object with the validation errors
     * @return bool Success
     */
    public static function resetPassword( array $input, &$errors=null )
    {
        $user = self::getUserByReminderToken( $input['token'] );

        if( $user )
        {
            $user->password              = $input['password'];	// Ardent library will hash it
            $user->password_confirmation = $input['password_confirmation'];

	        if( $user->updateUniques() )
	        {
	            \DB::table('password_reminders')              	// Destroy token
			            ->where('token', '=', $input['token'])
			            ->delete();

			    return true;
	        }
        }

        $errors = $user->errors();

        return false;
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
    	if( empty($identityString) || is_array($identityString) || is_object($identityString) )
    	{
            return null;
        }

    	$login_attribute = Config::get('laravel-helpers::auth.login_attribute');

        return \HelpUserModel::where( $login_attribute, '=', $identityString )->get()->first();
    }

    /**
     * Find a user by email
     * @param string $email Email to search for
     * @return User|null
     */
    public static function getUserByEmail( $email )
    {
    	if( empty($email) || is_array($email) || is_object($email) )
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
            			->subHours( Config::get('laravel-helpers::auth.password_reset_expiration', 7) )
            			->toDateTimeString();

        $user = \DB::table('password_reminders')
            			->select('email')
            			->where('token', '=', $token)
            			->where('created_at', '>=', $oldestValidDate )
            			->first();

        if ($user) {
            return self::getUserByEmail( $user->email );
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
    	$login_attribute = Config::get('laravel-helpers::auth.login_attribute');

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

        if ($count >= Config::get('laravel-helpers::auth.throttle_limit')) {
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
        	$tst = Config::get('laravel-helpers::auth.throttle_suspension_time');
        	Cache::put('login_throttling:'.md5($identityString), $count, $tst);
        }

        return $count;
    }
}