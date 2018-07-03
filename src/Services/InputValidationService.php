<?php

namespace App\Services;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InputValidationService
{
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param array $data Data to validate
     * @param array $fields Fields contraints definition
     *
     * @return array
     */
    public function validateData(array $data, array $fields): array
    {
        $constraints = new Constraints\Collection([
            'allowExtraFields' => true,
            'allowMissingFields' => false,
            'fields' => $fields,
        ]);

        $validator = Validation::createValidator();
        /** @var ConstraintViolation[] $violations */
        $violations = $validator->validate($data, $constraints);
        $messages = [];

        if (count($violations)) {

            foreach ($violations as $violation) {
                $messages[$violation->getPropertyPath()] = $violation->getMessage();
            }
        }

        return $messages;
    }
}
