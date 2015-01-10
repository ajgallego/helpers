<?php namespace Ajgallego\LaravelHelpers\System;

use Illuminate\Support\Facades\Redirect;
use Krucas\Notification\Facades\Notification;

/**
* Helper class for redirections. 
* Allows to add string errors and to show a notification. 
*/
class HelpRedirect
{
    private $mRedirect;

    /**
    * Private constructor
    */
    private function __construct( $_redirect )
    {
        $this->mRedirect = $_redirect;
    }

    /**
    * Create a redirection to an url
    * @param string $url
    * @param string $params
    * @return Redirection
    */
    public static function to( $_url, $_params = null )
    {
        return new HelpRedirect( Redirect::to( $_url, $_params ) );
    }

    /**
    * Create a redirection to an action
    * @param string $route
    * @param string $params
    * @return Redirection
    */
    public static function action( $_action, $_params = null )
    {
        return new HelpRedirect( Redirect::action( $_action, $_params ) );
    }

    /**
    * Create a redirection to a route
    * @param string $route
    * @param string $params
    * @return Redirection
    */
    public static function route( $_route, $_params = null )
    {
        return new HelpRedirect( Redirect::route( $_route, $_params ) );
    }

    /**
    * Redirect with success message
    * @param string/array/messageBag/Validator/array $message to show
    */
    public function withSuccess( $_message )
    {
        $messageBag = self::prv_buildMessageBag( $_message );
        return self::prv_redirect( 'success', $messageBag );
    }

    /**
    * Redirect with info message
    * @param string/array/messageBag/Validator $message to show
    */
    public function withInfo( $_message )
    {
        $messageBag = self::prv_buildMessageBag( $_message );
        return self::prv_redirect( 'info', $messageBag );
    }

    /**
    * Redirect with warning message
    * @param string/array/messageBag/Validator $message to show
    */
    public function withWarning( $_message )
    {
        $messageBag = self::prv_buildMessageBag( $_message );
        return self::prv_redirect( 'warning', $messageBag );
    }

    /**
    * Redirect with errors
    * @param string/array/messageBag/Validator $_errors to show
    */
    public function withErrors( $_errors )
    {
        $errorsBag = self::prv_buildMessageBag( $_errors );
        return self::prv_redirect( 'error', $errorsBag );
    }

    /**
    * Construct the message bag from different inputs
    */
    private function prv_buildMessageBag( $_messages )
    {
        if( $_messages instanceof \Illuminate\Support\MessageBag )
        {
            $messagesBag = $_messages;
        }
        elseif( $_messages instanceof \Illuminate\Validation\Validator )
        {
            $messagesBag = $_messages->getMessageBag();
        }
        else
        {
            $messagesBag = new \Illuminate\Support\MessageBag();

            if( is_array( $_messages ) )
            {
                foreach( $_messages as $k => $m )
                    $messagesBag->add( $k, $m );
            }
            else
                $messagesBag->add( '', $_messages );
        }

        return $messagesBag;
    }

    /**
    * Construct the redirection
    */
    private function prv_redirect( $type, \Illuminate\Support\MessageBag $messagesBag )
    {
        $strMessages = '';

        if( $messagesBag->count() > 1 )
        {
            $strMessages = '<ul>';
            foreach( $messagesBag as $e )
            {
                $strMessages .= '<li>'. $e .'</li>';
            }
            $strMessages .= '</ul>';
        }
        else
            $strMessages = $messagesBag->first();

        Notification::$type( $strMessages );

        if( $type == 'error' )
            return $this->mRedirect->withInput()->withErrors( $messagesBag );
        else
            return $this->mRedirect;
    }
}