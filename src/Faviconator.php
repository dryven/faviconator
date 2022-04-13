<?php

	namespace Dryven\Faviconator;

	use Statamic\Facades\YAML;

	/**
	 * Global definitions and routines.
	 *
	 * Class Faviconator
	 * @package Dryven\Faviconator
	 * @author dryven
	 */
	class Faviconator {

		public const NAMESPACE = "faviconator";

		public const PATH_STYLESHEET = "vendor/" . self::NAMESPACE . "/css/";
		public const PATH_JAVASCRIPT = "vendor/" . self::NAMESPACE . "/js/";

		public const NAVIGATION_ITEM_KEY = self::NAMESPACE . "_settings";

		public const ROUTE_SETTINGS_INDEX = self::NAMESPACE . ".settings.index";
		public const ROUTE_SETTINGS_UPDATE = self::NAMESPACE . ".settings.update";

		public const PERMISSION_SETTINGS_KEY = self::NAMESPACE . "_settings";
		public const PERMISSION_GENERAL_KEY = self::NAMESPACE . "_general";

		public const VENDOR_DEFAULT_SETTINGS_KEY = self::NAMESPACE . '-settings';
		public const VENDOR_VIEWS_KEY = self::NAMESPACE . '-views';
		public const VENDOR_LANGUAGES_KEY = self::NAMESPACE . '-lang';
		public const VENDOR_CONFIG_KEY = self::NAMESPACE . '-config';

		/**
		 * Returns a namespaced key, e.g. for views, etc.
		 *
		 * @param $key
		 *
		 * @return string
		 */
		public static function getNamespacedKey($key): string {
			return Faviconator::NAMESPACE . '::' . $key;
		}

		/**
		 * Returns the control panel translation with the addon's namespace.
		 *
		 * @param $translationKey
		 *
		 * @return string
		 */
		public static function getCpTranslation($translationKey): string {
			return __(Faviconator::NAMESPACE . '::cp.' . $translationKey);
		}

		public static function getConfig($key) {
			return config("statamic.faviconator.$key");
		}

	}