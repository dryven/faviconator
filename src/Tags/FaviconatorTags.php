<?php

namespace Dryven\Faviconator\Tags;

use Dryven\Faviconator\Configuration\ConfigBlueprint;
use Statamic\Tags\Tags;
use Statamic\Facades\Folder;
use Dryven\Faviconator\Faviconator;
use Dryven\Faviconator\Configuration\FaviconatorConfig;
use Illuminate\Support\Facades\File;
use Throwable;

/**
 * Class Faviconator
 * @package Dryven\Faviconator\Tags
 * @author dryven
 */
class FaviconatorTags extends Tags
{

	public static $handle = 'faviconator';

	protected $config;

	public function __construct()
	{
		$this->config = new FaviconatorConfig();
	}

	public function index()
	{
		$imagesPath = public_path(Faviconator::getConfig('assets.path') ?? 'img/favicons/');
		$images = Folder::disk()->getFiles($imagesPath)->toArray();

		if (empty($images))
			return view(Faviconator::getNamespacedKey('favicons'), collect(
				['error' => "There were no favicons found."]
			));

		foreach ($images as &$image) {
			$image['file'] = str_replace('/public', '', $image['file']);
			$image['checksum'] = $this->getFileHash(public_path($image['file']));
			preg_match("/\d+x\d+/", $image['filename'], $sizes);
			$image['dimensions'] = $sizes[0];

			$image['relation'] = (str_contains($image['filename'], 'apple-touch-icon')) ? 'apple-touch-icon' : 'icon';
		}

		return view(
			Faviconator::getNamespacedKey('favicons'),
			collect([
				'favicons' => $images,
				'file_svg_checksum' => $this->getFileHash(public_path('favicon.svg')),
				'favicon_ico_checksum' => $this->getFileHash(public_path('favicon.ico')),
			])->merge($this->config->raw())
		);
	}

	protected function getFileHash($file) {
		return File::exists($file) ? hash_file('crc32b', $file) : null;
	}
}
