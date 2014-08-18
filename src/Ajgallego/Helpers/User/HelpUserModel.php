<?php namespace Ajgallego\Helpers\User;

use Illuminate\Auth\UserInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use LaravelBook\Ardent\Ardent;
use Ajgallego\Helpers\User\HelpRoleModel;

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
        $rules = static::$rules;
        $rules = array_diff(array_keys($rules), array('password_confirmation'));

        $this->confirmed = true;
        return $this->updateUniques( $rules );
    }

    /**
     * Overwrites the original save method 
     * @return User object
     */
    public function save( array $rules = array(), array $customMessages = array(), array $options = array(), \Closure $beforeSave = null, \Closure $afterSave = null )
    {
        $id = $this->getKey();
        if( empty($id) )
        {
            $this->confirmation_code = md5( uniqid(mt_rand(), true) );
        }

        // If it doesn't retrieve rules, so validation on Ardent fails
        if (!empty($this->remember_token) && empty($rules))
        {
            $rules = static::$rules;
            $rules = array_diff(array_keys($rules), array('password_confirmation'));
        }

        $status = parent::save( $rules, $customMessages, $options, $beforeSave, $afterSave );

        if( $this->errors()->isEmpty() && ! $this->confirmed && \Config::get('helpers::auth.signup_email') == true )
        {
            $view_name = \Config::get('helpers::auth.email_account_confirmation');
            $user = $this;

            Mail::send(
                $view_name,
                compact('user'),
                function ($message) use ($user) {
                    $message
                        ->to( $user->email )
                        ->subject( Lang::get('helpers::auth.email.account_confirmation.subject') );
                }
            );

        }

        return $status;
    }


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
