<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Season;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;

class SeasonFixtures extends Fixture implements DependentFixtureInterface
{
    const SEASONS = [1, 2, 3, 4, 5];

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        foreach(ProgramFixtures::PROGRAMS as $program) {
            foreach (self::SEASONS as $seasonNumber) {
                $season = new Season();
                $season->setNumber($seasonNumber);
                $season->setProgram($this->getReference('program_' . str_replace(' ', '', $program['title'])));
                $season->setYear($faker->year());
                $season->setDescription($faker->paragraphs(3, true));
                $manager->persist($season);
                $this->addReference('season' . $seasonNumber . '_' . str_replace(' ', '', $program['title']), $season);
            }
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        // Tu retournes ici toutes les classes de fixtures dont ProgramFixtures dépend
        return [
            ProgramFixtures::class,
        ];
    }
}
