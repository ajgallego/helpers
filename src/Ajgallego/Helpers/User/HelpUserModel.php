<?php namespace Ajgallego\Helpers\User;

use Illuminate\Auth\UserInterface;

use LaravelBook\Ardent\Ardent;
use Ajgallego\Helpers\User\HelpRoleModel;
use J20\Uuid\Uuid;

class HelpUserModel extends Ardent implements UserInterface 
{
    protected $table = 'users';
    protected $hidden = array('password');  // The attributes excluded from the model's JSON form.
    public static $passwordAttributes = array('password');  // List of attribute names which should be hashed. (Ardent)
    public $autoHashPasswordAttributes = true;  // Automatically replace the plain-text password (from $passwordAttributes) with the hash 
    public $autoPurgeRedundantAttributes = true;  // Automatically remove redundant attributes (i.e. confirmation fields).

    public static $rules = array(
        'email'     => 'required|email|unique:users',
        'password'  => 'required|min:4|confirmed',
        'password_confirmation' => 'min:4',
    );

    /**
     * Many-to-Many relations with Role
     */
    public function roles()
    {
        return $this->belongsToMany('Ajgallego\Helpers\User\HelpRoleModel', 'assigned_roles', 'user_id', 'role_id');
    }

    /**
     * Confirm that the user email is valid.
     * @return bool
     */
    public function confirm()
    {
        $this->confirmed = true;
        return $this->save();
    }

    /**
     * Overwrites the original save method 
     * @return User object
     */
    public function save( array $rules = array(), array $customMessages = array(), array $options = array(), \Closure $beforeSave = null, \Closure $afterSave = null )
    {
        $this->confirmation_code = md5(uniqid(mt_rand(), true));

        return parent::save( $rules, $customMessages, $options, $beforeSave, $afterSave );
    }

    /**
     * Overwrites the original save method in order to perform
     * validation before actually saving the object.
     *
     * @param array $options
     *
     * @return bool
     */
/*    public function save(array $options = array())
    {
        if ($this->isValid()) {
            return parent::save($options);
        }

        return false;
    }
*/
    /**
     * Overwrite the Ardent save method. Saves model into
     * database
     *
     * @param array $rules:array
     * @param array $customMessages
     * @param array $options
     * @param \Closure $beforeSave
     * @param \Closure $afterSave
     * @return bool
     */
    /*public function save( array $rules = array(), array $customMessages = array(), array $options = array(), \Closure $beforeSave = null, \Closure $afterSave = null )
    {
        $duplicated = false;

        // When EloquentUserProvider call updateRememberToken
        // it doesn't retrieve rules, so validation on Ardent fails
        if (!empty($this->remember_token) && empty($rules))
        {
            $rules = static::$rules;
            $rules = array_diff(array_keys($rules), array('password_confirmation'));
        }

        if(! $this->getKey())
        {
            $duplicated = static::$app['confide.repository']->userExists( $this );
        }

        if(! $duplicated)
        {
            return $this->real_save( $rules, $customMessages, $options, $beforeSave, $afterSave );
        }
        else
        {
            $this->validationErrors->add(
                'duplicated',
                static::$app['translator']->get('confide::confide.alerts.duplicated_credentials')
            );

            return false;
        }
    }
    */

    /**
     * Ardent method overloading:
     * Before save the user. Generate a confirmation
     * code if is a new user.
     *
     * @param bool $forced
     * @return bool
     */
/*    public function beforeSave($forced = false)
    {
        $id=$this->getKey();
        if ( empty($id) )
        {
            $this->confirmation_code = md5( uniqid(mt_rand(), true) );
        }

        // Remove password_confirmation field before save to
        // database.
        if ( isset($this->password_confirmation) )
        {
            unset( $this->password_confirmation );
        }

        return true;
    }*/

    /**
     * Ardent method overloading:
     * After save, delivers the confirmation link email.
     * code if is a new user.
     *
     * @param $success
     * @param bool $forced
     * @return bool
     */
/*    public function afterSave($success=true, $forced = false)
    {
        if (! $this->confirmed && ! \Cache::get('confirmation_email_'.$this->getKey()) )
        {
            // on behalf or the config file we should send and email or not
            if( \Config::get('confide::signup_email') == true )
            {
                $view = static::$app['config']->get('confide::email_account_confirmation');
                $this->sendEmail( 'confide::confide.email.account_confirmation.subject', $view, array('user' => $this) );
            }
            // Save in cache that the email has been sent.
            $signup_cache = (int)static::$app['config']->get('confide::signup_cache');
            if( $signup_cache !== 0 )
            {
                static::$app['cache']->put('confirmation_email_'.$this->getKey(), true, $signup_cache);
            }
        }

        return true;
    }*/

    /**
     * Runs the real eloquent save method or returns
     * true if it's under testing. Because Eloquent
     * and Ardent save methods are not Confide's
     * responsibility.
     *
     * @param array $rules
     * @param array $customMessages
     * @param array $options
     * @param \Closure $beforeSave
     * @param \Closure $afterSave
     * @return bool
     */
/*    protected function real_save( array $rules = array(), array $customMessages = array(), array $options = array(), \Closure $beforeSave = null, \Closure $afterSave = null )
    {
        if ( defined('CONFIDE_TEST') )
        {
            $this->beforeSave();
            $this->afterSave(true);
            return true;
        }
        else{

            
            // This will make sure that a non modified password
            // will not trigger validation error.
            // @fixed Pull #110
            if( isset($rules['password']) && $this->password == $this->getOriginal('password') )
            {
                unset($rules['password']);
                unset($rules['password_confirmation']);
            }

            return parent::save( $rules, $customMessages, $options, $beforeSave, $afterSave );
        }
    }
*/


