<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use App\Repository\EpisodeRepository;
use App\Repository\ProgramRepository;
use App\Repository\SeasonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


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

    #[Route('/{id}', name: 'show', requirements: ['id'=>'\d+'], methods: ['GET'])]
    public function show(int $id, ProgramRepository $programRepository, SeasonRepository $seasonRepository): Response
    {
        $program = $programRepository->findOneBy(['id' => $id]);
        $seasons = $seasonRepository->findBy(['program' => $program->getId()]);

        if (!$program) {
            throw $this->createNotFoundException(
                'No program with id : ' . $id . ' found in program\'s table.'
            );
        }
        return $this->render('program/show.html.twig', [
            'program' => $program,
            'seasons' => $seasons,
        ]);
    }
    #[Route('/{programId}/seasons/{seasonId}', name: 'season_show')]
    public function showSeason(int $programId, int $seasonId, ProgramRepository $programRepository, SeasonRepository $seasonRepository, EpisodeRepository $episodeRepository) : Response
    {
        $program = $programRepository->findOneBy(['id' => $programId]);
        $season = $seasonRepository->findOneBy(['id' => $seasonId]);
        $episodes = $episodeRepository->findBy(['season' => $season->getId()]);
        return $this->render('program/season_show.html.twig',[
            'program' => $program,
            'season' => $season,
            'episodes' => $episodes,
        ]);
    }
}