<?php namespace Ajgallego\Helpers\Notifications;

use Illuminate\Support\Facades\Session;

class HelpNotification 
{
    private static $mSessionPrefix = 'help_notification';

	private static $mNotificationsTypes = ['error', 'success', 'warning', 'info'];

    private static $mNotificationFormat = '<div class="notification container alert alert-:type">
                                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                                <div>:message</div>
                                           </div>';

    private static $mNotificationIcons = ['error'   => 'fa-warning', 
                                          'success' => 'fa-thumbs-up', 
                                          'warning' => 'fa-warning', 
                                          'info'    => 'fa-info-circle' ];

	/**
     * Generic calls to Add a notification
     */
    public static function __callStatic( $_method, $_arguments )
    {
    	if( count($_arguments) == 1 && in_array( $_method, self::$mNotificationsTypes ) )
        {
            return self::prv_add( $_method, $_arguments[0] );
        }
        else if( count($_arguments) == 0 && $_method == 'show' )
        {
            return self::prv_show();
        }
        else
        {
			throw new \Exception("HelpNotification Error: Unknown method.", 1);
        }
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
     * Show notifications
     */
    private static function prv_show()
    {
        $strNotifications = '';

        foreach( self::$mNotificationsTypes as $type )
        {                
            $strNotifications .= self::prv_buildNotificationAlert( $type );
        }
 
        return $strNotifications;
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
            $strAlerts = '<p><i class="fa '. $alertIcon .'"></i><strong>'
                         .   trans('notifications.'. $_type)
                         .   '</strong>: '
                         .   $messages[0]
                         .'</p>';
        }
        else 
        {
            $strAlerts = '<p><i class="fa '. $alertIcon .'"></i><strong>'
                         .   trans('notifications.many_notifications')
                         .   '</strong>: <ul>';

            foreach( $messages as $message )
            {
                $strAlerts .= '<li>'. $message .'</li>';
            }

            $strAlerts .= '</ul></p>';
        }

        return str_replace(array(':message', ':type'), array($strAlerts, $alertClass), self::$mNotificationFormat);;
    }
}