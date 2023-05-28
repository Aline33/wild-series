<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Episode;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;
use Symfony\Component\String\Slugger\SluggerInterface;

class EpisodeFixtures extends Fixture implements DependentFixtureInterface
{

    private sluggerInterface $slug;

    /**
     * @param SluggerInterface $slug
     */
    public function __construct(SluggerInterface $slug)
    {
        $this->slug = $slug;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        foreach(ProgramFixtures::PROGRAMS as $program) {
            foreach(SeasonFixtures::SEASONS as $season) {
                for ($i = 1; $i < 11; $i++) {
                    $episode = new Episode();
                    $episode->setTitle($faker->sentence());
                    $episode->setNumber($i);
                    $episode->setSeason($this->getReference('season' . $season . '_' . str_replace(' ', '', $program['title'])));
                    $episode->setSynopsis($faker->paragraphs(3, true));
                    $episode->setDuration($faker->numberBetween(30,60));
                    $slugEpisode = $this->slug->slug($episode->getTitle());
                    $episode->setSlug($slugEpisode);
                    $manager->persist($episode);
                }
            }
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            SeasonFixtures::class,
        ];
    }
}
