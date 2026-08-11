<?php

namespace App\Factory;

use App\Entity\Vehicle;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Vehicle>
 */
final class VehicleFactory extends PersistentObjectFactory
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
        return Vehicle::class;
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
            'height' => 2500,
            'is_available' => true,
            'length' => 5000,
            'model' => self::faker()->randomElement(['FORD', 'MAN', 'IVECO', 'SCANIA', 'FIAT']),
            'next_inspection_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'next_tax_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'plate' => self::faker()->unique()->randomElement(['AA123BC', 'BB456DE', 'CC789FG', 'DD321HI', 'EE654JK', 'FF987LM',
                'GG234NP', 'HH567QR', 'II890ST', 'JJ123UV',
                'KL345WX', 'LM678YZ', 'MN901AB', 'NO234CD', 'OP567EF', 'PQ890GH', 'QR123IJ', 'RS456KL', 'ST789MN', 'TU012OP',
                'UV345QR', 'VW678ST', 'WX901UV', 'XY234WX', 'YZ567YZ', 'ZA890AB', 'AB123CD', 'BC456EF', 'CD789GH', 'DE012IJ'
            ]),
            'purchase_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'weight_range' => 850,
            'width' => 2100,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Vehicle $vehicle): void {})
        ;
    }
}
