<?php

namespace App\Service;

use Exception;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeocodingService
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getCoordinates(string $address, string $city, string $province): ?string
    {
        try {
            $response = $this->client->request('GET', 'https://nominatim.openstreetmap.org/search',
                ['query' => [
                    'q' => "$address, $city, $province",
                    'format' => 'json',
                    'limit' => 1,
                    'addressdetails' => 1,
                    'countrycodes' => 'it' // Limita la ricerca all'Italia
                ],
                    'headers' => [// Nominatim richiede un User-Agent identificativo
                    'User-Agent' => 'Truck26/1.0 (info@natalinitrasporti.it)']
                ]);

            $data = $response->toArray();

            if (empty($data)) {
                return null;
            }

            $lat = $data[0]['lat'];
            $lon = $data[0]['lon'];

            // Restituiamo il formato (lat,lng) pronto per il tipo POINT di Postgres
            return "($lat,$lon)";

        } catch (Exception $e) {
            // Logga l'errore se necessario
            return null;
        }
    }
}

