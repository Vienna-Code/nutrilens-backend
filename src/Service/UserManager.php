<?php

namespace App\Service;

use App\Entity\User;
use App\Enum\AlimentaryRestriction;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private GamificationManager $gm,
        private UserPasswordHasherInterface $passwordHasher,
        private ImageRepository $imageRepository,
    ) {}

    public function create(array &$data, array $privileges = []): User|false
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

    public function update(array &$data, User &$user, User $operator): User|false
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

        $isAdmin = \in_array('ROLE_ADMIN', $operator->getRoles());

        if (isset($data['roles']) && $isAdmin) {
            $roles = array_unique(array_merge($data['roles'], ['ROLE_USER']));
            $user->setRoles($roles);
        }

        if (isset($data['newPassword'])) {
            $canBypass = $isAdmin && $operator !== $user;

            if (!$canBypass) {
                if (!isset($data['currentPassword'])) {
                    throw new BadRequestHttpException('Current password required');
                }
    
                if (!$this->passwordHasher->isPasswordValid($user, $data['currentPassword'])) {
                    throw new UnauthorizedHttpException('Current password is incorrect');
                }
            }

            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['newPassword']);
            $user->setPassword($hashedPassword);
        }

        if ($isAdmin) {
            $user->setUsername($data['username'] ?? $user->getUsername());
            $user->setEmail($data['email'] ?? $user->getEmail());
        }

        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }
}