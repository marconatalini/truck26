<?php

namespace App\Factory;

use App\Entity\Mission;
use App\Workflow\State\MissionState;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use function Zenstruck\Foundry\faker;

/**
 * @extends PersistentObjectFactory<Mission>
 */
final class MissionFactory extends PersistentObjectFactory
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
        return Mission::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        $pick_date = \DateTimeImmutable::createFromMutable(self::faker()->dateTimeBetween('+1 hour', 'tomorrow 12 AM'));
        $delivery_date = $pick_date->modify('+4 hour');

        return [
            'pickup_at' => $pick_date,
            'delivery_at' => $delivery_date,
            'distance' => self::faker()->randomNumber(2, true),
            'price' => 0,
//            'weight' => self::faker()->randomNumber(2, true),
            'status' => MissionState::START,
            'express' => self::faker()->boolean(40)
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
             ->afterInstantiate(function(Mission $mission): void {
                 $mission->updateTotalWeightAndArea();
             })
        ;
    }
}
