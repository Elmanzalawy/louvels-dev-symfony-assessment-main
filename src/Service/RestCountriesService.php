<?php

namespace App\Service;

use App\Service\Contract\CountriesHttpInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RestCountriesService implements CountriesHttpInterface
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Returns array of countries returned by restcountries.com.
     *
     * @throws \Exception
     */
    public function getRestCountries(): array
    {
        // get countries from restcountries.com
        $response = $this->httpClient->request('GET', 'https://restcountries.com/v3.1/all');

        if (200 !== $response->getStatusCode()) {
            throw new \Exception('An error has occured while fetching country data');
        }
        $restCountries = $response->toArray();

        $restCountriesByCode = [];
        foreach ($restCountries as $restCountry) {
            $restCountriesByCode[$restCountry['cca3']] = $restCountry;
        }

        return $restCountriesByCode;
    }
}
