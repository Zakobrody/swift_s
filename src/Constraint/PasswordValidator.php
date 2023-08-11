<?php


namespace App\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PasswordValidator extends ConstraintValidator
{

    public $pattern = '/^(?=.*[A-Z]){2,}(?=.*[a-z]){2,}(?=.*[0-9]){2,}(?=.*[!@#$%^&*]){2,}[a-zA-Z0-9!@#$%^&*]{8,}$/';

    /* Checks if the passed value is valid.
    *
    * @param mixed      $value      The value that should be validated
    * @param Constraint $constraint The constraint for the validation
    */
    public function validate($value, Constraint $constraint)
    {
        if (!preg_match($this->pattern, $value, $matches)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}