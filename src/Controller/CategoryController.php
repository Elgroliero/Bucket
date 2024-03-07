<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path:'/category/', name: 'category_')]
class CategoryController extends AbstractController
{

    #[Route('create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cat = new Category();
        $form = $this->createForm(CategoryType::class, $cat);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($cat);
            $entityManager->flush();
            $this->addFlash('success', 'La catégorie a été créée avec succès !');
            return $this->redirectToRoute('category_list');
        }
        return $this->render('category/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route(path: '', name: 'list', methods: ['GET'])]
    public function list(CategoryRepository $CateRepo): Response
    {
        $categories = $CateRepo->findAll();
        return $this->render('category/list.html.twig', compact('categories'));
    }

}