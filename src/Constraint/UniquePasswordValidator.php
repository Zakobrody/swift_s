<?php


namespace App\Constraint;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniquePasswordValidator extends ConstraintValidator
{
    private $userPasswordHasher;

    private $security;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher, Security $security)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->security = $security;
    }

    /* Checks if the passed value is exists in given entity.
    *
    * @param mixed      $entity      The value that should be validated
    * @param Constraint $constraint The constraint for the validation
    */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniquePassword) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\CrossEntityUnique');
        }

        if (!is_array($constraint->field) && !is_string($constraint->field)) {
            throw new UnexpectedTypeException($constraint->field, 'array');
        }

        if (null === $value) {
            return;
        }

        /** @var User $user */
        $user = $this->security->getUser();

        foreach ($user->getUserPasswords() as $userPassword) {
            $user->setPassword($userPassword->getPassword());

            if ($this->userPasswordHasher->isPasswordValid($user, $value)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ string }}', $value)
                    ->addViolation();

                return;
            }
        }
    }
}