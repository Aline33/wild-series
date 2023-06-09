<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\ProgramRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\CategoryType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


#[Route("/category", name: "category_")]
class CategoryController extends AbstractController
{
    #[Route('/', name: "index")]
    public function index(CategoryRepository $categoryRepository) : Response
    {

        $categories = $categoryRepository->findAll();

        return $this->render('category/index.html.twig', [
            'categories' => $categories
        ]);
    }

    #[isGranted('ROLE_ADMIN')]
    #[Route('/new', name: 'new')]
    public function new(Request $request, CategoryRepository $categoryRepository): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $categoryRepository->save($category, true);

            $this->addFlash('success', 'La catégorie a été ajoutée avec succès.');

            return $this->redirectToRoute('category_index');
        }

        // Render the form
        return $this->render('category/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{categoryName}', name: 'show')]
    public function show(string $categoryName, CategoryRepository $categoryRepository, ProgramRepository $programRepository) : Response
    {
        $category = $categoryRepository->findOneBy(['name' => $categoryName]);

        if (!$category) {
            throw $this->createNotFoundException(
                'No category with name : ' . $categoryName . ' found in category\'s table.'
            );
        }
        $programs = $programRepository->findBy(['category' => $category->getId()], ['id' => "DESC"], 3);

        return $this->render('category/show.html.twig', [
            'programs' => $programs,
            'category' => $category->getName(),
        ]);
    }
}