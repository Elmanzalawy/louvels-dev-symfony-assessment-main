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

        if (0 === $countriesCount) {
            // seed countries if no countries are present in db
            $this->seedCountries($restCountriesByCode);
        } else {
            // otherwise update & restore countries
            $this->updateCountries($restCountriesByCode);
        }

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
     * Seed countries. Run during initial seeding.
     */
    private function seedCountries(array $countries): void
    {
        foreach ($countries as $countryData) {
            // update existing country or create a new one
            $this->updateOrCreateCountry($countryData);
        }
    }

    /**
     * Update existing countries or restore deleted ones.
     */
    private function updateCountries(array $restCountriesByCode): void
    {
        $countriesInDB = $this->entityManager->getRepository(Country::class)->findAll();

        // check for deleted countries
        if (count($countriesInDB) < count($restCountriesByCode)) {
            foreach ($restCountriesByCode as $cca3 => $restCountry) {
                $countryExists = $this->entityManager->getRepository(Country::class)->findOneBy(['cca3' => $cca3]);

                if (!$countryExists) {
                    $this->updateOrCreateCountry($restCountry);
                }
            }
        }

        foreach ($countriesInDB as $country) {
            $cca3 = $country->getCca3();
            if (!isset($restCountriesByCode[$cca3])) {
                // if country does not exist in restcountries.com, remove it
                $this->entityManager->remove($country);
            } else {
                // otherwise sync country with restcountries.com
                $countryData = $restCountriesByCode[$cca3];
                $this->updateOrCreateCountry($countryData);
            }
        }
    }

    /**
     * Create country or update it if it exists.
     */
    private function updateOrCreateCountry(array $countryData): void
    {
        $country = $this->countryRepository->findOneBy(['cca3' => $countryData['cca3']]) ?? new Country();
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
