<?php

	namespace Dryven\Faviconator\Http\Controllers;

	use Dryven\Faviconator\Configuration\FaviconatorConfig;
	use Dryven\Faviconator\Faviconator;
	use Illuminate\Http\Request;
	use Statamic\Facades\User;
	use Statamic\Http\Controllers\CP\CpController;

	/**
	 * Class SettingsController
	 * @package Dryven\Faviconator\Http\Controllers
	 * @author dryven
	 */
	class SettingsController extends CpController {

		protected $config;

		public function __construct(Request $request) {
			parent::__construct($request);

			$this->config = new FaviconatorConfig();
		}

		public function index() {
			// No access if the user doesn't have the right permissions to show them
			abort_unless(User::current()->hasPermission('super') ||
				User::current()->hasPermission(Faviconator::PERMISSION_GENERAL_KEY), 403);

			return view(Faviconator::getNamespacedKey('settings'), [
				'title' => Faviconator::getCpTranslation('title'),
				'action' => cp_route(Faviconator::ROUTE_SETTINGS_INDEX),
				'blueprint' => $this->config->blueprint()->toPublishArray(),
				'values' => $this->config->values(),
				'meta' => $this->config->fields()->meta()
			]);
		}

		public function update(Request $request) {
			// No access if the user doesn't have the right permissions to edit them
			abort_unless(User::current()->hasPermission('super') ||
				User::current()->hasPermission(Faviconator::PERMISSION_GENERAL_KEY), 403);

			$values = $this->config->validatedValues($request);

			$this->config->setValues($values)->save();
		}

	}