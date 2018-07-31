<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractParametersValidator extends ConstraintValidator
{
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof AbstractParameters) {
            throw new UnexpectedTypeException($constraint, AbstractParameters::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $parameters = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->context
                ->buildViolation(sprintf('Invalid JSON (%d)', json_last_error()))
                ->addViolation();
        }

        /** @var ConstraintViolation[]|ConstraintViolationList $errors */
        $errors = $this->validator->validate($parameters, $this->buildConstraints());

        if ($errors->count()) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{errors}}', $this->errorsAsString($errors))
                ->addViolation();
        }
    }

    private function errorsAsString(ConstraintViolationList $constraintViolationList): string
    {
        $result = [];

        foreach ($constraintViolationList as $error) {
            $result[] = sprintf('%s: %s', $error->getPropertyPath(), $error->getMessage());
        }

        return join(" | ", $result);
    }

    abstract protected function buildConstraints();
}
