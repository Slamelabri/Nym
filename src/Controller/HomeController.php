<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        // Récupérer les 8 derniers produits actifs
        $latestProducts = $productRepository->findBy(
            ['isActive' => true],
            ['createdAt' => 'DESC'],
            8
        );

        // Récupérer les 6 produits les plus chers (produits "premium")
        $premiumProducts = $productRepository->findBy(
            ['isActive' => true],
            ['price' => 'DESC'],
            6
        );

        // Récupérer toutes les catégories actives
        $categories = $categoryRepository->findBy(['isActive' => true]);

        return $this->render('home/index.html.twig', [
            'latestProducts' => $latestProducts,
            'premiumProducts' => $premiumProducts,
            'categories' => $categories,
        ]);
    }
}