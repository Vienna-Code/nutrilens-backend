<?php

namespace App\Factory;

use App\Entity\User;
use App\Enum\AlimentaryRestriction;
use App\Enum\UserRole;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<User>
 */
final class UserFactory extends PersistentObjectFactory
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    #[\Override]
    public static function class(): string
    {
        return User::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'alimentaryRestrictions' => [self::faker()->randomElement(AlimentaryRestriction::cases())],
            'createdAt' => new \DateTimeImmutable(),
            'email' => self::faker()->unique()->safeEmail(),
            'password' => 'fakeuserpassword',
            'points' => 0,
            'roles' => ['ROLE_UNVERIFIED'],
            'verification' => bin2hex(random_bytes(32)),
            'username' => self::faker()->unique()->userName(),
        ];
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function(User $user) {
                $plain = $user->getPassword();
                if (str_starts_with($plain, '$2y$')) {
                    throw new \ErrorException('Password must be passed to the factory in plain text for hashing.');
                }
                $user->setPassword($this->passwordHasher->hashPassword($user, $plain));
            })
        ;
    }
}
