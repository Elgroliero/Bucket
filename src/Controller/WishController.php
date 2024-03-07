<?php

namespace App\Controller;

use App\Entity\Wish;
use App\Form\WishType;
use App\Helper\Censurator;
use App\Repository\WishRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route(path: 'wish/', name: 'wish_')]
class WishController extends AbstractController
{
    #[Route(path: '', name: 'list', methods: ['GET'])]
    public function list(WishRepository $wishRepo, Request $request): Response
    {
        $maxPerPage = 10;
        if (($page = $request->get('p', 1)) < 1) {
            return $this->redirectToRoute('wish_list');
        }
        $total = count($wishRepo->getAllPublishedWishes());
        $wishes = $wishRepo->findPublishedWishesWithCategories($page, $maxPerPage);
        if ($page !== 1 && empty($wishes)) {
            return $this->redirectToRoute('wish_list');
        }
        return $this->render('wish/list.html.twig', compact('wishes', 'total', 'maxPerPage'));
    }
// $elementsByPage, $elementsByPage*($page-1)
    //ex : http://127.0.0.1:8001/detail/12
    /*    #[Route(path: '{id}', name: 'details', requirements: ["id" => "[1-9]\d*"], methods: ['GET'])]
        public function details(?Wish $wish): Response
        {
            if (!$wish || !$wish->isPublished()) {
                throw $this->createNotFoundException('no published wish found for id ' . $wish->getId());
            }
            return $this->render('wish/details.html.twig', compact('wish'));
        }*/
    #[Route(path: '{id}', name: 'details', requirements: ["id" => "[1-9]\d*"], methods: ['GET'])]
    public function details(WishRepository $wishRepo, Request $request): Response
    {
        $wish = $wishRepo->getWishById($request->get('id'));
        if (!$wish || !$wish->isPublished()) {
            throw $this->createNotFoundException('no published wish found for id ' . $wish->getId());
        }
        return $this->render('wish/detail.html.twig', compact('wish'));
    }

    #[Route(path: "create", name: 'create', methods: ['GET', 'POST'])]
    public function form(Censurator $censurator, EntityManagerInterface $entMana, Request $request, SluggerInterface $slugger): Response
    {
        $wish = new Wish();
        $wishForm = $this->createForm(WishType::class, $wish);
        $wishForm->handleRequest($request);

        if ($wishForm->isSubmitted() && $wishForm->isValid()) {

            $wish->setisPublished(true);
            $titreCensure = $censurator->purify($wish->getTitle());
            $descriptionCensure = $censurator->purify($wish->getDescription());
            $wish->setTitle($titreCensure);
            $wish->setDescription($descriptionCensure);
            $entMana->persist($wish);
            $entMana->flush();
            $this->addFlash('success', 'Le wish a été créé avec succès !');
            return $this->redirectToRoute('wish_details', ['id' => $wish->getId()]);
        }
        return $this->render('wish/create.html.twig', ['wishForm' => $wishForm]);
    }

    #[Route(path: "/update/{id}", name: 'update', methods: ['GET', 'POST'])]
    public function update(Censurator $censurator, EntityManagerInterface $entityManager, Request $request, int $id): Response
    {
        $wish = $entityManager->getRepository(Wish::class)->find($id);
        if (!$wish) {
            throw $this->createNotFoundException('No published wish found for id ' . $id);
        }
        $wishForm = $this->createForm(WishType::class, $wish);
        $wishForm->handleRequest($request);
        if ($wishForm->isSubmitted() && $wishForm->isValid()) {
            // Récupérer les données soumises par le formulaire
            $formData = $wishForm->getData();
            // Modifier les attributs du souhait seulement si les données sont définies et non vides
            if (!empty($formData->getTitle())) {
                $titreCensure = $censurator->purify($formData->getTitle());
                $wish->setTitle($titreCensure);
            }
            if (!empty($formData->getDescription())) {
                $descriptionCensure = $censurator->purify($formData->getDescription());
                $wish->setDescription($descriptionCensure);
            }
            if (!empty($formData->getAuthor())) {
                $wish->setAuthor($formData->getAuthor());
            }
            $entityManager->flush();
            $this->addFlash('success', 'Le wish a été mis à jour avec succès !');
            return $this->redirectToRoute('wish_list');
        }
        return $this->render('wish/update.html.twig', [
            'wish' => $wish,
            'wishForm' => $wishForm->createView()
        ]);
    }

