<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use App\Repository\EpisodeRepository;
use App\Repository\ProgramRepository;
use App\Repository\SeasonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Program;
use App\Entity\Season;
use App\Entity\Episode;
use App\Form\ProgramType;
use Symfony\Component\HttpFoundation\Request;


#[Route("/program", name: "program_")]
class ProgramController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ProgramRepository $programRepository): Response
    {

        $programs = $programRepository->findAll();

        return $this->render('program/index.html.twig', [
            'programs' => $programs,
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, ProgramRepository $programRepository): Response
    {
        $program = new Program();
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $programRepository->save($program, true);

            return $this->redirectToRoute('program_index');
        }
        return $this->render('program/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{program}', name: 'show', requirements: ['id'=>'\d+'], methods: ['GET'])]
    public function show(Program $program, SeasonRepository $seasonRepository): Response
    {
        //$program = $programRepository->findOneBy(['id' => $id]);
        $seasons = $seasonRepository->findBy(['program' => $program->getId()]);

        /*if (!$program) {
            throw $this->createNotFoundException(
                'No program with id : ' . $id . ' found in program\'s table.'
            );
        }*/
        return $this->render('program/show.html.twig', [
            'program' => $program,
            'seasons' => $seasons,
        ]);
    }
    #[Route('/{program}/season/{season}', name: 'season_show')]
    public function showSeason(Program $program, Season $season, EpisodeRepository $episodeRepository) : Response
    {
        //$program = $programRepository->findOneBy(['id' => $programId]);
        //$season = $seasonRepository->findOneBy(['id' => $seasonId]);
        $episodes = $episodeRepository->findBy(['season' => $season->getId()]);
        return $this->render('program/season_show.html.twig',[
            'program' => $program,
            'season' => $season,
            'episodes' => $episodes,
        ]);
    }
#[Route('/{program}/season/{season}/episode/{episode}', name:'episode_show')]
    public function showEpisode(Program $program, Season $season, Episode $episode) : Response
    {
        return $this->render('program/episode_show.html.twig', [
            'program' => $program,
            'season' => $season,
            'episode' => $episode,
        ]);
    }
}