<?php namespace Ajgallego\LaravelHelpers\Notifications;

class HelpPersistentNotification 
{
	private static $mNotificationsTypes = ['error', 'success', 'warning', 'info'];

	/**
     * Generic calls to Add and Show notifications
     */
    public function __callStatic( $_method, $_arguments )
    {
    	if( ! in_array( $_type, self::$mTypes ) )
			throw new \Exception("PersistentNotification Error: Unknown method.", 1);

    	if( count($_arguments) != 3 )
			throw new \Exception("PersistentNotification Error: Wrong number of arguments.", 1);

		if( ! is_a( $_arguments[0], 'User' ) )
			throw new \Exception("PersistentNotification Error: First argument must be an instance of User", 1);	

    	return self::prv_add( $_type, $_arguments[0], $_arguments[1], $_arguments[2] );

        //return call_user_func_array(array($this->container(null), $name), $arguments);
    }

    /**
     * Generic calls to Add and Show notifications
     */
/*    public static function __callStatic( $_method, $_arguments )
    {
        if( count($_arguments) == 0 && $_method == 'show' )
        {
            return self::prv_show();
        }
        else if( count($_arguments) == 1 && in_array( $_method, self::$mNotificationsTypes ) )
        {
            return self::prv_add( $_method, $_arguments[0] );
        }
        else
        {
            throw new \Exception("HelpNotification Error: Unknown method or wrong number of arguments.", 1);
        }
    }*/

    /**
     * Add a notification
     */
    private function prv_add( $_type, $_user, $_message, $_url )
    {
    	$notif = new PersistentNotificationRepository();
    	$notif->type = $_type;
    	$notif->message = $_message;
	    $notif->url = $_url;

	    //$user = self::objectToObject($_user, 'PersistentNotification');

	    $_user->persistentNotifications()->save( $notif );


	    ///$_user->persistentNotificationRepository()->associate( $notif );
/*
    	$_user->account()->associate($account);

    	$notif = PersistentNotificationRepository::create( array(
    		'user_id' => $_user->id, 
    		'type' => $_type, 
	        'message' => $_message, 
	        'url' => $_url ));*/

    	return $notif->id;
    }


    private function objectToObject($instance, $className) 
    {
	    return unserialize(sprintf(
	        'O:%d:"%s"%s',
	        strlen($className),
	        $className,
	        strstr(strstr(serialize($instance), '"'), ':')
	    ));
	}
}