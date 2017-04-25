<?php

namespace Dotink\Lab;

return [
	'setup' => function($data, $shared) {
		class CustomInspector extends \Checkpoint\Inspector
		{
			public function validate($data)
			{
				if ($data != 'data') {
					$this->log('data', 'The data was not equal to "data"');
				}
			}
		}
	},

	'tests' => [
		'Instantiation' => function($data, $shared) {
			$shared->inspector = new CustomInspector;
		},

		'Basic Validation' => function($data, $shared) {
			accept('Checkpoint\Inspector::countMessages')
				-> using($shared->inspector)
				-> equals(0)
			;

			accept('Checkpoint\Inspector::run')
				-> using($shared->inspector)
				-> with('data')
				-> equals($shared->inspector)
			;

			accept('Checkpoint\Inspector::countMessages')
				-> using($shared->inspector)
				-> equals(0)
			;

			accept('Checkpoint\Inspector::run')
				-> using($shared->inspector)
				-> with('nondata')
				-> equals($shared->inspector)
			;

			accept('Checkpoint\Inspector::countMessages')
				-> using($shared->inspector)
				-> equals(1)
			;
		},

		'Logging' => function($data, $shared) {
			accept('Checkpoint\Inspector::getMessages')
				-> using($shared->inspector)
				-> equals(['data' => ['The data was not equal to "data"']])

				-> with('data')
				-> equals(['The data was not equal to "data"'])
			;
		},


		'Clear' => function($data, $shared) {
			accept('Checkpoint\Inspector::clear')
				-> using($shared->inspector)
				-> equals($shared->inspector)
			;

			accept('Checkpoint\Inspector::countMessages')
				-> using($shared->inspector)
				-> equals(0)
			;
		},

		'Child Inspectors' => function($data, $shared) {
			class ParentInspector extends \Checkpoint\Inspector
			{
				public function __construct($child)
				{
					$this->add('child', $child);
				}

				public function validate($data)
				{
					$this->fetch('child')->run($data);
				}
			}

			$shared->parentInspector = new ParentInspector($shared->inspector);

			accept('Checkpoint\Inspector::run')
				-> using($shared->parentInspector)
				-> with('data')
				-> equals($shared->parentInspector)
			;

			accept('Checkpoint\Inspector::countMessages')
				-> using($shared->parentInspector)
				-> equals(0)
			;

			accept('Checkpoint\Inspector::run')
				-> using($shared->parentInspector)
				-> with('nondata')
				-> equals($shared->parentInspector)
			;

			accept('Checkpoint\Inspector::countMessages')
				-> using($shared->parentInspector)
				-> equals(1)
			;

			accept('Checkpoint\Inspector::getMessages')
				-> using($shared->parentInspector)
				-> equals(['child' => ['data' => ['The data was not equal to "data"']]])

				-> with('child')
				-> equals(['data' => ['The data was not equal to "data"']])

				-> with('child.data')
				-> equals(['The data was not equal to "data"'])
			;
		},

		'Checks' => function($data, $shared)
		{
			class ChecksInspector extends \Checkpoint\Inspector
			{
				public function validate($data)
				{
					foreach ($data as $check => $value) {
						$this->check($check, $value, [$check]);
					}
				}
			}

			$shared->checksInspector = new ChecksInspector();


			accept('Checkpoint\Inspector::setValidator')
				-> using($shared->checksInspector)
				-> with(new \Respect\Validation\Validator)
				-> equals($shared->checksInspector)
			;

			accept('Checkpoint\Inspector::run')
				-> using($shared->checksInspector)
				-> with($shared->goodData = [
					'notBlank'   => 'Non-blank value',
					'alpha'      => 'AlphaValue',
					'email'      => 'user@example.com',
					'lowercase'  => 'lowercase',
					'phone'      => '212-555-3822',
					'creditCard' => '4024007153361885',
				])
				-> equals($shared->checksInspector)
			;

			accept('Checkpoint\Inspector::countMessages')
				-> using($shared->checksInspector)
				-> equals(0)
			;

			accept('Checkpoint\Inspector::run')
				-> using($shared->checksInspector)
				-> with($shared->badData = [
					'notBlank'   => '',
					'alpha'      => 'Alpha Value 1',
					'email'      => 'user_example.com',
					'lowercase'  => 'LowerCase',
					'phone'      => '3822',
					'creditCard' => '402400715336',
				])
				-> equals($shared->checksInspector)
			;

			accept('Checkpoint\Inspector::countMessages')
				-> using($shared->checksInspector)
				-> equals(count($shared->badData))
			;

		}
	]
];
