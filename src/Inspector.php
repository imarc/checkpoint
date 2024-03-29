<?php

namespace Checkpoint;

use RuntimeException;
use Respect\Validation\Validator;

/**
 * The inspector is a rule/error message organizer that wraps around Respect/Validation
 *
 * @author Matthew J. Sahagian [mjs] matthew.sahagian@gmail.com
 */
abstract class Inspector implements Validation
{
	/**
	 * Default errors corresponding to argumentless validation rules
	 *
	 * @static
	 * @access protected
	 * @var array<string, string>
	 */
	static protected $defaultErrors = [
		'alpha'       => 'This field should contain only letters',
		'alnum'       => 'This field should only contain letters, numbers, and spaces',
		'boolVal'     => 'This field should only contain true/false values',
		'numericVal'  => 'This field should only contain numeric values',
		'date'        => 'This field should contain a valid date',
		'email'       => 'This field should contain a valid e-mail address',
		'phone'       => 'This field should contain a valid phone number e.g. 212-555-1234',
		'lowercase'   => 'This field should not contain capital letters',
		'notBlank'    => 'This field cannot be left blank',
		'notOptional' => 'This field is required',
		'trueVal'     => 'This field must contain a true value',
		'countryCode' => 'This field must be a valid ISO country code',
		'creditCard'  => 'This field must be a valid credit card number',
		'url'         => 'This field should contain a valid URL, including http:// or https://',
	];


	/**
	 * List of child inspectors
	 *
	 * @access private
	 * @var array<string, self>
	 */
	protected $children = array();


	/**
	 * List of error messages keyed by rule name
	 *
	 * These will reflect `$defaultErrors` when the inspector is cleared and will have new ones
	 * added as they are defined.
	 *
	 * @access protected
	 * @var array<string, string>
	 */
	protected $errors = array();


	/**
	 * Custom rules keyed by the rule name
	 *
	 * @access protected
	 * @var array<string, Validator>
	 */
	protected $rules = array();


	/**
	 * The internal validator
	 *
	 * @access protected
	 * @var Validator|null
	 */
	protected $validator = NULL;


	/**
	 * List of logged messages
	 *
	 * @access private
	 * @var array<string, array<string>>
	 */
	private $messages = array();


	/**
	 * Add a child inspector
	 *
	 * @access public
	 * @param string $reference The reference used to find or recall the child
	 * @param Inspector $child The child instance
	 * @return Inspector The object instance for method chaining
	 */
	public function add(string $reference, Inspector $child)
	{
		$this->children[$reference] = $child;

		return $this;
	}


	/**
	 * Check data against a particular set of rules
	 *
	 * @access public
	 * @param string $key The key to use when logging error messages
	 * @param mixed $data The data to validate against the rules
	 * @param array<string> $rules The array of rules to check the data against
	 * @param bool $is_optional Allow all checks to fail if no data is present
	 * @return bool Whether or not the check passed or failed
	 */
	public function check(string $key, $data, array $rules, bool $is_optional = FALSE): bool
	{
		$pass = TRUE;

		if (!$is_optional) {
			$rules = array_unique(array_merge(['notOptional'], $rules));
		}

		if (!in_array('notOptional', $rules) && !$data) {
			return $pass;
		}

		foreach ($rules as $rule) {
			if (!isset($this->errors[$rule])) {
				throw new RuntimeException(sprintf(
					'Unsupported validation rule "%s", try using define()',
					$rule
				));
			}

			if (isset($this->rules[$rule])) {
				$check = $this->rules[$rule];
			} else {
				$check = $this->validator->create()->$rule();
			}

			if (!$check->validate($data)) {
				$pass = FALSE;

				$this->log($key, $this->errors[$rule]);

				if ($rule == 'notOptional') {
					break;
				}
			}
		}

		return $pass;
	}


	/**
	 * Count the number of validation messages (including registered children)
	 *
	 * @access public
	 * @return integer The number of error messages across this inspector and all its children
	 */
	public function countMessages()
	{
		$count = 0;

		foreach (array_keys($this->messages) as $key) {
			$count += count($this->messages[$key]);
		}

		foreach ($this->children as $reference => $inspector) {
			$count += $inspector->countMessages();
		}

		return $count;
	}


