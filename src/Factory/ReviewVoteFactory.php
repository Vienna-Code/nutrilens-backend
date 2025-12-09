<?php

namespace App\Factory;

use App\Entity\ReviewVote;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<ReviewVote>
 */
final class ReviewVoteFactory extends PersistentObjectFactory
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
        return ReviewVote::class;
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
            'review' => ReviewFactory::random(),
            'user' => UserFactory::random(),
            'positive' => self::faker()->randomElement([
                true, true, true, true, true, true, false, false, null
            ])
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(ReviewVote $reviewVote): void {})
        ;
    }
}
