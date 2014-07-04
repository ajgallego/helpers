<?php 

namespace Ajgallego\Helpers\System;

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
    * @return 
    */
    public static function to( $_url, $_params = null )
    {
        return new HelpRedirect( Redirect::to( $_url, $_params ) );
    }

    /**
    * Create a redirection to an action
    * @param string $route
    * @param string $params
    * @return 
    */
    public static function action( $_action, $_params = null )
    {
        return new HelpRedirect( Redirect::action( $_action, $_params ) );
    }

    /**
    * Create a redirection to a route
    * @param string $route
    * @param string $params
    * @return 
    */
    public static function route( $_route, $_params = null )
    {
        return new HelpRedirect( Redirect::route( $_route, $_params ) );
    }

    /**
    * Create a redirection to an url
    * @param string $route
    * @param string $params
    * @return 
    */
    public function withErrors( $_errors )
    {
        if( ! $_errors instanceof Illuminate\Support\MessageBag )
        {
            $errorsBag = new Illuminate\Support\MessageBag();

            if( is_array( $_errors) )
            {
                foreach( $_errors as $k => $e )
                    $errorsBag->add( $k, $e );
            }
            else
                $errorsBag->add( '', $_errors );
        }
        else
            $errorsBag = $_errors;

        return self::prv_redirect_with_errors( $errorsBag );
    }

    /**
    * Construct the redirection
    */
    private function prv_redirect_with_errors( $_errors )
    {
        $errorsBag = $_errors->all();
        $strError = '';

        if( count( $errorsBag ) > 1 )
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
                       ->withErrors( $_errors );
    }
}