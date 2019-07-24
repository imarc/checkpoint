<?php

namespace Checkpoint;

use Exception;

/**
 * And exception to be thrown when any validation errors are found.
 *
 * @author Matthew J. Sahagian [mjs] matthew.sahagian@gmail.com
 * @copyright Imarc LLC 2016
 */
class ValidationException extends Exception
{
	/**
	 *
	 */
	protected $inspector = NULL;


	/**
	 *
	 */
	public function setInspector(Inspector $inspector)
	{
		$this->inspector = $inspector;

		return $this;
	}


	/**
	 *
	 */
	public function getMessages($path = NULL)
	{
		return $this->inspector->getMessages($path);
	}
}
