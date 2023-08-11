<?php

namespace App\Service;

use App\Constraint\Password;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ImportUser
{
    /** @var UserPasswordHasherInterface */
    private $passwordEncoder;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ValidatorInterface  */
    private $validator;

    /**
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordHasherInterface $passwordEncoder
     * @param ValidatorInterface $validator
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordEncoder,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->validator = $validator;
    }
    public function saveUserToDB(array $userData)
    {
        $user = new User();
        $user->setEmail($userData['email']);
        $user->setPassword($this->passwordEncoder->hashPassword($user, $userData['password']));
        $user->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function processFile(string $csvData): array
    {
        $lines = explode("\r\n", $csvData);
        $responseData = [
            'total' => 0,
            'done' => 0,
            'error' => 0,
        ];

        foreach ($lines as $key => $line) {
            if ($key == 0) {
                continue;
            }

            $lineArray = str_getcsv($line);
            if (empty($lineArray[0]) && empty($lineArray[1])) {
                break;
            }

            if (empty($lineArray[0]) || empty($lineArray[1])) {
                $responseData['total']++;
                $responseData['error']++;
                continue;
            }

            $email = $lineArray[0];
            $password = $lineArray[1];

            try{
                $errorList = $this->validator->validate(
                    $email,
                    new Assert\Email()
                );
                if ($errorList->count()) {
                    $responseData['total']++;
                    $responseData['error']++;
                    continue;
                }

                $errorList = $this->validator->validate(
                    $password,
                    new Password()
                );
                if ($errorList->count()) {
                    $responseData['total']++;
                    $responseData['error']++;
                    continue;
                }

                $this->saveUserToDB([
                    'email' => $email,
                    'password' => $password
                ]);
            } catch (Exception $e) {
                $responseData['total']++;
                $responseData['error']++;
                continue;
            }

            $responseData['total']++;
            $responseData['done']++;
        }

        return $responseData;
    }
}