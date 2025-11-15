<?php

namespace App\Factory;

use App\Entity\Commerce;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Commerce>
 */
final class CommerceFactory extends PersistentObjectFactory
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
        return Commerce::class;
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
            'address' => self::faker()->streetAddress(),
            'contactInfo' => [
                'number' => '+598' . self::faker()->numerify('########'),
                'email' => self::faker()->safeEmail()
            ],
            'coordsLat' => self::faker()->randomFloat(7, -34.8938000, -34.8242000),
            'coordsLon' => self::faker()->randomFloat(7, -56.1954000, -56.1123000),
            'name' => self::faker()->company(),
            'paymentMethods' => array_values(array_filter(['efectivo', 'credito', 'debito'], fn() => self::faker()->boolean(80))),
            'type' => self::faker()->randomElement([
                'kiosco', 'kiosco', 'kiosco', 'supermercado', 'supermercado', 'restaurante'
            ]),
            'verified' => self::faker()->boolean(80)
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Commerce $commerce): void {})
        ;
    }
}
