<?php namespace Ajgallego\Helpers\System;

/**
* Slug Helper class. 
*/
class HelpSlug
{
    /**
    * Returns an unique slug from an input String. It compares the 
    * generated slug with the model in order to be unique.
    * @param string $fromStr Input string to generate the new slug
    * @param string $model Eloquent model where slugs are stored
    * @param string $slugColumnName Name of the column in the model where the slugs are stored. Default: 'slug'
    * @return string Generated slug
    */
    public static function getSlug( $fromStr, $model, $slugColumnName = 'slug' ) 
    {
        $slug = Str::slug( $fromStr );

        $slugs = $model->whereRaw( "$slugColumnName REGEXP '^{$slug}(-[0-9]*)?$'" )->select($slugColumnName);

        if( $slugs->count() === 0 )
        {
            return $slug;
        }

        // Get slugs in reverse order and get first (it solves the error 
        // when an slug in the series is removed)
        $lastSlugNumber = intval(str_replace($slug . '-', '', $slugs->orderBy($slugColumnName, 'desc')->first()->$slugColumnName));

        return $slug .'-'. ($lastSlugNumber + 1);
    }

}