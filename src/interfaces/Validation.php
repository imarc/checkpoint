<?php

namespace Checkpoint;

use Respect\Validation\Validator;

/**
 * A simple validation interface to typehint dependency injectors that can do setter injection
 *
 * @author Matthew J. Sahagian [mjs] matthew.sahagian@gmail.com
 * @copyright Imarc LLC 2016
 */
interface Validation
{
	public function setValidator(Validator $validator): self;
}
