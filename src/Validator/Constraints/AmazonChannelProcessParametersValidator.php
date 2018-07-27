<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;

class AmazonChannelProcessParametersValidator extends AbstractParametersValidator
{
    protected function buildConstraints(): Constraint
    {
        $constraints = new Constraints\Collection([
            'allowExtraFields' => false,
            'allowMissingFields' => true,
            'fields' => [
                'created_at_from' => [new Constraints\DateTime()],
                'created_at_to' => [new Constraints\DateTime()],
            ]
        ]);

        return $constraints;
    }
}
