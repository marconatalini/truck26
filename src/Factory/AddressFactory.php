<?php

namespace App\Factory;

use App\Entity\Address;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Address>
 */
final class AddressFactory extends PersistentObjectFactory
{
    public const ATTIVITA = [
        ["Nome attività"=>"Alfa Servizi SRL","Indirizzo"=>"Via Roma 12","comune"=>"Verona","sigla provincia"=>"VR"],
        ["Nome attività"=>"Beta Impianti SNC","Indirizzo"=>"Via Milano 45","comune"=>"Verona","sigla provincia"=>"VR"],
        ["Nome attività"=>"Gamma Costruzioni","Indirizzo"=>"Via Napoli 19","comune"=>"Cerea","sigla provincia"=>"VR"],
        ["Nome attività"=>"Delta Food SRL","Indirizzo"=>"Via Oppi 103","comune"=>"Casaleone","sigla provincia"=>"VR"],
        ["Nome attività"=>"Epsilon Tech","Indirizzo"=>"Via Francia 1","comune"=>"San Giovanni Lupatoto","sigla provincia"=>"VR"],
        ["Nome attività"=>"Zeta Logistic","Indirizzo"=>"Via Sommacampagna 63","comune"=>"Verona","sigla provincia"=>"VR"],
        ["Nome attività"=>"Eta Consulting","Indirizzo"=>"Corso Porta Nuova 96","comune"=>"Verona","sigla provincia"=>"VR"],
        ["Nome attività"=>"Theta Energia","Indirizzo"=>"Via Brescia 22","comune"=>"Peschiera del Garda","sigla provincia"=>"VR"],
        ["Nome attività"=>"Iota Solutions","Indirizzo"=>"Via Mantova 8","comune"=>"Legnago","sigla provincia"=>"VR"],
        ["Nome attività"=>"Kappa Design","Indirizzo"=>"Via Venezia 55","comune"=>"Villafranca di Verona","sigla provincia"=>"VR"],

        ["Nome attività"=>"Lambda SRL","Indirizzo"=>"Viale Roma 101","comune"=>"Vicenza","sigla provincia"=>"VI"],
        ["Nome attività"=>"Mu Impianti","Indirizzo"=>"Via Torino 44","comune"=>"Vicenza","sigla provincia"=>"VI"],
        ["Nome attività"=>"Nu Costruzioni","Indirizzo"=>"Via Verdi 12","comune"=>"Schio","sigla provincia"=>"VI"],
        ["Nome attività"=>"Xi Engineering","Indirizzo"=>"Via Marconi 33","comune"=>"Bassano del Grappa","sigla provincia"=>"VI"],
        ["Nome attività"=>"Omicron Tech","Indirizzo"=>"Via Dante 27","comune"=>"Arzignano","sigla provincia"=>"VI"],
        ["Nome attività"=>"Pi Sistemi","Indirizzo"=>"Via Garibaldi 9","comune"=>"Valdagno","sigla provincia"=>"VI"],
        ["Nome attività"=>"Rho Services","Indirizzo"=>"Via Pasubio 60","comune"=>"Thiene","sigla provincia"=>"VI"],
        ["Nome attività"=>"Sigma SRL","Indirizzo"=>"Via Europa 15","comune"=>"Montecchio Maggiore","sigla provincia"=>"VI"],
        ["Nome attività"=>"Tau Consulting","Indirizzo"=>"Via Veneto 88","comune"=>"Lonigo","sigla provincia"=>"VI"],
        ["Nome attività"=>"Upsilon Group","Indirizzo"=>"Via Asiago 5","comune"=>"Asiago","sigla provincia"=>"VI"],

        ["Nome attività"=>"Phi Servizi","Indirizzo"=>"Corso del Popolo 77","comune"=>"Rovigo","sigla provincia"=>"RO"],
        ["Nome attività"=>"Chi Impianti","Indirizzo"=>"Via Mazzini 21","comune"=>"Rovigo","sigla provincia"=>"RO"],
        ["Nome attività"=>"Psi Costruzioni","Indirizzo"=>"Via Eridania 14","comune"=>"Adria","sigla provincia"=>"RO"],
        ["Nome attività"=>"Omega SRL","Indirizzo"=>"Via Romea 200","comune"=>"Porto Viro","sigla provincia"=>"RO"],
        ["Nome attività"=>"Delta Nord","Indirizzo"=>"Via Garibaldi 3","comune"=>"Lendinara","sigla provincia"=>"RO"],
        ["Nome attività"=>"Polesine Tech","Indirizzo"=>"Via Veneto 56","comune"=>"Badia Polesine","sigla provincia"=>"RO"],
        ["Nome attività"=>"Adige Service","Indirizzo"=>"Via Po 10","comune"=>"Occhiobello","sigla provincia"=>"RO"],
        ["Nome attività"=>"Rovigo Energia","Indirizzo"=>"Via Trieste 42","comune"=>"Rovigo","sigla provincia"=>"RO"],
        ["Nome attività"=>"Veneta Consulting","Indirizzo"=>"Via Dante 18","comune"=>"Taglio di Po","sigla provincia"=>"RO"],
        ["Nome attività"=>"Adria Logistic","Indirizzo"=>"Via del Mare 9","comune"=>"Rosolina","sigla provincia"=>"RO"],

        ["Nome attività"=>"Brescia Tech","Indirizzo"=>"Via Milano 120","comune"=>"Brescia","sigla provincia"=>"BS"],
        ["Nome attività"=>"Lombarda SRL","Indirizzo"=>"Via Roma 11","comune"=>"Brescia","sigla provincia"=>"BS"],
        ["Nome attività"=>"Sebino Impianti","Indirizzo"=>"Via Iseo 45","comune"=>"Iseo","sigla provincia"=>"BS"],
        ["Nome attività"=>"Garda Solutions","Indirizzo"=>"Via Desenzano 77","comune"=>"Desenzano del Garda","sigla provincia"=>"BS"],
        ["Nome attività"=>"Valcamonica SRL","Indirizzo"=>"Via Nazionale 88","comune"=>"Darfo Boario Terme","sigla provincia"=>"BS"],
        ["Nome attività"=>"Franciacorta Wine Tech","Indirizzo"=>"Via Provinciale 12","comune"=>"Erbusco","sigla provincia"=>"BS"],
        ["Nome attività"=>"Oglio Servizi","Indirizzo"=>"Via Cremona 66","comune"=>"Manerbio","sigla provincia"=>"BS"],
        ["Nome attività"=>"Camuna Costruzioni","Indirizzo"=>"Via Brescia 5","comune"=>"Edolo","sigla provincia"=>"BS"],
        ["Nome attività"=>"Sebino Consulting","Indirizzo"=>"Via Bergamo 39","comune"=>"Palazzolo sull'Oglio","sigla provincia"=>"BS"],
        ["Nome attività"=>"Lago SRL","Indirizzo"=>"Via Sirmione 25","comune"=>"Sirmione","sigla provincia"=>"BS"],

        ["Nome attività"=>"Dolomiti Tech","Indirizzo"=>"Via Roma 14","comune"=>"Trento","sigla provincia"=>"TN"],
        ["Nome attività"=>"AltoAdige SRL","Indirizzo"=>"Via Bolzano 33","comune"=>"Trento","sigla provincia"=>"TN"],
        ["Nome attività"=>"Trentino Servizi","Indirizzo"=>"Via Brennero 120","comune"=>"Trento","sigla provincia"=>"TN"],
        ["Nome attività"=>"Sudtirol Consulting","Indirizzo"=>"Via Museo 5","comune"=>"Bolzano","sigla provincia"=>"BZ"],
        ["Nome attività"=>"Alpen Energie","Indirizzo"=>"Via Innsbruck 18","comune"=>"Bolzano","sigla provincia"=>"BZ"],
        ["Nome attività"=>"Dolomiti Costruzioni","Indirizzo"=>"Via Gardena 9","comune"=>"Ortisei","sigla provincia"=>"BZ"],
        ["Nome attività"=>"Val Pusteria SRL","Indirizzo"=>"Via Brunico 21","comune"=>"Brunico","sigla provincia"=>"BZ"],
        ["Nome attività"=>"Trento Logistic","Indirizzo"=>"Via Rovereto 44","comune"=>"Rovereto","sigla provincia"=>"TN"],
        ["Nome attività"=>"Lavis Tech","Indirizzo"=>"Via Nazionale 2","comune"=>"Lavis","sigla provincia"=>"TN"],
        ["Nome attività"=>"Merano Servizi","Indirizzo"=>"Via Roma 60","comune"=>"Merano","sigla provincia"=>"BZ"]
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
        return Address::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        $key = array_rand(self::ATTIVITA);

        return [
            'addressLine1' => self::ATTIVITA[$key]['Indirizzo'],
            'addressLine2' => null,
            'city' => self::ATTIVITA[$key]['comune'],
            'province' => self::ATTIVITA[$key]['sigla provincia'],
            'postalCode' => null,
            'country' => null,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Address $address): void {})
        ;
    }
}
