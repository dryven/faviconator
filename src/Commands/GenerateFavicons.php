<?php

namespace Dryven\Faviconator\Commands;

use Dryven\Faviconator\Configuration\ConfigBlueprint;
use Statamic\Facades\File;
use Illuminate\Console\Command;
use Dryven\Faviconator\Faviconator;
use Dryven\Faviconator\Configuration\FaviconatorConfig;
use Illuminate\Support\Facades\Log;

/**
 * Class GenerateFavicons
 * @package Dryven\Commands
 * @author dryven
 * @author chrisbliss18
 */
class GenerateFavicons extends Command
{

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'favicon:generate';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generates favicon images';

	protected $config;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle(): int
	{
		// Check that the gd library extension is loaded correctly
		if (extension_loaded('gd') === false) {
			$this->line('The gd extendion could not be found. Please install and/or enable it in the php.ini file.', 'fg=red');
			return self::FAILURE;
		}

		// Set the config variable accordingly
		$this->config = new FaviconatorConfig();

		// Get the image file from the settings
		$assetContainer = ConfigBlueprint::getAssetsContainer();
		$sourceFile = $this->config->assetPath('file_png')->path();
		$image = imagecreatefromstring(File::disk($assetContainer->handle())->get($sourceFile));
		$svgFile = $this->config->assetPath('file_svg');
		$svgFile = isset($svgFile) ? File::disk($assetContainer->handle())->get($svgFile->path()) : null;

		if ($image === false) {
			$this->line('The given file was not an image.', 'fg=red');
			return self::FAILURE;
		}

		if ($this->cropImageIfNecessary($image) == self::FAILURE) {
			$this->line('Image could not be cropped.', 'fg=red');
			return self::FAILURE;
		}

		if ($this->resizeImageIfNecessary($image) == self::FAILURE) {
			$this->line('Image could not be resized.', 'fg=red');
			return self::FAILURE;
		}

		if ($this->generateIcoFile($image) == self::FAILURE) {
			$this->line('ICO image file could not be created.', 'fg=red');
		}

		if ($this->generateFaviconFiles($image) == self::FAILURE) {
			$this->line('The favicon pngs could not be created.', 'fg=red');
		}

		if ($svgFile !== null && $this->copySvgFavicon($svgFile) === self::FAILURE) {
			$this->line('The svg favicon could not be copied', 'fg=red');
		}

		return self::SUCCESS;
	}

	/**
	 * Crops the image if it has not the ratio required for favicons.
	 *
	 * @param $image
	 * @return bool
	 */
	private function cropImageIfNecessary(&$image): bool
	{
		// Crop the image only if the image has not a ratio of 1 : 1
		if (imagesx($image) / (float)imagesy($image) !== 1.0) {
			$this->line(
				'The given image was not square, so it was cropped. Please check the output.',
				'fg=yellow'
			);

			$image = imagecrop(
				$image,
				[
					'x' => round(imagesx($image) / 2 - imagesy($image) / 2),
					'y' => 0,
					'width' => imagesy($image),
					'height' => imagesy($image),
				]
			);

			if (!$image) return self::FAILURE;
		}

		return self::SUCCESS;
	}

	/**
	 * Resizes image if it is under the size needed for favicon generation.
	 *
	 * @param $image
	 * @return bool
	 */
	private function resizeImageIfNecessary(&$image): bool
	{
		$minSize = $this->getRecommendedImageSize();
		$imageWidth = imagesx($image);
		$imageHeight = imagesy($image);

		// Resize the image only if the image has not the qualified size
		if (imagesx($image) < $minSize || imagesy($image) < $minSize) {
			$this->line("<fg=yellow>The given image has a size of</fg=yellow> <fg=blue>${imageWidth}x$imageHeight" .
				"<fg=yellow>, so it was resized to</fg=yellow> <fg=blue>${minSize}x$minSize</fg=blue>.");

			$image = imagescale($image, $minSize, $minSize, IMG_NEAREST_NEIGHBOUR);

			if (!$image) return self::FAILURE;
		}

		return self::SUCCESS;
	}

