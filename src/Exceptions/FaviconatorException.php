<?php

namespace Dryven\Faviconator\Exceptions;

use Exception;

/**
 * Class FaviconatorException
 * @package Dryven\Faviconator\Exception
 * @author dryven
 */
class FaviconatorException extends Exception
{

	public function __toString()
	{
		parent::__toString();
	}
}
