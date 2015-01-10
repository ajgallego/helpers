<?php namespace Ajgallego\Laravel-Helpers;

use Illuminate\Database\Eloquent\Model;

class PersistentNotification extends \Eloquent 
{
    protected $table = 'persistent_notifications';
    protected $guarded = array('*');

    public static $rules = array( 
                'user_id' => 'required', 
                'group'   => 'required'
                'type'    => 'required|in:error,success,warning,info',
                'message' => 'required',
                'url'     => 'sometimes|url' 
    );

    /**
    * Relationships
    */
    public function user()
    {
        return $this->belongsTo('User');
    }
}