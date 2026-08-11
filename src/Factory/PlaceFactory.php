<?php

namespace App\Factory;

use App\Entity\Place;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Place>
 */
final class PlaceFactory extends PersistentObjectFactory
{

    const PLACES = ["Alfa Servizi SRL", "Beta Impianti SNC", "Gamma Costruzioni", "Delta Food SRL", "Epsilon Tech", "Zeta Logistic", "Eta Consulting", "Theta Energia", "Iota Solutions", "Kappa Design", "Lambda SRL", "Mu Impianti", "Nu Costruzioni", "Xi Engineering", "Omicron Tech", "Pi Sistemi", "Rho Services", "Sigma SRL", "Tau Consulting", "Upsilon Group", "Phi Servizi", "Chi Impianti", "Psi Costruzioni", "Omega SRL", "Delta Nord", "Polesine Tech", "Adige Service", "Rovigo Energia", "Veneta Consulting", "Adria Logistic", "Brescia Tech", "Lombarda SRL", "Sebino Impianti", "Garda Solutions", "Valcamonica SRL", "Franciacorta Wine Tech", "Oglio Servizi", "Camuna Costruzioni", "Sebino Consulting", "Lago SRL", "Dolomiti Tech", "AltoAdige SRL", "Trentino Servizi", "Sudtirol Consulting", "Alpen Energie", "Dolomiti Costruzioni", "Val Pusteria SRL", "Trento Logistic", "Lavis Tech", "Merano Servizi"
    ];

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
        return Place::class;
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
            'name' => self::faker()->randomElement(self::PLACES),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Place $place): void {})
        ;
    }
}
