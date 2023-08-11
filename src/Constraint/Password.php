<?php


namespace App\Constraint;

use Symfony\Component\Validator\Constraint;

class Password extends Constraint
{
    public $message = "Hasło musi zawierać co najmniej 8 znaków. W tym min.: 2 małe litery, 2 duże litery, 2 cyfry i 2 znaki specjalne.";
}