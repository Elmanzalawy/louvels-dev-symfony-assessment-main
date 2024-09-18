<?php

namespace App\Service;

use App\Entity\Country;
use App\Repository\CountryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CountrySyncService
{
    private $httpClient;
    private $entityManager;
    private $countryRepository;

    public function __construct(HttpClientInterface $httpClient, EntityManagerInterface $entityManager, CountryRepository $countryRepository)
    {
        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;
        $this->countryRepository = $countryRepository;
    }

    /**
     * Sync database countries with restcountries.com
     * Invalid countries will be deleted on sync.
     */
    public function syncCountries(): void
    {
        // get countries from 3rd party
        $restCountriesByCode = $this->getRestCountries();

        // get countries count in the database
        $countriesCount = $this->entityManager->getRepository(Country::class)->count();

        if ($countriesCount > 0) {
            $this->entityManager->createQueryBuilder()->delete(Country::class, 'c')->getQuery()->execute();
        }

        $this->seedCountries($restCountriesByCode);

        // flush changes to the database
        $this->entityManager->flush();
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

    /**
     * Insert countries into database.
     */
    private function seedCountries(array $countries): void
    {
        foreach ($countries as $countryData) {
            // update existing country or create a new one
            $this->createCountry($countryData);
        }
    }

    /**
     * Create country or update it if it exists.
     */
    private function createCountry(array $countryData): void
    {
        $country = new Country();
        $country->setName($countryData['name']['common'] ?? null);
        $country->setCca3($countryData['cca3']);
        $country->setRegion($countryData['region'] ?? null);
        $country->setSubregion($countryData['subregion'] ?? null);
        $country->setIndependant($countryData['independent'] ?? false);
        $country->setPopulation($countryData['population'] ?? null);
        $country->setFlag($countryData['flag'] ?? null);
        $country->setCurrency($countryData['currencies'] ?? []);
        $country->setDemonym($countryData['demonyms'] ?? []);

        $this->entityManager->persist($country);
    }
}
