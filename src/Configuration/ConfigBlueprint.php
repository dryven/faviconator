<?php

namespace Dryven\Faviconator\Configuration;

use Dryven\Faviconator\Faviconator;
use Statamic\Facades\AssetContainer;

/**
 * Class ConfigBlueprint
 * @package Dryven\Faviconator\Configuration
 * @author dryven
 */
class ConfigBlueprint
{

	public static function getAssetsContainer()
	{
		$configHandle = Faviconator::getConfig('assets.container') ?? '';

		return AssetContainer::findByHandle($configHandle) ?? AssetContainer::all()->first();
	}

	public static function getBlueprint(): array
	{
		return [
			'sections' => [
				'general' => [
					'display' => Faviconator::getCpTranslation('tab_general'),
					'fields' => [
						[
							'handle' => 'file_png',
							'field' => [
								'type' => 'assets',
								'display' => Faviconator::getCpTranslation('file_png'),
								'instructions' => Faviconator::getCpTranslation('file_png_instructions'),
								'placeholder' => Faviconator::getCpTranslation('file_png_placeholder'),
								'container' => self::getAssetsContainer()->handle(),
								'max_files' => 1,
								'validate' => 'mimes:png'
							]
						],
						[
							'handle' => 'file_svg',
							'field' => [
								'type' => 'assets',
								'display' => Faviconator::getCpTranslation('file_svg'),
								'instructions' => Faviconator::getCpTranslation('file_svg_instructions'),
								'placeholder' => Faviconator::getCpTranslation('file_svg_placeholder'),
								'container' => self::getAssetsContainer()->handle(),
								'max_files' => 1,
								'validate' => 'mimes:svg'
							]
						],
						[
							'handle' => 'app_name',
							'field' => [
								'type' => 'text',
								'display' => Faviconator::getCpTranslation('app_name'),
								'instructions' => Faviconator::getCpTranslation('app_name_instructions'),
								'placeholder' => Faviconator::getCpTranslation('app_name_placeholder'),
							]
						],
						[
							'handle' => 'theme_color',
							'field' => [
								'type' => 'color',
								'display' => Faviconator::getCpTranslation('theme_color'),
								'instructions' => Faviconator::getCpTranslation('theme_color_instructions'),
								'placeholder' => Faviconator::getCpTranslation('theme_color_placeholder'),
								'color_modes' => [
									'hex',
								],
								'default_color_mode' => 'HEXA',
							]
						]
					]
				]
			]
		];
	}
}