	/**
	 * Define a new rule and its related error messaging
	 *
	 * @access public
	 * @param string $rule The name of the rule to define
	 * @param string $error The error message to log if the rule is violated
	 * @return Validator A new respect validator instance for chaining rules
	 */
	public function define($rule, $error)
	{
		$this->rules[$rule]  = $this->validator->create();
		$this->errors[$rule] = $error;

		return $this->rules[$rule];
	}


	/**
	 * Get all the messages under a particular path.
	 *
	 * The path is determined by a combination of child validator keys and the final error message key, such that if
	 * a child validator was added with `person` and contained a validation messages logged to `firstName` then the
	 * path `person.firstName` would acquire those validation messages.
	 *
	 * @access public
	 * @param string $path The path to the validation messages
	 * @return array<string, mixed>|array<string> The list of validation messages based on violated rules
	 */
	public function getMessages($path = NULL): array
	{
		if ($path) {
			if (isset($this->messages[$path])) {
				return $this->messages[$path];
			}

			if (strpos($path, '.') === FALSE) {
				return isset($this->children[$path])
					? $this->children[$path]->getMessages()
					: array();
			}

			$parts = explode('.', $path);
			$head  = array_shift($parts);

			return isset($this->children[$head])
				? $this->children[$head]->getMessages(implode('.', $parts))
				: array();
		}

		$messages = $this->messages;

		foreach ($this->children as $reference => $inspector) {
			$messages[$reference] = $inspector->getMessages();

			if (empty($messages[$reference])) {
				unset($messages[$reference]);
			}
		}

		return $messages;
	}


	/**
	 * The entry point for running validation
	 *
	 * Instead of running the validate method directly, run should be used to ensure initial messages from any previous
	 * validation are cleared and the inspector is reset.
	 *
	 * @access public
	 * @param mixed $data The data to validate
	 * @param bool $exception_on_messages Throw an exception if there are error messages
	 * @return self The object instance for method chaining
	 */
	public function run($data, $exception_on_messages = FALSE): self
	{
		$this->clear();
		$this->setup($data);
		$this->validate($data);

		if ($exception_on_messages && $this->countMessages()) {
			$exception = new ValidationException('Please correct the errors shown below.');

			$exception->setInspector($this);

			throw $exception;
		}

		return $this;
	}


	/**
	 * Set up validation checks and default error messages for the data
	 * @access public
	 * @param mixed $data The data to validate
	 * @return void
	 */
	protected function setup($data)
	{
	}


	/**
	 * Set the internal validator (an instance of Respect\Validation)
	 *
	 * @access public
	 * @param Validator $validator The internal validator instance
	 * @return Validation The object instance for method chaining
	 */
	public function setValidator(Validator $validator): Validation
	{
		$this->validator = $validator;

		return $this;
	}


	/**
	 * Clear the messages, rules, and errors for this inspector (reset it back to defaults)
	 *
	 * @access protected
	 * @return self The object instance for method chaining
	 */
	protected function clear(): self
	{
		$this->messages = array();
		$this->rules    = array();
		$this->errors   = static::$defaultErrors;

		return $this;
	}


	/**
	 * Fetch a child inspector instance which was previously registered via `add()`
	 *
	 * This method is generally used inside the custom `validate()` method of the parent inspector to fetch a child
	 * and pass a subset of its data to the child for validation.
	 *
	 * @access protected
	 * @param string $reference The reference under which the child inspector was added
	 * @return self The child inspector instance
	 */
	protected function fetch($reference): self
	{
		if (!isset($this->children[$reference])) {
			throw new RuntimeException(sprintf(
				'Reference "%s" is not valid / has not been added.',
				$reference
			));
		}

		return $this->children[$reference];
	}


	/**
	 * Log a message on this inspector
	 *
	 * @access protected
	 * @param string $key The key under which to log the message.
	 * @param string $message The message to log
	 * @return self The object instance for method chaining
	 */
	protected function log($key, $message): self
	{
		$this->messages[$key][] = $message;

		return $this;
	}


	/**
	 * Validate some data
	 *
	 * This method is intended to be overloaded with custom/explicit validation.
	 *
	 * @access protected
	 * @param mixed $data The data to validate
	 * @return void
	 */
	protected function validate($data)
	{
		return;
	}
}
