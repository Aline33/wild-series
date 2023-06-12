<?php

namespace App\Twig\Components;

use App\Repository\ActorRepository;
use App\Repository\CategoryRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('navbar')]
final class NavbarComponent
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private ActorRepository $actorRepository,
    ) {
    }

    public function getCategories(): array
    {
        return $this->categoryRepository->findBy([], ['name' => 'ASC']);
    }

    public function getActors(): array
    {
        return $this->actorRepository->findBy([], ['name' => 'ASC']);
    }
}
