<?php

namespace App\Factory;

use App\Entity\CommerceSchedule;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<CommerceSchedule>
 */
final class CommerceScheduleFactory extends PersistentObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    #[\Override]
    public static function class(): string
    {
        return CommerceSchedule::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'commerce' => CommerceFactory::random(),
            'weekday' => self::faker()->numberBetween(0,6),
            'opensAt' => new \DateTimeImmutable(self::faker()->randomElement(['07:00', '09:00', '12:00', '15:00'])),
            'closesAt' => new \DateTimeImmutable(self::faker()->randomElement(['18:00', '21:00', '23:00'])),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(CommerceSchedule $commerceSchedule): void {})
        ;
    }
}
