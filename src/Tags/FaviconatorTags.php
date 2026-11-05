<?php

namespace Dryven\Faviconator\Tags;

use Statamic\Tags\Tags;
use function public_path;
use Statamic\Facades\Site;
use Statamic\Facades\Folder;
use Dryven\Faviconator\Faviconator;
use Illuminate\Support\Facades\File;
use Dryven\Faviconator\Configuration\FaviconatorConfig;

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
		$images = Folder::disk()
			->getFiles($imagesPath)
			->toArray();

		if (empty($images)) {
			return view(Faviconator::getNamespacedKey('favicons'), collect(
				['error' => "There were no favicons found."]
			));
		}

		$favicons = collect($images)
			->map(function ($image) {
				$image['file'] = str_replace('/public', '', $image['file']);
				$image['checksum'] = $this->getFileHash(public_path($image['file']));
				preg_match("/\d+x\d+/", $image['filename'], $sizes);

				if (count($sizes) > 0) {
					$image['dimensions'] = $sizes[0];
					$image['size'] = (int) explode('x', $sizes[0])[0];
				}

				// Statamic's Folder API stores the extension separately from filename.
				$extension = strtolower($image['extension'] ?? pathinfo($image['basename'] ?? $image['filename'], PATHINFO_EXTENSION));
				$image['extension'] = $extension;
				$image['type'] = $this->mimeTypeForExtension($extension);
				$image['relation'] = str_contains($image['filename'], 'apple-touch-icon')
					? 'apple-touch-icon'
					: 'icon';

				return $image;
			})
			// Root fallbacks cover ico/svg; keep the generated size variants as PNG/apple icons.
			->filter(fn ($image) => ($image['extension'] ?? null) === 'png');

		$pngIcons = $favicons
			->filter(fn ($image) => $image['relation'] === 'icon')
			->sort(function ($a, $b) {
				return $this->iconSortScore($b) <=> $this->iconSortScore($a);
			})
			->values();

		$appleTouchIcons = $favicons
			->filter(fn ($image) => $image['relation'] === 'apple-touch-icon')
			->sortByDesc('size')
			->values();

		$ordered = $pngIcons
			->concat($this->rootFallbackIcons())
			->concat($appleTouchIcons)
			->values()
			->all();

		$config = FaviconatorConfig::create(Site::current()->handle);

		return view(
			Faviconator::getNamespacedKey('favicons'),
			collect([
				'favicons' => $ordered,
				'theme_color' => 0,
			])->merge($config->raw())
		);
	}

	/**
	 * Root /favicon.ico and /favicon.svg are widely requested by browsers and crawlers.
	 */
	protected function rootFallbackIcons(): array
	{
		$icons = [];

		$candidates = [
			[
				'path' => 'favicon.svg',
				'type' => 'image/svg+xml',
				'relation' => 'icon',
			],
			[
				'path' => 'favicon.ico',
				'type' => 'image/x-icon',
				'relation' => 'icon',
			],
		];

		foreach ($candidates as $candidate) {
			$rootPath = public_path($candidate['path']);
			$directoryPath = $this->faviconPath($candidate['path']);

			if (File::exists($rootPath) && filesize($rootPath) > 0) {
				$file = '/' . ltrim($candidate['path'], '/');
				$checksumFile = $rootPath;
			} elseif (File::exists($directoryPath) && filesize($directoryPath) > 0) {
				$basePath = Faviconator::getConfig('assets.path') ?? 'img/favicons/';

				if (Faviconator::getConfig('multi_site')) {
					$basePath .= Site::current()->handle() . '/';
				}

				$file = '/' . trim($basePath, '/') . '/' . $candidate['path'];
				$checksumFile = $directoryPath;
			} else {
				continue;
			}

			$icons[] = [
				'file' => $file,
				'checksum' => $this->getFileHash($checksumFile),
				'type' => $candidate['type'],
				'relation' => $candidate['relation'],
			];
		}

		return $icons;
	}

	/**
	 * Prefer sizes Google recommends (>48px), especially 96px, then larger icons.
	 */
	protected function iconSortScore(array $image): int
	{
		$size = $image['size'] ?? 0;

		if ($size === 96) {
			return 10000 + $size;
		}

		if ($size >= 48) {
			return 5000 + $size;
		}

		return $size;
	}

	protected function mimeTypeForExtension(string $extension): ?string
	{
		return match ($extension) {
			'png' => 'image/png',
			'svg' => 'image/svg+xml',
			'ico' => 'image/x-icon',
			default => null,
		};
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
