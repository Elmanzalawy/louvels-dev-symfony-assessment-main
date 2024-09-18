<?php

namespace App\Service;

use App\Entity\Country;
use App\Service\Contract\CountriesHttpInterface;
use Doctrine\ORM\EntityManagerInterface;

class CountrySyncService
{
    private $entityManager;
    private $countriesHttpService;

    public function __construct(EntityManagerInterface $entityManager, CountriesHttpInterface $countriesHttpService)
    {
        $this->entityManager = $entityManager;
        $this->countriesHttpService = $countriesHttpService;
    }

    /**
     * Sync database countries with restcountries.com
     * Invalid countries will be deleted on sync.
     */
    public function syncCountries(): void
    {
        // get countries from 3rd party
        $restCountriesByCode = $this->countriesHttpService->getRestCountries();

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
