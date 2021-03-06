<?php namespace Ajgallego\LaravelHelpers\User;

use LaravelBook\Ardent\Ardent;
use Ajgallego\LaravelHelpers\User\HelpUserModel;

class HelpRoleModel extends Ardent
{
    protected $table = 'roles';
    protected $guarded = array('*');

    public static $rules = array(
                'name' => 'required|between:4,128'
    );

    /**
     * Many-to-Many relations with Users
     */
    public function users()
    {
        return $this->belongsToMany('\Ajgallego\LaravelHelpers\User\HelpUserModel', 'assigned_roles', 'role_id', 'user_id');
    }

    /**
     * Many-to-Many relations with Permission
     */
    public function perms()
    {
        return $this->belongsToMany('\Ajgallego\LaravelHelpers\User\HelpPermissionModel', 'permission_role', 'role_id', 'permission_id');
    }

    /**
     * Before save should serialize permissions to save
     * as text into the database
     *
     * @param array $value
     */
    public function setPermissionsAttribute($value)
    {
        $this->attributes['permissions'] = json_encode($value);
    }

    /**
     * When loading the object it should un-serialize permissions to be
     * usable again
     *
     * @param string $value
     * @return permissions json
     */
    public function getPermissionsAttribute($value)
    {
        return (array)json_decode($value);
    }

    /**
     * Before delete all constrained foreign relations
     *
     * @param bool $forced
     * @return bool
     */
    public function beforeDelete( $forced = false )
    {
        try {
            \DB::table('assigned_roles')->where('role_id', $this->id)->delete();
            \DB::table('permission_role')->where('role_id', $this->id)->delete();
        } catch(Exception $e) {}

        return true;
    }


    /**
     * Save permissions inputted
     * @param $inputPermissions
     */
    public function savePermissions($inputPermissions)
    {
        if(! empty($inputPermissions)) {
            $this->perms()->sync($inputPermissions);
        } else {
            $this->perms()->detach();
        }
    }

    /**
     * Attach permission to current role
     * @param $permission
     */
    public function attachPermission( $permission )
    {
        if( is_object($permission))
            $permission = $permission->getKey();

        if( is_array($permission))
            $permission = $permission['id'];

        $this->perms()->attach( $permission );
    }

    /**
     * Detach permission form current role
     * @param $permission
     */
    public function detachPermission( $permission )
    {
        if( is_object($permission))
            $permission = $permission->getKey();

        if( is_array($permission))
            $permission = $permission['id'];

        $this->perms()->detach( $permission );
    }

    /**
     * Attach multiple permissions to current role
     *
     * @param $permissions
     * @return void
     */
    public function attachPermissions($permissions)
    {
        foreach ($permissions as $permission)
        {
            $this->attachPermission($permission);
        }
    }

    /**
     * Detach multiple permissions from current role
     *
     * @param $permissions
     * @return void
     */
    public function detachPermissions($permissions)
    {
        foreach ($permissions as $permission)
        {
            $this->detachPermission($permission);
        }
    }

}