	/**
	 * Generates and saves the ico file in the public path of the application
	 *
	 * @param $image
	 * @return bool
	 */
	private function generateIcoFile($image): bool
	{
		/**
		 * ICONDIR structure
		 *
		 * Offset | Size   | Purpose
		 * ---------------------------------------------------------------------
		 * 0x0    | 2B (v) | Reserved, must always be 0.
		 * 0x2    | 2B (v) | 1 for icon (.ico), 2 for cursor (.cur)
		 * 0x4    | 2B (v) | Number of images in the file
		 *
		 * ICONDIRENTRY structure
		 *
		 * Offset | Size   | Purpose
		 * ---------------------------------------------------------------------
		 * 0x0    | 1B (C) | Image width in pixels. 0 < x < 256
		 * 0x1    | 1B (C) | Image height in pixels. 0 < x < 256
		 * 0x2    | 1B (C) | Number of colors in the palette. 0 for no palette.
		 * 0x3    | 1B (C) | Reserved, should be 0.
		 * 0x4    | 2B (v) | Color planes, should be 0 or 1.
		 * 0x6    | 2B (v) | Bits per pixel.
		 * 0x8    | 4B (V) | Size of the images data in bytes.
		 * 0x12   | 4B (V) | Offset of BMP/PNG data from offset ICONDIR.
		 */
		$ICON_DIR_SIZE = 6;
		$ICON_DIR_ENTRY_SIZE = 16;

		$icoSizes = Faviconator::getConfig('ico_sizes');

		$imageCount = count($icoSizes);

		// Create a variable for the ico file data and write the ico header
		$icoData = pack('v3', 0, 1, $imageCount);
		$imageData = '';

		$offset = $ICON_DIR_SIZE + ($ICON_DIR_ENTRY_SIZE * $imageCount);

		foreach ($icoSizes as $size) {
			// Create a new image to resample the image in the specified size
			$newImage = $this->createResizedImage($image, $size);

			// Collection png data
			ob_start();
			if (imagepng($newImage, null, 9, PNG_ALL_FILTERS) === false) {
				$this->line('The image could not be saved as png.', 'fg=red');
				return self::FAILURE;
			}
			$pngImage = ob_get_clean();

			// Creating the image structure and saving the pixel data for later
			$icoData .= pack('C4v2V2', $size, $size, 0, 0, 1, 32, strlen($pngImage), $offset);
			$imageData .= $pngImage;

			$offset += strlen($pngImage);
		}

		$icoData .= $imageData;
		unset($imageData);

		File::disk()->put(public_path('favicon.ico'), $icoData);

		$dimensions = collect($icoSizes)->map(function ($size) {
			return $size . 'x' . $size;
		})->toArray();

		$dimensionsString = '<fg=blue>' . implode("<fg=green>, </fg=green>", $dimensions) . '</fg=blue>';
		$filesizeString = '<fg=blue>' . strlen($icoData) . '</fg=blue>';

		$this->line("<fg=green>An ICO with the dimensions $dimensionsString and a size of $filesizeString bytes was created.</fg=green>");

		return self::SUCCESS;
	}

	private function copySvgFavicon($svgFile): bool
	{
		File::disk()->put(public_path('favicon.svg'), $svgFile);

		return self::SUCCESS;
	}

	/**
	 * @param $image
	 * @return bool
	 */
	private function generateFaviconFiles($image): bool
	{
		// Create directories to save the favicon in, if they don't exist
		File::disk()->makeDirectory(public_path(Faviconator::getConfig('assets.path') ?? 'img/favicons/'));

		$genericSizes = Faviconator::getConfig('favicon_sizes');
		$appleTouchIconSizes = Faviconator::getConfig('apple_touch_icon_sizes');

		$this->generateFaviconsByType($image, $genericSizes, 'favicon');
		$this->generateFaviconsByType($image, $appleTouchIconSizes, 'apple-touch-icon');

		$faviconSizes = array_merge($genericSizes, $appleTouchIconSizes);

		$dimensions = collect($faviconSizes)->map(function ($size) {
			return $size . 'x' . $size;
		})->toArray();
		$dimensionsString = '<fg=blue>' . implode("<fg=green>, </fg=green>", $dimensions) . '</fg=blue>';

		$this->line("<fg=green>PNG favions with the dimensions $dimensionsString were created.</fg=green>");

		return self::SUCCESS;
	}

	private function generateFaviconsByType($image, $sizes, $name): bool
	{
		foreach ($sizes as $size) {
			// Create a new image to resample the image in the specified size
			$newImage = $this->createResizedImage($image, $size);

			ob_start();
			if (imagepng($newImage, null, 9, PNG_ALL_FILTERS) === false) {
				$this->line('The image could not be saved as png.', 'fg=red');
				return self::FAILURE;
			}
			$pngImage = ob_get_clean();

			$filepath = public_path((Faviconator::getConfig('assets.path') ?? 'img/favicons/') . $name . "-${size}x$size.png");

			File::disk()->put($filepath, $pngImage);
		}

		return self::SUCCESS;
	}

	/**
	 * Returns resized alpha-enabled image
	 *
	 * @param $image
	 * @param $size
	 * @return false|\GdImage|resource
	 */
	private function createResizedImage($image, $size)
	{
		$newImage = imagecreatetruecolor($size, $size);

		imagecolortransparent($newImage, imagecolorallocatealpha($newImage, 0, 0, 0, 127));
		imagealphablending($newImage, false);
		imagesavealpha($newImage, true);

		if (
			imagecopyresampled($newImage, $image, 0, 0, 0, 0, $size, $size, imagesx($image), imagesy($image)) ===
			false
		) {
			$this->line('The image data could not be resampled.');
			return self::FAILURE;
		}

		return $newImage;
	}

	/**
	 * Returns the size in which a favicon should be optimally uploaded.
	 *
	 * @return mixed
	 */
	private function getRecommendedImageSize()
	{
		return max(array_keys(Faviconator::getConfig('favicon_sizes')));
	}
}
