<?php 

namespace Ajgallego\Helpers;

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
			$loader->alias('HelpZip',    			'Ajgallego\Helpers\DataCompression\HelpZip');
			$loader->alias('HelpZipStream',    		'Ajgallego\Helpers\DataCompression\HelpZipStream');
			$loader->alias('HelpDataView',    		'Ajgallego\Helpers\DataVisualization\HelpDataView');
			$loader->alias('HelpActionButton',  	'Ajgallego\Helpers\HtmlGeneration\HelpActionButton');
			$loader->alias('HelpRedirect',  		'Ajgallego\Helpers\System\HelpRedirect');
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
