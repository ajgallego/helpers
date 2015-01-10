<?php namespace Ajgallego\LaravelHelpers;

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
			$loader->alias('HelpZip',    				'Ajgallego\LaravelHelpers\DataCompression\HelpZip');
			$loader->alias('HelpZipStream',    			'Ajgallego\LaravelHelpers\DataCompression\HelpZipStream');
			
			// Data visualization
			$loader->alias('HelpDataView',    			'Ajgallego\LaravelHelpers\DataVisualization\HelpDataView');
			
			// Datatypes
			$loader->alias('HelpArray', 				'Ajgallego\LaravelHelpers\Datatypes\HelpArray');
			$loader->alias('HelpString', 				'Ajgallego\LaravelHelpers\Datatypes\HelpString');
			
			// Html generation
			$loader->alias('HelpActionButton',  		'Ajgallego\LaravelHelpers\HtmlGeneration\HelpActionButton');
			$loader->alias('HelpForm',  				'Ajgallego\LaravelHelpers\HtmlGeneration\HelpForm');
			$loader->alias('HelpMenu',  				'Ajgallego\LaravelHelpers\HtmlGeneration\HelpMenu');

			// Notifications
			$loader->alias('HelpNotification',			'Ajgallego\LaravelHelpers\Notifications\HelpNotification');
			$loader->alias('HelpPersistentNotification','Ajgallego\LaravelHelpers\Notifications\HelpPersistentNotification');
			
			// System
			$loader->alias('HelpRedirect',  			'Ajgallego\LaravelHelpers\System\HelpRedirect');
			$loader->alias('HelpUUID',  				'Ajgallego\LaravelHelpers\System\HelpUUID');

			// User and Roles
			$loader->alias('HelpUser',		  			'Ajgallego\LaravelHelpers\User\HelpUser');
			$loader->alias('HelpUserModel',	  			'Ajgallego\LaravelHelpers\User\HelpUserModel');
			$loader->alias('HelpRoleModel',	  			'Ajgallego\LaravelHelpers\User\HelpRoleModel');

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
