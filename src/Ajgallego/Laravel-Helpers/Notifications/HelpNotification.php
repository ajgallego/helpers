<?php namespace Ajgallego\Laravel-Helpers\Notifications;

use Illuminate\Support\Facades\Session;

class HelpNotification 
{
    private static $mSessionPrefix = 'help_notification';

	private static $mNotificationsTypes = ['error', 'success', 'warning', 'info'];

    private static $mNotificationFormat = '<div class="notification alert alert-:type">
                                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                                <div>:message</div>
                                           </div>';

    private static $mNotificationIcons = ['error'   => 'fa-warning', 
                                          'success' => 'fa-thumbs-up', 
                                          'warning' => 'fa-warning', 
                                          'info'    => 'fa-info-circle' ];

	/**
     * Generic calls to Add and Show notifications
     */
    public static function __callStatic( $_method, $_arguments )
    {
    	if( count($_arguments) == 1 && in_array( $_method, self::$mNotificationsTypes ) )
        {
            return self::prv_add( $_method, $_arguments[0] );
        }
        else
        {
			throw new \Exception("HelpNotification Error: Unknown method or wrong number of arguments.", 1);
        }
    }

    /**
     * Show notifications
     */
    public static function show()
    {
        $strNotifications = '';

        foreach( self::$mNotificationsTypes as $type )
        {                
            $strNotifications .= self::prv_buildNotificationAlert( $type );
        }
 
        return $strNotifications;
    }

    /**
     * Get all notifications
     */
    public static function get()
    {
        $arrayNotifications = '';

        foreach( self::$mNotificationsTypes as $type )
        {                
            $arrayNotifications[$type] = \Session::pull( self::$mSessionPrefix .'.'. $type, array() );
        }
 
        return $arrayNotifications;
    }

    /**
     * Add a notification
     */
    private static function prv_add( $_type, $_messages )
    {
        if( $_messages instanceof \Illuminate\Support\MessageBag )
        {
            $_messages = $_messages->all();
        }
        elseif( $_messages instanceof \Illuminate\Validation\Validator )
        {
            $_messages = $_messages->getMessageBag()->all();
        }

        
        if( is_array( $_messages ) )
        {
            foreach( $_messages as $k => $m )
                \Session::push( self::$mSessionPrefix .'.'. $_type, $m );
        }
        else
            \Session::push( self::$mSessionPrefix .'.'. $_type, $_messages );
    }

    /**
     * Build a notification alert
     */
    private static function prv_buildNotificationAlert( $_type )
    {
        $strAlerts = '';
        $alertClass = ( $_type == 'error' ? 'danger' : $_type );
        $alertIcon = self::$mNotificationIcons[ $_type ];
        $messages = \Session::pull( self::$mSessionPrefix .'.'. $_type, array() );

        if( empty( $messages ) )
        {
            return '';
        }
        else if( count( $messages ) == 1 )
        {
            $strAlerts = '<span class="title"><i class="fa fa-fw '. $alertIcon .'"></i>'
                         .   trans('notifications.'. $_type)
                         .'</span><p>'
                         .   $messages[0]
                         .'</p>';
        }
        else 
        {
            $strAlerts = '<span class="title"><i class="fa fa-fw '. $alertIcon .'"></i>'
                         .   trans('notifications.many_notifications')
                         .   '</span><p><ul>';

            foreach( $messages as $message )
            {
                $strAlerts .= '<li>'. $message .'</li>';
            }

            $strAlerts .= '</ul></p>';
        }

        return str_replace(array(':message', ':type'), array($strAlerts, $alertClass), self::$mNotificationFormat);;
    }
}