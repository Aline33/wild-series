<?php

namespace App\DataFixtures;

use App\DataFixtures\ProgramFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Season;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class SeasonFixtures extends Fixture implements DependentFixtureInterface
{
    const SEASONS = [1, 2];

    public function load(ObjectManager $manager): void
    {
        foreach(ProgramFixtures::PROGRAMS as $program) {
            foreach (self::SEASONS as $seasonNumber) {
                $season = new Season();
                $season->setNumber($seasonNumber);
                $season->setProgram($this->getReference('program_' . str_replace(' ', '', $program['title'])));
                $season->setYear(2023);
                $season->setDescription("Description de la saison");
                $manager->persist($season);
                $this->addReference('season' . $seasonNumber . '_' . str_replace(' ', '', $program['title']), $season);
            }
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        // Tu retournes ici toutes les classes de fixtures dont ProgramFixtures dépend
        return [
            ProgramFixtures::class,
        ];
    }
}
