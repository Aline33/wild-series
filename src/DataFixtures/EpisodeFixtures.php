<?php

namespace App\DataFixtures;

use App\Entity\Season;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Episode;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class EpisodeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach(ProgramFixtures::PROGRAMS as $program) {
            foreach(SeasonFixtures::SEASONS as $season) {
                for ($i = 1; $i < 3; $i++) {
                    $episode = new Episode();
                    $episode->setTitle('Episode numéro' . $i);
                    $episode->setNumber($i);
                    $episode->setSeason($this->getReference('season' . $season . '_' . str_replace(' ', '', $program['title'])));
                    $episode->setSynopsis('Le synopsis !!');
                    $manager->persist($episode);
                }
            }
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        // Tu retournes ici toutes les classes de fixtures dont ProgramFixtures dépend
        return [
            SeasonFixtures::class,
        ];
    }
}
