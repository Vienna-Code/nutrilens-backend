<?php

namespace App\Service;

use App\Entity\User;
use App\Enum\AlimentaryRestriction;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private GamificationManager $gm,
        private UserPasswordHasherInterface $passwordHasher,
        private ImageRepository $imageRepository,
    ) {}

    public function create(array &$data, array $privileges): User|false
    {
        $user = new User();
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setAlimentaryRestrictions($data['alimentaryRestrictions'] ?? []);
        $user->setRoles(['ROLE_USER']);
        $user->setVerification(null);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        if (isset($data['profilePicture'])) {
            $image = $this->imageRepository->find($data['profilePicture']);
            if (!$image) {
                throw new \InvalidArgumentException('La imagen no fue encontrada');
            }

            $user->setProfilePicture($data['profilePicture']);
        }

        if (isset($data['roles']) && \in_array('ROLE_ADMIN', $privileges)) {
            $roles = array_unique(array_merge($data['roles'], ['ROLE_USER']));
            $user->setRoles($roles);
        }

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function update(array &$data, User &$user, array $privileges): User|false
    {
        if (isset($data['alimentaryRestrictions'])) {
            foreach ($data['alimentaryRestrictions'] as &$restriction) {
                $restriction = AlimentaryRestriction::tryFrom($restriction);
            }
            $user->setAlimentaryRestrictions($data['alimentaryRestrictions'] ?? $user->getAlimentaryRestrictions());
        }

        if (isset($data['profilePicture'])) {
            $image = $this->imageRepository->find($data['profilePicture']);
            if (!$image) {
                throw new \InvalidArgumentException('La imagen no fue encontrada');
            }

            $user->setProfilePicture($data['profilePicture']);
        }

        if (isset($data['roles']) && \in_array('ROLE_ADMIN', $privileges)) {
            $roles = array_unique(array_merge($data['roles'], ['ROLE_USER']));
            $user->setRoles($roles);
        }

        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }
}