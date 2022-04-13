<?php

namespace Dryven\Faviconator;

use Statamic\Statamic;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Dryven\Faviconator\Tags\FaviconatorTags;
use Statamic\Providers\AddonServiceProvider;
use Dryven\Faviconator\Commands\GenerateFavicons;

/**
 * The connection point between Laravel, Statamic and this addon.
 * This is where the magic begins.
 *
 * Class ServiceProvider
 * @package Dryven\Faviconator
 * @author dryven
 */
class ServiceProvider extends AddonServiceProvider
{

	protected $tags = [
		FaviconatorTags::class,
	];

	protected $commands = [
		GenerateFavicons::class,
	];

	protected $routes = [
		'cp' => __DIR__ . '/../routes/cp.php',
	];

	protected $publishAfterInstall = false;

	public function boot()
	{
		parent::boot();

		Statamic::booted(function () {
			$this
				->bootPermissions()
				->bootNavigation();

			$this->loadTranslationsFrom(__DIR__ . '/../resources/lang', Faviconator::NAMESPACE);
			$this->loadViewsFrom(__DIR__ . '/../resources/views', Faviconator::NAMESPACE);
		});

		Statamic::afterInstalled(function ($command) {
			$command->call('vendor:publish', ['--tag' => Faviconator::VENDOR_CONFIG_KEY]);
		});
	}

	/**
	 * Creates navigation item for this addon's control panel settings.
	 *
	 * @return $this
	 */
	protected function bootNavigation(): ServiceProvider
	{
		Nav::extend(function ($nav) {
			$nav
				->create(Faviconator::getCpTranslation('navigation_item'))
				->can(Faviconator::PERMISSION_GENERAL_KEY)
				->route(Faviconator::ROUTE_SETTINGS_INDEX)
				->section('Tools')
				->icon('browser-com');
		});

		return $this;
	}

	/**
	 * Registers the permissions. Gives the users more control who can do what.
	 *
	 * @return $this
	 */
	protected function bootPermissions(): ServiceProvider
	{
		// Add permission group for this addon
		Permission::group(
			Faviconator::PERMISSION_SETTINGS_KEY,
			Faviconator::getCpTranslation('permission_settings'),
			function () {
				// Add permission for configuring the settings
				Permission::register(Faviconator::PERMISSION_GENERAL_KEY)
					->label(Faviconator::getCpTranslation('permission_general'))
					->description(Faviconator::getCpTranslation('permission_general_description'));
			}
		);

		return $this;
	}

	/**
	 * Registers all publishables available through Artisan's vendor:publish.
	 *
	 * @return $this
	 */
	protected function bootPublishables(): ServiceProvider
	{
		parent::bootPublishables();

		$this->publishes([
			__DIR__ . '/../resources/views' => resource_path('views/vendor/' . Faviconator::NAMESPACE),
		], Faviconator::VENDOR_VIEWS_KEY);

		$this->publishes([
			__DIR__ . '/../resources/lang' => resource_path('lang/vendor/' . Faviconator::NAMESPACE),
		], Faviconator::VENDOR_LANGUAGES_KEY);

		$this->publishes([
			__DIR__ . '/../config' => config_path(),
		], Faviconator::VENDOR_CONFIG_KEY);

		return $this;
	}
}
