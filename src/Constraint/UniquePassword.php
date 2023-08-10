<?php

namespace App\Constraint;

use Symfony\Component\Validator\Constraint;

class UniquePassword extends Constraint
{
    public $message = "Hasło musi być różne od poprzedniego";
    public $field ;
}