<?php

namespace App\Service;

use App\Entity\User;
use App\Enum\AlimentaryRestriction;
use App\Enum\UserRank;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private GamificationManager $gm,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function create(array &$data): User|false
    {
        $user = new User();
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setAlimentaryRestrictions($data['alimentaryRestrictions'] ?? []);
        $user->setRoles(['ROLE_USER']);
        $user->setVerification(null);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function update(array &$data, User &$user): User|false
    {
        if (isset($data['alimentaryRestrictions'])) {
            foreach ($data['alimentaryRestrictions'] as &$restriction) {
                $restriction = AlimentaryRestriction::tryFrom($restriction);
            }
            $user->setAlimentaryRestrictions($data['alimentaryRestrictions'] ?? $user->getAlimentaryRestrictions());
        }

        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }
}