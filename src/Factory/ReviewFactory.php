<?php

namespace App\Factory;

use App\Entity\Review;
use App\Enum\Visibility;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Review>
 */
final class ReviewFactory extends PersistentObjectFactory
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
        return Review::class;
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
            'content' => self::faker()->text(500),
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'positive' => self::faker()->boolean(75),
            'updatedAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'useful' => 0,
            'visibility' => Visibility::PUBLIC,
            'user' => UserFactory::random(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Review $review): void {})
        ;
    }
}
