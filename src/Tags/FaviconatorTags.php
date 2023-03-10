<?php

namespace Dryven\Faviconator\Tags;

use Dryven\Faviconator\Configuration\ConfigBlueprint;
use Statamic\Facades\Site;
use Statamic\Tags\Tags;
use Statamic\Facades\Folder;
use Dryven\Faviconator\Faviconator;
use Dryven\Faviconator\Configuration\FaviconatorConfig;
use Illuminate\Support\Facades\File;
use Throwable;
use function public_path;

/**
 * Class Faviconator
 * @package Dryven\Faviconator\Tags
 * @author dryven
 */
class FaviconatorTags extends Tags
{
	public static $handle = 'faviconator';

	public function index()
	{
		$imagesPath = $this->faviconPath();
		$images = Folder::disk()->getFiles($imagesPath)->toArray();

		if (empty($images))
			return view(Faviconator::getNamespacedKey('favicons'), collect(
				['error' => "There were no favicons found."]
			));

		foreach ($images as &$image) {

			if ($image['extension'] !== 'png') {
				continue;
			}

			$image['file'] = str_replace('/public', '', $image['file']);
			$image['checksum'] = $this->getFileHash(public_path($image['file']));
			preg_match("/\d+x\d+/", $image['filename'], $sizes);
			$image['dimensions'] = $sizes[0];

			$image['relation'] = (str_contains($image['filename'], 'apple-touch-icon')) ? 'apple-touch-icon' : 'icon';
		}

		$config = FaviconatorConfig::create(Site::current()->handle);

		return view(
			Faviconator::getNamespacedKey('favicons'),
			collect([
				'favicons' => $images,
				'file_svg_checksum' => $this->getFileHash(public_path('favicon.svg')),
				'favicon_ico_checksum' => $this->getFileHash(public_path('favicon.ico')),
				'theme_color' => 0,
			])->merge($config->raw())
		);
	}

	protected function getFileHash($file)
	{
		return File::exists($file) ? hash_file('crc32b', $file) : null;
	}

	private function faviconPath(string $path = ''): string
	{
		$basePath = Faviconator::getConfig('assets.path') ?? 'img/favicons/';

		if (Faviconator::getConfig('multi_site')) {
			$basePath .= Site::current()->handle() . '/';
		}

		return public_path($basePath . $path);
	}
}
