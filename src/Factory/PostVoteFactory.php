<?php

namespace App\Factory;

use App\Entity\PostVote;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<PostVote>
 */
final class PostVoteFactory extends PersistentObjectFactory
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
        return PostVote::class;
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
            'post' => PostFactory::random(),
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
            // ->afterInstantiate(function(PostVote $postVote): void {})
        ;
    }
}
