<?php namespace Ajgallego\Laravel-Helpers;

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
        return $this->belongsToMany('Ajgallego\Laravel-Helpers\User\HelpRoleModel', 'permission_role', 'role_id', 'permission_id');
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
    
    
    
    //------------------------------------------
    /*public function preparePermissionsForDisplay($permissions)
    {
        // Get all the available permissions
        $availablePermissions = $this->all()->toArray();

        foreach($permissions as &$permission) {
            array_walk($availablePermissions, function(&$value) use(&$permission){
                if($permission->name == $value['name']) {
                    $value['checked'] = true;
                }
            });
        }
        return $availablePermissions;
    }*/

    /**
     * Convert from input array to savable array.
     * @param $permissions
     * @return array
     */
    /*public function preparePermissionsForSave( $permissions )
    {
        $availablePermissions = $this->all()->toArray();
        $preparedPermissions = array();
        foreach( $permissions as $permission => $value )
        {
            // If checkbox is selected
            if( $value == '1' )
            {
                // If permission exists
                array_walk($availablePermissions, function(&$value) use($permission, &$preparedPermissions){
                    if($permission == (int)$value['id']) {
                        $preparedPermissions[] = $permission;
                    }
                });
            }
        }
        return $preparedPermissions;
    }*/
}
