<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use App\Form\SearchProgramType;
use App\Repository\ProgramRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(ProgramRepository $programRepository, Request $request): Response
    {

        $form = $this->createForm(SearchProgramType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $search = $form->getData()['Recherche'];
            $programs = $programRepository->findLikeNameAndActor($search);
        } else {
            $programs = $programRepository->findBy([], ['id' => 'DESC']);
        }

        return $this->render('index.html.twig', [
            'programs' => $programs,
            'form' => $form,
        ]);
    }
}