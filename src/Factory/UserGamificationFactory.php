<?php

namespace App\Factory;

use App\Entity\UserGamification;
use App\Enum\UserRole;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<UserGamification>
 */
final class UserGamificationFactory extends PersistentObjectFactory
{
    public function __construct()
    {
    }

    #[\Override]
    public static function class(): string
    {
        return UserGamification::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'date' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'event' => self::faker()->text(50),
            'points' => self::faker()->randomElement([0, 10, 20, 50, 100]),
            'user' => UserFactory::random(),
        ];
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(UserGamification $userGamification): void {})
        ;
    }
}
