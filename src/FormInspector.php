<?php

namespace Checkpoint;

/**
 * The inspector is a rule/error message organizer that wraps around Respect/Validation
 *
 * @author Matthew J. Sahagian [mjs] matthew.sahagian@gmail.com
 */
abstract class FormInspector extends Inspector
{
	/**
	 *
	 */
	protected $checks = array();


	/**
	 *
	 */
	protected $required = array();


	/**
	 *
	 */
	public function setRequiredFields(array $required)
	{
		$this->required = array_replace_recursive($this->required, $required);
	}


	/**
	 *
	 */
	protected function validate($data)
	{
		$fields = array_unique(array_diff(array_merge(
			array_keys($this->checks),
			array_keys($this->required)
		),  array_keys($this->children)));

		foreach ($fields as $field) {
			$value = $data[$field] ?? NULL;

			if (isset($this->checks[$field])) {
				$checks = $this->checks[$field];
			} else {
				$checks = array();
			}

			if (empty($this->required[$field])) {
				$this->check($field, $value, $checks, TRUE);
			} else {
				$this->check($field, $value, $checks);
			}
		}

		foreach ($this->children as $field => $child) {
			if (isset($this->required[$field])) {
				$child->setRequiredFields($this->required[$field]);
			}

			$child->run($data[$field] ?? array());
		}
	}
}
