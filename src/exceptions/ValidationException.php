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
	 * @access protected
	 * @var Inspector|null
	 */
	protected $inspector = NULL;


	/**
	 * @access public
	 * @param Inspector $inspector
	 * @return self
	 */
	public function setInspector(Inspector $inspector): self
	{
		$this->inspector = $inspector;

		return $this;
	}


	/**
	 * @access public
	 * @param string $path
	 * @return array<string, mixed>|array<string> The list of validation messages based on violated rules
	 */
	public function getMessages(string $path = NULL): array
	{
		return $this->inspector->getMessages($path);
	}
}
