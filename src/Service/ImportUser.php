<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ImportUser
{
    /** @var UserPasswordHasherInterface */
    private $passwordEncoder;

    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordHasherInterface $passwordEncoder
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordEncoder
    ) {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
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

    public function processFile(string $csvData)
    {
        $lines = explode("\r", $csvData);

        foreach ($lines as $key => $line) {
            if ($key == 0) {
                continue;
            }

            $lineArray = str_getcsv($line);
            if (!isset($lineArray[1])) {
                break;
            }

            $this->saveUserToDB([
                'email' => $lineArray[0],
                'password' => $lineArray[1]
            ]);
        }
    }
}