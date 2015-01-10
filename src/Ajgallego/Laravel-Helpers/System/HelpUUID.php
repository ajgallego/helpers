<?php namespace Ajgallego\Laravel-Helpers\System;

/**
* UUID<br/>
* Generate a Universally Unique Identifier
*/
class HelpUUID 
{
    /**
     * Generates a UUID v2
     * @return string
     */
    protected function v2()
    {
        return md5( uniqid(mt_rand(), true) );
    }

    /**
    * Returns a random hash
    */
    public static function generateRandomHashedKey( $_baseKey = '' ) 
    {
        return sha1( $_baseKey . uniqid(mt_rand(), true) . time() );
    }

    /**
    * Generate a UUID v4<br/>
    * The UUID is 36 characters with dashes, 32 characters without.
    * @param bool $dashes Optional parameter (default true). If true add dashes to the generated UUID
    * @return string E.g. 67f71e26-6d76-4d6b-9b6b-944c28e32c9d
    */
    public static function v4($dashes = true)
    {
        if ($dashes)
        {
            $format = '%s-%s-%04x-%04x-%s';
        }
        else
        {
            $format = '%s%s%04x%04x%s';
        }

        return sprintf($format,

            // 8 hex characters
            bin2hex(openssl_random_pseudo_bytes(4)),

            // 4 hex characters
            bin2hex(openssl_random_pseudo_bytes(2)),

            // "4" for the UUID version + 3 hex characters
            mt_rand(0, 0x0fff) | 0x4000,

            // (8, 9, a, or b) for the UUID variant + 3 hex characters
            mt_rand(0, 0x3fff) | 0x8000,

            // 12 hex characters
            bin2hex(openssl_random_pseudo_bytes(6))
        );
    }

}
