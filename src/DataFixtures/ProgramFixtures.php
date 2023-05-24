<?php

namespace App\DataFixtures;

use App\Entity\Program;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProgramFixtures extends Fixture implements DependentFixtureInterface
{

    public const PROGRAMS = [
        ['title' => 'Walking dead',
            'synopsis' => 'Des zombies envahissent la terre',
            'category' => 'Action',
            ],
        ['title' => 'Game of Thrones',
            'synopsis' => "Sur le continent de Westeros, en proie au retour de l'Hiver, on se disputent le Trône de Fer.",
            'category' => 'Fantastique',
            ],
        ['title' => 'The Last of Us',
            'synopsis' => "Une pandémie provoquée par le cordyceps à décimée l'humanité.",
            'category' => 'Action',
        ],
        ['title' => 'The 100',
            'synopsis' => "Après une apocalypse nucléaire les réfugiés des stations spatiales retournent sur Terre.",
            'category' => 'Action',
            ],
        ['title' => 'The Haunting of Bly Manor',
            'synopsis' => "Une institutrice est engagée pour veiller sur deux orphelins vivant dans un manoir isolé.",
            'category' => "Horreur",
            ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::PROGRAMS as $key => $sousProgram) {

            $program = new Program();
            $program->setTitle($sousProgram['title']);
            $program->setSynopsis($sousProgram['synopsis']);
            $program->setCategory($this->getReference('category_' . $sousProgram['category']));
            $this->addReference('program_' . str_replace(' ','',  $sousProgram['title']), $program);
            $manager->persist($program);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        // Tu retournes ici toutes les classes de fixtures dont ProgramFixtures dépend
        return [
            CategoryFixtures::class,
        ];
    }
}
