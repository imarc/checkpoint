<?php

namespace Checkpoint;

use Respect\Validation\Validator;

/**
 *
 */
interface Validation
{
	public function setValidator(Validator $validator);
}