    //-------------------------------------------------------------------------
    //-------------------------------------------------------------------------
    // ROLES FUNCTIONS
    //-------------------------------------------------------------------------
    //-------------------------------------------------------------------------

    /**
     * Checks if the user has a Role by its name
     * @param string $name Role name.
     * @return boolean
     */
    public function hasRole( $name )
    {
        foreach ($this->roles as $role) {
            if( $role->name == $name )
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has a permission by its name
     * @param string $permission Permission string.
     * @return boolean
     */
    public function can( $permission )
    {
        foreach ($this->roles as $role) {
            // Deprecated permission value within the role table.
            if( is_array($role->permissions) && in_array($permission, $role->permissions) )
            {
                return true;
            }

            // Validate against the Permission table
            foreach($role->perms as $perm) {
                if($perm->name == $permission) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks role(s) and permission(s) and returns bool, array or both
     * @param string|array $roles Array of roles or comma separated string
     * @param string|array $permissions Array of permissions or comma separated string.
     * @param array $options validate_all (true|false) or return_type (boolean|array|both) Default: false | boolean
     * @return array|bool
     * @throws InvalidArgumentException
     */
    public function ability( $roles, $permissions, $options=array() ) 
    {
        // Convert string to array if that's what is passed in.
        if(!is_array($roles)){
            $roles = explode(',', $roles);
        }
        if(!is_array($permissions)){
            $permissions = explode(',', $permissions);
        }

        // Set up default values and validate options.
        if(!isset($options['validate_all'])) {
            $options['validate_all'] = false;
        } else {
            if($options['validate_all'] != true && $options['validate_all'] != false) {
                throw new InvalidArgumentException();
            }
        }
        if(!isset($options['return_type'])) {
            $options['return_type'] = 'boolean';
        } else {
            if($options['return_type'] != 'boolean' &&
                $options['return_type'] != 'array' &&
                $options['return_type'] != 'both') {
                throw new InvalidArgumentException();
            }
        }

        // Loop through roles and permissions and check each.
        $checkedRoles = array();
        $checkedPermissions = array();
        foreach($roles as $role) {
            $checkedRoles[$role] = $this->hasRole($role);
        }
        foreach($permissions as $permission) {
            $checkedPermissions[$permission] = $this->can($permission);
        }

        // If validate all and there is a false in either
        // Check that if validate all, then there should not be any false.
        // Check that if not validate all, there must be at least one true.
        if(($options['validate_all'] && !(in_array(false,$checkedRoles) || in_array(false,$checkedPermissions))) ||
            (!$options['validate_all'] && (in_array(true,$checkedRoles) || in_array(true,$checkedPermissions)))) {
            $validateAll = true;
        } else {
            $validateAll = false;
        }

        // Return based on option
        if($options['return_type'] == 'boolean') {
            return $validateAll;
        } elseif($options['return_type'] == 'array') {
            return array('roles' => $checkedRoles, 'permissions' => $checkedPermissions);
        } else {
            return array($validateAll, array('roles' => $checkedRoles, 'permissions' => $checkedPermissions));
        }

    }

    /**
     * Alias to eloquent many-to-many relation's
     * attach() method
     * @param mixed $role
     * @return void
     */
    public function attachRole( $role )
    {
        if( is_object($role))
            $role = $role->getKey();

        if( is_array($role))
            $role = $role['id'];

        $this->roles()->attach( $role );
    }

    /**
     * Alias to eloquent many-to-many relation's
     * detach() method
     * @param mixed $role
     * @return void
     */
    public function detachRole( $role )
    {
        if( is_object($role))
            $role = $role->getKey();

        if( is_array($role))
            $role = $role['id'];

        $this->roles()->detach( $role );
    }

    /**
     * Attach multiple roles to a user
     * @param $roles
     * @return void
     */
    public function attachRoles($roles)
    {
        foreach ($roles as $role)
        {
            $this->attachRole($role);
        }
    }

    /**
     * Detach multiple roles from a user
     * @param $roles
     * @return void
     */
    public function detachRoles($roles)
    {
        foreach ($roles as $role)
        {
            $this->detachRole($role);
        }
    }


    //-------------------------------------------------------------------------
    //-------------------------------------------------------------------------
    // USER INTERFACE AND REMINDABLE INTERFACE IMPLEMENTATION
    //-------------------------------------------------------------------------
    //-------------------------------------------------------------------------

    /**
     * Get the unique identifier for the user.
     * @see \Illuminate\Auth\UserInterface
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        // Get the value of the model's primary key.
        return $this->getKey();
    }

    /**
     * Get the password for the user.
     * @see \Illuminate\Auth\UserInterface
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the token value for the "remember me" session.
     * @see \Illuminate\Auth\UserInterface
     * @return string
     */
    public function getRememberToken()
    {
        return $this->{$this->getRememberTokenName()};
    }

    /**
     * Set the token value for the "remember me" session.
     * @see \Illuminate\Auth\UserInterface
     * @param string $value
     */
    public function setRememberToken($value)
    {
        $this->{$this->getRememberTokenName()} = $value;
    }

    /**
     * Get the column name for the "remember me" token.
     * @see \Illuminate\Auth\UserInterface
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    /**
     * Get the e-mail address where password reminders are sent.
     * @see \Illuminate\Auth\Reminders\RemindableInterface
     * @return string
     */
    public function getReminderEmail()
    {
        return $this->email;
    }
}
