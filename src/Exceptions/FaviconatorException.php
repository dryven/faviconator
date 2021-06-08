<?php

	namespace DDM\Faviconator\Exceptions;

	use Exception;

	/**
	 * Class FaviconatorException
	 * @package DDM\Faviconator\Exception
	 * @author  DDM Studio
	 */
	class FaviconatorException extends Exception {

		public function __toString() {
			parent::__toString();
		}

	}