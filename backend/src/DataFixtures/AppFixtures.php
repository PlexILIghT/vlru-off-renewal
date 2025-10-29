<?php

namespace App\DataFixtures;

use App\Entity\BigFolkDistrict;
use App\Entity\Blackout;
use App\Entity\Building;
use App\Entity\City;
use App\Entity\District;
use App\Entity\FolkDistrict;
use App\Entity\Street;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class AppFixtures extends Fixture
{
    private Generator $faker;
    private array $cities = [];
    private array $streets = [];
    private array $districts = [];
    private array $folkDistricts = [];
    private array $bigFolkDistricts = [];
    private array $buildings = [];

    public function load(ObjectManager $manager): void
    {
        $this->faker = Factory::create();

        // Clear existing data (makes it rerunnable)
        $this->clearData($manager);

        $this->loadCities($manager);
        $this->loadDistricts($manager);
        $this->loadFolkDistricts($manager);
        $this->loadBigFolkDistricts($manager);
        $this->loadStreets($manager);
        $this->loadBuildings($manager);
        $this->loadBlackouts($manager);

        $manager->flush();
    }

    private function clearData(ObjectManager $manager): void
    {
        // Clear data in reverse order to respect foreign key constraints
        $manager->createQuery('DELETE FROM App\Entity\Blackout')->execute();
        $manager->createQuery('DELETE FROM App\Entity\Building')->execute();
        $manager->createQuery('DELETE FROM App\Entity\Street')->execute();
        $manager->createQuery('DELETE FROM App\Entity\City')->execute();
        $manager->createQuery('DELETE FROM App\Entity\District')->execute();
        $manager->createQuery('DELETE FROM App\Entity\FolkDistrict')->execute();
        $manager->createQuery('DELETE FROM App\Entity\BigFolkDistrict')->execute();
    }

    private function loadCities(ObjectManager $manager): void
    {
        $cityNames = ['Владивосток', 'Артем'];

        foreach ($cityNames as $cityName) {
            $city = new City();
            $city->setName($cityName);
            $manager->persist($city);
            $this->cities[] = $city;
        }
    }

    private function loadDistricts(ObjectManager $manager): void
    {
        $districtNames = [
            'Ленинский район', 'Первомайский район', 'Первореченский район', 'Советский район',
            'Фрунзенский район'
        ];

        foreach ($districtNames as $districtName) {
            $district = new District();
            $district->setName($districtName);
            $manager->persist($district);
            $this->districts[] = $district;
        }
    }

    private function loadFolkDistricts(ObjectManager $manager): void
    {
        $folkDistrictNames = [
            'Центр', 'о. Русский', 'о. Попова', '64, 71 микрорайон', 'Угловое', 'Район Хребта Богатая Грива',
            'Поворот на Шамору', 'Заря', 'Шамора', 'Гайдамак', 'Луговая'
        ];

        foreach ($folkDistrictNames as $folkDistrictName) {
            $folkDistrict = new FolkDistrict();
            $folkDistrict->setName($folkDistrictName);
            $manager->persist($folkDistrict);
            $this->folkDistricts[] = $folkDistrict;
        }
    }

    private function loadBigFolkDistricts(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $bigFolkDistrict = new BigFolkDistrict();
            $manager->persist($bigFolkDistrict);
            $this->bigFolkDistricts[] = $bigFolkDistrict;
        }
    }

    private function loadStreets(ObjectManager $manager): void
    {
        $streetNames = [
            'Кирова ул.', 'Самаргинская ул.', 'Южная ул.', 'Чайковского ул.', 'Керченская ул.', 'Патрокл ул.',
            'Зеленая (о. Русский) ул.', 'Барнаульская ул.', 'Морская 1-я ул.', 'Джамбула ул.', 'Майора Филипова ул.',
            'Садовый пер.', 'Комсомольская ул.', 'Восьмая (пос. трудовое) ул.', 'Уткинская ул.', 'Раздольный пер.',
            'Промышленная 1-я ул.', 'Шахтерский 3-й пер.', 'Арсеньева (п-ов. Песчанный) ул.', 'Арсеньева ул.',
            'Былинная (пос. трудовое) ул.', 'Чехова ул.', 'Сабанеева ул.', 'Шошина ул.', 'Бикинская ул.',
            'Пятилетка 6-я ул.', 'Линейный 2-й пер.', 'Омская ул.', 'Бианки ул.', 'ст Аралия (у. Соловей-Ключ) ул.',
            'Донбасская ул.', '2-й Севский пер.',  'Айвазовского ул.', 'Балтийская ул.',  'Пригородная 3-я ул.'
        ];

        foreach ($streetNames as $streetName) {
            $street = new Street();
            $street->setName($streetName);
            $street->setCity($this->faker->randomElement($this->cities));
            $manager->persist($street);
            $this->streets[] = $street;
        }
    }

    private function loadBuildings(ObjectManager $manager): void
    {
        $buildingTypes = ['жилое многоквартирное', 'строящееся', 'нежилое', 'частный дом'];

        for ($i = 0; $i < 200; $i++) {
            $building = new Building();
            $building->setStreet($this->faker->randomElement($this->streets));
            $building->setNumber($this->faker->buildingNumber());
            $building->setDistrict($this->faker->randomElement($this->districts));
            $building->setIsFake($this->faker->boolean(90)); // 90% of fakes
            $building->setFolkDistrict($this->faker->randomElement($this->folkDistricts));
            $building->setBigFolkDistrict($this->faker->randomElement($this->bigFolkDistricts));
            $building->setType($this->faker->randomElement($buildingTypes));
            $building->setCity($this->faker->randomElement($this->cities));
            $building->setCoordinates([
                'lat' => $this->faker->latitude(42.91618252401699, 43.48271870000001),
                'lng' => $this->faker->longitude(131.71302120105406, 132.3344938453211)
            ]);

            $manager->persist($building);
            $this->buildings[] = $building;
        }
    }

    private function loadBlackouts(ObjectManager $manager): void
    {
        $blackoutTypes = ['electricity', 'cold_water', 'hot_water', 'heat'];
        $initiatorNames = ['ООО "Управляющая компания "Регион-ЖКХ"', 'ТСЖ "Залив-2"', 'ООО "УО "ДВКС"', 'ООО УК «Атлант»'];
        $sources = ['ООО "Паллада"', 'ООО "РЭУ у Порта"', 'ООО "Невельского"', null, null, null, null];

        for ($i = 0; $i < 50; $i++) {
            $blackout = new Blackout();
            $blackout->setStartDate($this->faker->dateTimeBetween('-1 month', 'now'));
            $blackout->setEndDate($this->faker->dateTimeBetween('now', '+1 day'));
            $blackout->setDescription($this->faker->text(200));
            $blackout->setType($this->faker->randomElement($blackoutTypes));
            $blackout->setInitiatorName($this->faker->randomElement($initiatorNames));
            $blackout->setSource($this->faker->randomElement($sources));

            // Add random buildings to this blackout
            $affectedBuildings = $this->faker->randomElements(
                $this->buildings,
                $this->faker->numberBetween(1, 10)
            );

            foreach ($affectedBuildings as $building) {
                $blackout->addBuilding($building);
            }

            $manager->persist($blackout);
        }
    }
}
