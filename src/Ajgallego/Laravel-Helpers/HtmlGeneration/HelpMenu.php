<?php namespace Ajgallego\Laravel-Helpers\HtmlGeneration;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;

class HelpMenu
{
	/**
	* Build a navigation menu from an array with the following structure: <br/>
	* $navbar = array( <br/>
	*		['url'=>'url',           'textkey'=>'public.title', 'nopattern' => true ], <br/>
	*		['url'=>'url/products',  'textkey'=>'public.products.title' ], <br/>
	*		['url'=>'url/clients',   'textkey'=>'public.clients.title', 'icon' => 'fa-users' ], <br/>
	*);
	*/
	public static function build( array $_menu, $_currentLocale = '', $_addItemClass = '' )
	{
		$str = '';

		foreach( $_menu as $key => $option )
		{
			$icon = isset( $option['icon'] ) ? '<i class="fa '. $option['icon'] .' fa-fw"></i> ' : '';

			if( isset( $option['divider'] ) )
			{
				$str .= '<li class="divider"></li>';
			}
			else if( isset( $option['submenu'] ) )
			{
				$requestPattern = $_currentLocale . ( $option['url'] == '' ? '' : '/'. $option['url'] . '*' );
	        	$isActive = '';
	        	$isOpenned = ''; 
	        	if( Request::is( $requestPattern ) ) {
	        		$isActive =  ' active';
	        		$isOpenned = ' in';
	        	}

				$str .= '<li class="'. $_addItemClass .' list-toggle'. $isActive .'">'
						.   '<a class="accordion-toggle" href="#collapse-'.$key.'" data-toggle="collapse">'
						. 		$icon . trans( $option['textkey'] )
						.	'</a>'
						.	'<ul id="collapse-'.$key.'" class="collapse'. $isOpenned .'">'
                        .		self::build( $option['submenu'], $_currentLocale )
                        .	'</ul>'
                        .'</li>';
			}
			else
			{
				$submenusPattern = (isset($option['nopattern']) ? '' : '*' );
	        	        $requestPattern = $_currentLocale . ( $option['url'] == '' ? '' : '/'. $option['url'] . $submenusPattern );
	        	        //$requestPattern = $_currentLocale . $option['url'] . $submenusPattern;

	        	$isActive = \Request::is( $requestPattern ) ? ' active' : '';

	            $str .= '<li class="'. $_addItemClass . $isActive .'">'
	            		.	'<a href="'. \URL::to( $option['url'] ) .'">'
	                    .		$icon . trans( $option['textkey'] )
	                    .	'</a>'
	                    .'</li>';
	        }
        }

        return $str;
	}
}