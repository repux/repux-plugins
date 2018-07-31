<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class AbstractParameters extends Constraint
{
    public $message = 'Parameters are invalid ({{errors}})';
}
