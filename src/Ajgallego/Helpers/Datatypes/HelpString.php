<?php namespace Ajgallego\Helpers\Datatypes;

/**
* String helper class
*/
class HelpString
{
    //-------------------------------------------------------------------------
    public static function getRandomHashedKey( $baseKey = '' ) 
    {
    	return sha1( $baseKey . uniqid(mt_rand(), true) . time() );
    }

    //--------------------------------------------------------------------------
    public static function startsWith($haystack, $needle)
    {
        return !strncmp($haystack, $needle, strlen($needle));
    }

    //--------------------------------------------------------------------------
    public static function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }

}