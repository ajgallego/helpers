<?php namespace Ajgallego\Helpers;

use LaravelBook\Ardent\Ardent;

class HelpPermissionModel extends Ardent
{
    protected $table = 'permissions';
    protected $guarded = array('*');

    public static $rules = array(
                'name'          => 'required|between:4,128',
                'display_name'  => 'required|between:4,128'
    );

    /**
     * Many-to-Many relations with Roles
     */
    public function roles()
    {
        return $this->belongsToMany('Ajgallego\Helpers\User\HelpRoleModel', 'permission_role', 'role_id', 'permission_id');
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
            \DB::table('permission_role')->where('permission_id', $this->id)->delete();
        } catch (Exception $e) {}

        return true;
    }

}