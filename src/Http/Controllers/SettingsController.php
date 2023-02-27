<?php

namespace Dryven\Faviconator\Http\Controllers;

use Statamic\Facades\Site;
use Statamic\Facades\User;
use Illuminate\Http\Request;
use Dryven\Faviconator\Faviconator;
use Statamic\Http\Controllers\CP\CpController;
use Dryven\Faviconator\Configuration\FaviconatorConfig;

/**
 * Class SettingsController
 * @package Dryven\Faviconator\Http\Controllers
 * @author dryven
 */
class SettingsController extends CpController
{
	public function index()
	{
		// No access if the user doesn't have the right permissions to show them
		abort_unless(User::current()->hasPermission('super') ||
			User::current()->hasPermission(Faviconator::PERMISSION_GENERAL_KEY), 403);

		$config = $this->getFaviconatorConfig();

		return view(Faviconator::getNamespacedKey('settings'), [
			'title' => Faviconator::getCpTranslation('title'),
			'action' => cp_route(Faviconator::ROUTE_SETTINGS_INDEX),
			'blueprint' => $config->blueprint()->toPublishArray(),
			'values' => $config->values(),
			'meta' => $config->fields()->meta()
		]);
	}

	public function update(Request $request)
	{
		// No access if the user doesn't have the right permissions to edit them
		abort_unless(User::current()->hasPermission('super') ||
			User::current()->hasPermission(Faviconator::PERMISSION_GENERAL_KEY), 403);

		$config = $this->getFaviconatorConfig();

		$values = $config->validatedValues($request);

		$config->setValues($values)->save();
	}

	private function getFaviconatorConfig(): FaviconatorConfig
	{
		return FaviconatorConfig::create(Site::selected()->handle());
	}
}
