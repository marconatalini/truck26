<?php

namespace App\Factory;

use App\Entity\Package;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Package>
 */
final class     PackageFactory extends PersistentObjectFactory
{
    const ASPECTS = ['bancale', 'scatola', 'gabbia', 'fascio'];
    const PACKAGE_DEFAULTS = [
        'bancale' => '1200x800x1000x300',
        'scatola' => '300x500x400x25',
        'gabbia' => '1000x1000x1000x500',
        'fascio' => '100x2500x100x20'];

    private function setDefaults(Package $package, string $aspect): void
    {
        $package->setWidth(explode("x", self::PACKAGE_DEFAULTS[$aspect])[0]);
        $package->setLength(explode("x", self::PACKAGE_DEFAULTS[$aspect])[1]);
        $package->setHeight(explode("x", self::PACKAGE_DEFAULTS[$aspect])[2]);
        $package->setWeight(explode("x", self::PACKAGE_DEFAULTS[$aspect])[3]);
    }

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
        return Package::class;
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
            'quantity' => self::faker()->randomNumber(1,true),
            'aspect' => self::faker()->randomElement(self::ASPECTS)
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
             ->afterInstantiate(function(Package $package): void {
                 $this->setDefaults($package, $package->getAspect());
             })
        ;
    }
}
