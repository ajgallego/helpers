<?php namespace Ajgallego\Helpers;

use Illuminate\Support\ServiceProvider;

class HelpersServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('ajgallego/helpers');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->booting(function()
		{
			$loader = \Illuminate\Foundation\AliasLoader::getInstance();
			
			// Data compression
			$loader->alias('HelpZip',    				'Ajgallego\Helpers\DataCompression\HelpZip');
			$loader->alias('HelpZipStream',    			'Ajgallego\Helpers\DataCompression\HelpZipStream');
			
			// Data visualization
			$loader->alias('HelpDataView',    			'Ajgallego\Helpers\DataVisualization\HelpDataView');
			
			// Datatypes
			$loader->alias('HelpArray', 				'Ajgallego\Helpers\Datatypes\HelpArray');
			$loader->alias('HelpString', 				'Ajgallego\Helpers\Datatypes\HelpString');
			
			// Html generation
			$loader->alias('HelpActionButton',  		'Ajgallego\Helpers\HtmlGeneration\HelpActionButton');
			$loader->alias('HelpForm',  				'Ajgallego\Helpers\HtmlGeneration\HelpForm');
			$loader->alias('HelpMenu',  				'Ajgallego\Helpers\HtmlGeneration\HelpMenu');

			// Notifications
			$loader->alias('HelpNotification',			'Ajgallego\Helpers\Notifications\HelpNotification');
			$loader->alias('HelpPersistentNotification','Ajgallego\Helpers\Notifications\HelpPersistentNotification');
			
			// System
			$loader->alias('HelpRedirect',  			'Ajgallego\Helpers\System\HelpRedirect');
			$loader->alias('HelpUUID',  				'Ajgallego\Helpers\System\HelpUUID');
			$loader->alias('HelpSlug',  				'Ajgallego\Helpers\System\HelpSlug');

			// User and Roles
			$loader->alias('HelpUser',		  			'Ajgallego\Helpers\User\HelpUser');
			$loader->alias('HelpUserModel',	  			'Ajgallego\Helpers\User\HelpUserModel');
			$loader->alias('HelpRoleModel',	  			'Ajgallego\Helpers\User\HelpRoleModel');

		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
