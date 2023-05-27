<?php

namespace App\Controller;

use App\Entity\Actor;
use App\Entity\Episode;
//use App\Form\ActorType;
use App\Repository\ActorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/actor', name: "actor_")]
class ActorController extends AbstractController
{
    #[Route('/', name: "index")]
    public function index(ActorRepository $actorRepository) : Response
    {

        $actors = $actorRepository->findAll();

        return $this->render('actor/index.html.twig', [
            'actors' => $actors
        ]);
    }

    #[Route('/{actor}', name: 'show', requirements: ['id'=>'\d+'], methods: ['GET'])]
    public function show(Actor $actor): Response
    {
        $programs = $actor->getPrograms();

        return $this->render('actor/show.html.twig', [
            'actor' => $actor,
            'programs' => $programs,
        ]);
    }
}