<?php

namespace App\Service;

use App\Entity\User;
use App\Enum\AlimentaryRestriction;
use App\Enum\UserRank;
use Doctrine\ORM\EntityManagerInterface;

class UserManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private GamificationManager $gm,
    ) {}

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