<?php

namespace App\DataFixtures;

use App\Entity\Program;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProgramFixtures extends Fixture implements DependentFixtureInterface
{

    public const PROGRAMS = [
        ['title' => 'Walking dead',
            'synopsis' => 'Des zombies envahissent la terre',
            'poster' =>"walkingdead-64788ccf9f91f136469706.jpg",
            'category' => 'Action',
            ],
        ['title' => 'Game of Thrones',
            'synopsis' => "Sur le continent de Westeros, en proie au retour de l'Hiver, on se disputent le Trône de Fer.",
            'poster' => "gameofthrones-64788cf37f0dc641292514.jpg",
            'category' => 'Fantastique',
            ],
        ['title' => 'The Last of Us',
            'synopsis' => "Une pandémie provoquée par le cordyceps à décimée l'humanité.",
            'poster' => "the100-647894642f430846313346.webp",
            'category' => 'Action',
        ],
        ['title' => 'The 100',
            'synopsis' => "Après une apocalypse nucléaire les réfugiés des stations spatiales retournent sur Terre.",
            'poster' => "the100-647894642f430846313346.webp",
            'category' => 'Action',
            ],
        ['title' => 'The Haunting of Bly Manor',
            'synopsis' => "Une institutrice est engagée pour veiller sur deux orphelins vivant dans un manoir isolé.",
            'poster' => "thehautingofblymanor-647894773ac9c132687290.jpeg",
            'category' => "Horreur",
            ],
    ];

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
        foreach (self::PROGRAMS as $key => $sousProgram) {

            $program = new Program();
            $program->setTitle($sousProgram['title']);
            $program->setSynopsis($sousProgram['synopsis']);
            $program->setPoster($sousProgram['poster']);
            $program->setCategory($this->getReference('category_' . $sousProgram['category']));
            $this->addReference('program_' . str_replace(' ','',  $sousProgram['title']), $program);
            $slugProgram = $this->slug->slug($sousProgram['title']);
            $program->setSlug($slugProgram);
            $program->setOwner($this->getReference('user_contributor'));
            $manager->persist($program);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        // Tu retournes ici toutes les classes de fixtures dont ProgramFixtures dépend
        return [
            CategoryFixtures::class,
            UserFixtures::class,
        ];
    }
}
