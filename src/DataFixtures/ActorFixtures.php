<?php

namespace App\DataFixtures;

use App\Entity\Actor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ActorFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for($i = 1; $i < 11; $i++) {
            $actor = new Actor();
            $actor->setName($faker->name());
            $programs = array_rand(ProgramFixtures::PROGRAMS, 3);
            $idProgram1 = $programs[0];
            $idProgram2 = $programs[1];
            $idProgram3 = $programs[2];
            $titleProgram1 = ProgramFixtures::PROGRAMS[$idProgram1]['title'];
            $titleProgram2 = ProgramFixtures::PROGRAMS[$idProgram2]['title'];
            $titleProgram3 = ProgramFixtures::PROGRAMS[$idProgram3]['title'];
            $actor->addProgram($this->getReference('program_' . str_replace(' ', '', $titleProgram1)));
            $actor->addProgram($this->getReference('program_' . str_replace(' ', '', $titleProgram2)));
            $actor->addProgram($this->getReference('program_' . str_replace(' ', '', $titleProgram3)));

            $manager->persist($actor);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            ProgramFixtures::class,
        ];
    }
}
