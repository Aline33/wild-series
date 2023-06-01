<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use App\Repository\EpisodeRepository;
use App\Repository\ProgramRepository;
use App\Repository\SeasonRepository;
use App\Service\ProgramDuration;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Program;
use App\Entity\Season;
use App\Entity\Episode;
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
    public function index(ProgramRepository $programRepository, RequestStack $requestStack): Response
    {
        $programs = $programRepository->findAll();

        $session = $requestStack->getSession();
        if (!$session->has('total')) {
            $session->set('total', 0);
        }
        $total = $session->get('total');

        return $this->render('program/index.html.twig', [
            'programs' => $programs,
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
    public function showEpisode(Program $program, Season $season, Episode $episode) : Response
    {

        return $this->render('program/episode_show.html.twig', [
            'program' => $program,
            'season' => $season,
            'episode' => $episode,
        ]);
    }
}