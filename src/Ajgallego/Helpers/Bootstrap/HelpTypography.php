<?php namespace Ajgallego\Helpers\DataCompression;

/** 
* 
*/
class HelpTypography
{
	/**
	* Make a paragraph stand out (increases the font size)
	*/
	public static function lead( $_text )
	{
		return '<p class="lead">'. $_text . '</p>';
	}

	/**
	* For highlighting a run of text due to its relevance in another context (it highlights the text in yellow)
	*/
	public static function mark( $_text )
	{
		return '<mark>'. $_text . '</mark>';
	}

	/**
	* For indicating blocks of text that have been deleted (it marks the text with a Strikethrough)
	*/
	public static function deleted( $_text )
	{
		return '<del>'. $_text . '</del>';
	}

	/**
	* For indicating blocks of text that are no longer relevant
	*/
	public static function strikethrough( $_text )
	{
		return '<s>'. $_text . '</s>';
	}

	/**
	* For indicating additions to the document
	*/
	public static function inserted( $_text )
	{
		return '<ins>'. $_text . '</ins>';
	}

	/**
	* Underline text
	*/
	public static function underlined( $_text )
	{
		return '<ins>'. $_text . '</ins>';
	}
}