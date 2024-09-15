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

        // get all countries from the database
        $countries = $this->entityManager->getRepository(Country::class)->findAll();

        if (0 == count($countries)) {
            $this->seedCountries($restCountries);
        } else {
            // check for deleted countries
            if (count($countries) < count($restCountries)) {
                foreach ($restCountriesByCode as $cca3 => $restCountry) {
                    $countryExists = $this->entityManager->getRepository(Country::class)->findOneBy(['cca3' => $cca3]);

                    if (!$countryExists) {
                        $this->updateOrCreateCountry($restCountry);
                    }
                }
            }
            foreach ($countries as $country) {
                $cca3 = $country->getCca3();
                if (!isset($restCountriesByCode[$cca3])) {
                    // if country does not exist in restcountries.com, remove it
                    $this->entityManager->remove($country);
                } else {
                    $countryData = $restCountriesByCode[$cca3];
                    $this->updateOrCreateCountry($countryData);
                }
            }
        }

        // flush changes to the database
        $this->entityManager->flush();
    }

    /**
     * Seed countries. Run during initial seeding.
     *
     * @param mixed $countries
     */
    private function seedCountries(array $countries): void
    {
        foreach ($countries as $countryData) {
            // update existing country or create a new one
            $this->updateOrCreateCountry($countryData);
        }
    }

    /**
     * Create country or update it if it exists.
     */
    private function updateOrCreateCountry($countryData): void
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
