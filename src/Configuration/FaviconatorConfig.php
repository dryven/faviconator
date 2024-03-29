<?php

namespace Dryven\Faviconator\Configuration;

use Dryven\Faviconator\Faviconator;
use Statamic\Support\Arr;
use Statamic\Facades\File;
use Statamic\Facades\YAML;
use Statamic\Facades\Asset;
use Statamic\Fields\Fields;
use Illuminate\Http\Request;
use Statamic\Fields\Blueprint;
use Illuminate\Support\Facades\Artisan;
use function base_path;
use function sprintf;

/**
 * Class FaviconatorConfig
 * @package Dryven\Faviconator\Configuration
 * @author dryven
 */
class FaviconatorConfig
{
	protected $blueprint;
	protected $configPath;
	protected $configData;
	protected string $handle;

	public function __construct(string $handle)
	{
		$this->blueprint = \Statamic\Facades\Blueprint::make()->setContents(ConfigBlueprint::getBlueprint());
		$this->configPath = $this->getConfigurationPath($handle);
		$this->handle = $handle;
		$this->configData = YAML::parse(File::disk()->get($this->configPath));
	}

	public static function create(string $handle): self
	{
		return new self($handle);
	}

	/**
	 * Returns the path to the configuration file.
	 *
	 * @return string
	 */
	public function path(): string
	{
		return $this->configPath;
	}

	/**
	 * Returns the blueprint.
	 *
	 * @return Blueprint
	 */
	public function blueprint(): Blueprint
	{
		return $this->blueprint;
	}

	/**
	 * @param $handle
	 *
	 * @return \Statamic\Assets\Asset|null
	 */
	public function assetPath($handle)
	{
		if (empty($this->values()[$handle])) return null;

		return Asset::find($this->values()[$handle][0]);
	}

	/**
	 * Returns the values augmented by the blueprint.
	 */
	public function values(): array
	{
		return $this->fields()->values()->all();
	}

	/**
	 * Returns the current blueprint fields.
	 *
	 * @return Fields
	 */
	public function fields(): Fields
	{
		return $this->blueprint->fields()->addValues($this->raw())->preProcess();
	}

	/**
	 * Returns the raw array data.
	 *
	 * @return array
	 */
	public function raw(): array
	{
		return $this->configData;
	}

	/**
	 * Validates and returns the values without fields equal to null.
	 *
	 * @param Request $request
	 *
	 * @return array
	 */
	public function validatedValues(Request $request): array
	{
		$fields = $this->blueprint->fields()->addValues($request->all());

		$fields->validate();

		return Arr::removeNullValues($fields->process()->values()->all());
	}

	/**
	 * Sets the configuration data / values array.
	 *
	 * @param array $values
	 *
	 * @return $this
	 */
	public function setValues(array $values): FaviconatorConfig
	{
		$this->configData = $values;

		return $this;
	}

	/**
	 * Saves the configuration to disk.
	 */
	public function save()
	{
		File::disk()->put($this->configPath, YAML::dump($this->configData));

		Artisan::call(sprintf('favicon:generate --site=%s', $this->handle));
	}

	private function getConfigurationPath(string $handle): string
	{
		$suffix = '';

		if (Faviconator::getConfig('multi_site')) {
			$suffix = sprintf('_%s', $handle);
		}

		return base_path(
			sprintf('content/faviconator%s.yaml', $suffix)
		);
	}
}
