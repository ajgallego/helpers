<?php namespace Ajgallego\Laravel-Helpers;

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
		$this->package('ajgallego/laravel-helpers');
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
			$loader->alias('HelpZip',    				'Ajgallego\Laravel-Helpers\DataCompression\HelpZip');
			$loader->alias('HelpZipStream',    			'Ajgallego\Laravel-Helpers\DataCompression\HelpZipStream');
			
			// Data visualization
			$loader->alias('HelpDataView',    			'Ajgallego\Laravel-Helpers\DataVisualization\HelpDataView');
			
			// Datatypes
			$loader->alias('HelpArray', 				'Ajgallego\Laravel-Helpers\Datatypes\HelpArray');
			$loader->alias('HelpString', 				'Ajgallego\Laravel-Helpers\Datatypes\HelpString');
			
			// Html generation
			$loader->alias('HelpActionButton',  		'Ajgallego\Laravel-Helpers\HtmlGeneration\HelpActionButton');
			$loader->alias('HelpForm',  				'Ajgallego\Laravel-Helpers\HtmlGeneration\HelpForm');
			$loader->alias('HelpMenu',  				'Ajgallego\Laravel-Helpers\HtmlGeneration\HelpMenu');

			// Notifications
			$loader->alias('HelpNotification',			'Ajgallego\Laravel-Helpers\Notifications\HelpNotification');
			$loader->alias('HelpPersistentNotification','Ajgallego\Laravel-Helpers\Notifications\HelpPersistentNotification');
			
			// System
			$loader->alias('HelpRedirect',  			'Ajgallego\Laravel-Helpers\System\HelpRedirect');
			$loader->alias('HelpUUID',  				'Ajgallego\Laravel-Helpers\System\HelpUUID');

			// User and Roles
			$loader->alias('HelpUser',		  			'Ajgallego\Laravel-Helpers\User\HelpUser');
			$loader->alias('HelpUserModel',	  			'Ajgallego\Laravel-Helpers\User\HelpUserModel');
			$loader->alias('HelpRoleModel',	  			'Ajgallego\Laravel-Helpers\User\HelpRoleModel');

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
