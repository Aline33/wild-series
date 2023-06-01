<?php

namespace App\Controller;

use App\Entity\Actor;
use App\Entity\Episode;
use App\Form\ActorType;
use App\Entity\Program;
use App\Form\ProgramType;
use App\Repository\ActorRepository;
use App\Repository\ProgramRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

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

    #[Route('/new', name: 'new')]
    public function new(Request $request, ActorRepository $actorRepository): Response
    {
        $actor = new Actor();

        $form = $this->createForm(ActorType::class, $actor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $actorRepository->save($actor, true);

            $this->addFlash('success', 'L\'acteur a été ajouté avec succès.');

            return $this->redirectToRoute('actor_index');
        }
        return $this->render('actor/new.html.twig', [
            'form' => $form,
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