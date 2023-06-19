<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use App\Form\CommentType;
use App\Form\SearchProgramType;
use App\Form\SeasonType;
use App\Repository\CommentRepository;
use App\Repository\EpisodeRepository;
use App\Repository\ProgramRepository;
use App\Repository\SeasonRepository;
use App\Repository\UserRepository;
use App\Service\ProgramDuration;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Program;
use App\Entity\Season;
use App\Entity\Episode;
use App\Entity\Comment;
use App\Form\ProgramType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route("/program", name: "program_")]
class ProgramController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ProgramRepository $programRepository, RequestStack $requestStack, Request $request): Response
    {
        $form = $this->createForm(SearchProgramType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $search = $form->getData()['Recherche'];
            $programs = $programRepository->findBy(['title' => $search]);
        } else {
            $programs = $programRepository->findAll();
        }

        $session = $requestStack->getSession();
        if (!$session->has('total')) {
            $session->set('total', 0);
        }
        $total = $session->get('total');

        return $this->render('program/index.html.twig', [
            'programs' => $programs,
            'form'=> $form,
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/new', name: 'new')]
    public function new(Request $request, MailerInterface $mailer, ProgramRepository $programRepository, SluggerInterface $slugger): Response
    {
        $program = new Program();

        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $slug = $slugger->slug($program->getTitle());
            $program->setSlug($slug);

            $program->setOwner($this->getUser());

            $programRepository->save($program, true);

            $email = (new Email())
                ->from($this->getParameter('mailer_from'))
                ->to('your_email@example.com')
                ->subject('Une nouvelle série vient d\'être publiée !')
                ->html($this->renderView('Program/newProgramEmail.html.twig', ['program' => $program]));

            $mailer->send($email);

            $this->addFlash('success', 'La série a été ajoutée avec succès.');

            return $this->redirectToRoute('program_index');
        }
        return $this->render('program/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{slug}', name: 'show', methods: ['GET'])]
    #[ParamConverter('program', options: ['mapping' => ['slug' => 'slug']])]
    public function show(Program $program, ProgramDuration $programDuration): Response
    {
        //$program = $programRepository->findOneBy(['id' => $id]);
        //$seasons = $seasonRepository->findBy(['program' => $program->getId()]);
        $seasons = $program->getSeasons();
        /*if (!$id) {
            throw $this->createNotFoundException(
                'No program with id : ' . $id . ' found in program\'s table.'
            );
        }*/
        return $this->render('program/show.html.twig', [
            'program' => $program,
            'seasons' => $seasons,
            'programDuration' => $programDuration->calculate($program),
        ]);
    }
    #[Route('/{slug}/season/{season}', name: 'season_show')]
    #[ParamConverter('program', options: ['mapping' => ['slug' => 'slug']])]
    public function showSeason(Program $program, Season $season) : Response
    {
        //$program = $programRepository->findOneBy(['id' => $programId]);
        //$season = $seasonRepository->findOneBy(['id' => $seasonId]);
        //$episodes = $episodeRepository->findBy(['season' => $season->getId()]);
        $episodes = $season->getEpisodes();

        return $this->render('program/season_show.html.twig',[
            'program' => $program,
            'season' => $season,
            'episodes' => $episodes,
        ]);
    }
#[Route('/{slug_program}/season/{season}/episode/{slug_episode}', name:'episode_show')]
#[ParamConverter('program', options: ['mapping' => ['slug_program' => 'slug']])]
#[ParamConverter('episode', options: ['mapping' => ['slug_episode' => 'slug']])]
    public function showEpisode(Program $program, Season $season, Episode $episode, Request $request, CommentRepository $commentRepository) : Response
    {
        $comment = new Comment;

        $commentForm = $this->createForm(CommentType::class, $comment);

        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {

            $comment->setAuthor($this->getUser());
            $comment->setEpisode($episode);
            $commentRepository->save($comment, true);

            $this->addFlash('success', 'Le commentaire a été ajouté avec succès.');

            return $this->redirectToRoute('program_episode_show', [
                'slug_program' => $program->getSlug(),
                'season' => $season->getId(),
                'slug_episode' => $episode->getSlug(),
                ]);
        }
        $comments = $commentRepository->findBy([], ['id' => 'DESC']);

        return $this->render('program/episode_show.html.twig', [
            'program' => $program,
            'season' => $season,
            'episode' => $episode,
            'commentForm' => $commentForm,
            'comments' => $comments,
        ]);
    }

    #[Route('/{slug}/edit', name: 'edit', methods: ['GET', 'POST'])]
    #[ParamConverter('program', options: ['mapping' => ['slug' => 'slug']])]
    public function edit(Request $request, Program $program, ProgramRepository $programRepository, SluggerInterface $slugger): Response
    {
        if($this->getUser() !== $program->getOwner()) {
            throw $this->createAccessDeniedException('Only the owner can edit the program!');
        }

        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $slug = $slugger->slug($program->getTitle());
            $program->setSlug($slug);

            $programRepository->save($program, true);

            $this->addFlash('success', 'La série a été mise à jour avec succès.');

            return $this->redirectToRoute('program_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('program/edit.html.twig', [
            'program' => $program,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Program $program, ProgramRepository $programRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$program->getId(), $request->request->get('_token'))) {
            $programRepository->remove($program, true);

            $this->addFlash('danger', 'La série a été supprimée avec succès.');
        }

        return $this->redirectToRoute('program_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/watchlist', methods: ['GET', 'POST'], name: 'watchlist')]
    public function addToWatchlist(Program $program, UserRepository $userRepository): Response
    {
        if (!$program) {
            throw $this->createNotFoundException(
                'No program with this id found in program\'s table.'
            );
        }

        /** @var \App\Entity\User */
        $user = $this->getUser();
        if ($user->isInWatchlist($program)) {
            $user->removeFromWatchlist($program);
        } else {
            $user->addToWatchlist($program);
        }
        $userRepository->save($user, true);

        return $this->redirectToRoute('program_show', [
            'slug' => $program->getSlug()
        ], Response::HTTP_SEE_OTHER);
    }
}