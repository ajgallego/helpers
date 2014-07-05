<?php 

namespace Ajgallego\Helpers\System;

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
    * Show a success notification
    * @param string $message to show
    */
    public function withSuccess( $_message )
    {
        self::prv_show_notification( 'success', $_message );
    }

    /**
    * Redirect with errors
    * @param string/array/messageBag/Validator $_errors
    */
    public function withErrors( $_errors )
    {
        if( $_errors instanceof \Illuminate\Support\MessageBag )
        {
            $errorsBag = $_errors;
        }
        elseif( $_errors instanceof \Illuminate\Validation\Validator )
        {
            $errorsBag = $_errors->getMessageBag();
        }
        else
        {
            $errorsBag = new \Illuminate\Support\MessageBag();

            if( is_array( $_errors ) )
            {
                foreach( $_errors as $k => $e )
                    $errorsBag->add( $k, $e );
            }
            else
                $errorsBag->add( '', $_errors );
        }

        return self::prv_redirect_with_errors( $errorsBag );
    }

    /**
    * Show a notification of different types
    */
    private function prv_show_notification( $_notification_type, $_message )
    {
        if( $errorsBag->count() > 1 )
        {
            $strError = '<ul>';
            foreach( $errorsBag as $e )
            {
                $strError .= '<li>'. $e .'</li>';
            }
            $strError .= '</ul>';
        }
        else
            $strError = $errorsBag->first();

        Notification::error( $strError );
    }

    /**
    * Construct the redirection
    */
    private function prv_redirect_with_errors( \Illuminate\Support\MessageBag $errorsBag )
    {
        //$errorsBag = $_errors->all();
        $strError = '';

        if( $errorsBag->count() > 1 )
        {
            $strError = '<ul>';
            foreach( $errorsBag as $e )
            {
                $strError .= '<li>'. $e .'</li>';
            }
            $strError .= '</ul>';
        }
        else
            $strError = $errorsBag->first();

        Notification::error( $strError );

        return $this->mRedirect
                       ->withInput()
                       ->withErrors( $errorsBag );
    }
}