    #[Route(path: "{id}", name: 'delete', requirements: ["id" => "[1-9]\d*"], methods: ['POST'])]
    public function delete(EntityManagerInterface $entMana, Request $request): Response
    {
        $wish = $entMana->getRepository(Wish::class)->find($request->get('id'));
        $entMana->remove($wish);
        $entMana->flush();
        $this->addFlash('danger', 'Le wish a été supprimé avec succès !');
        return $this->redirectToRoute('wish_list');
    }
}


//
//namespace App\Controller;
//
//use App\Entity\Wish;
//use App\Repository\WishRepository;
//use Doctrine\ORM\EntityManagerInterface;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\Routing\Attribute\Route;
//
//class WishController extends AbstractController
//{
//    #[Route('/wish', name: 'wish', methods: ['GET'])]
//    #[Route('/wish_list', name: 'wish_list', methods: ['GET'])]
//    public function list(WishRepository $wishRepository, Request $request): Response
//    {
//        // Pagination
//        $elementsByPage = 5;
//        if (($page = $request->get('p', 1)) < 1) {
//            return $this->redirectToRoute('wish_list');
//        }
//
//        // Nombre d'éléments
//        $total = count($wishRepository->getAllPublishedWishes());
//
//        $wishes = $wishRepository->getAllPublishedWishes();
//        $wishes = array_slice($wishes, ($page - 1) * $elementsByPage, $elementsByPage);
//
//        return $this->render('wish/list.html.twig', compact('wishes', 'total', 'elementsByPage'));
//    }
//
////    #[Route(path: '/wish_detail/{id}', name: 'wish_detail', requirements: ['id' => '[1-9]\d*'], methods: ['GET'])]
////    public function detail(?Wish $wish): Response
////    {
////        if (!$wish || !$wish->isPublished()) {
////            throw $this->createNotFoundException('Wish not found !');
////        }
////        return $this->render('wish/detail.html.twig', compact('wish'));
////    }
//
//    #[Route(path: '/wish_detail/{id}', name: 'wish_detail', requirements: ['id' => '[1-9]\d*'], methods: ['GET'])]
//    public function detail(WishRepository $wishRepository, Request $request): Response
//    {
//        $wish = $wishRepository->getWishById($request->get('id'));
//
//        if (!$wish || !$wish->isPublished()) {
//            throw $this->createNotFoundException('Wish not found !');
//        }
//        return $this->render('wish/detail.html.twig', compact('wish'));
//    }
//
////    public function detail(EntityManagerInterface $entityManager, Request $request): Response
////
////    {
////        $wish = $entityManager->getRepository(Wish::class)->find($request->get('id'));
////
////        if ($wish === null) {
////            throw $this->createNotFoundException();
////        }
////
////        if ($wish->isPublished() === false) {
////            throw $this->createNotFoundException('Wish not found');
////        }
////
////        return $this->render('wish/detail.html.twig', compact('wish'));
////    }
//
////    #[Route(path: "insert", name: "insert", methods: ["GET"])]
////    public function insert(EntityManagerInterface $entityManager): Response
////    {
////        $wish = new Wish("Voler", "Voler dans les airs", "Toto", true, new \DateTime('now'));
////        $entityManager->persist($wish);
////        $entityManager->flush();
////        return $this->redirectToRoute('wish_list');
////    }
//
//    #[Route(path: "delete/{id}", name: "delete", requirements: ['id' => '\d+'], methods: ["GET"])]
//    public function delete(EntityManagerInterface $entityManager, Request $request): Response
//    {
//        $wish = $entityManager->getRepository(Wish::class)->find($request->get('id'));
//        $entityManager->remove($wish);
//        $entityManager->flush();
//        return $this->redirectToRoute('wish_list');
//    }
//
//}
