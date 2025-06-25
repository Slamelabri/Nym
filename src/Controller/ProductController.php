<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Category;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/products')]
class ProductController extends AbstractController
{
    #[Route('', name: 'app_products')]
    public function index(ProductRepository $productRepository, Request $request, PaginatorInterface $paginator): Response
    {
        // Récupérer tous les produits actifs
        $queryBuilder = $productRepository->createQueryBuilder('p')
            ->where('p.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('p.createdAt', 'DESC');

        // Pagination
        $products = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            12 // 12 produits par page
        );

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'pageTitle' => 'Tous nos produits'
        ]);
    }

    #[Route('/category/{slug}', name: 'app_products_by_category')]
    public function byCategory(
        string $slug, 
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        // Trouver la catégorie par son slug
        $category = $categoryRepository->findOneBy(['slug' => $slug, 'isActive' => true]);
        
        if (!$category) {
            throw $this->createNotFoundException('Catégorie non trouvée');
        }

        // Récupérer les produits de cette catégorie
        $queryBuilder = $productRepository->createQueryBuilder('p')
            ->join('p.categories', 'c')
            ->where('c.id = :categoryId')
            ->andWhere('p.isActive = :active')
            ->setParameter('categoryId', $category->getId())
            ->setParameter('active', true)
            ->orderBy('p.createdAt', 'DESC');

        // Pagination
        $products = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'category' => $category,
            'pageTitle' => 'Catégorie: ' . $category->getName()
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', requirements: ['id' => '\d+'])]
    public function show(Product $product): Response
    {
        // Vérifier que le produit est actif - CORRECTION ICI
        if (!$product->isActive()) {
            throw $this->createNotFoundException('Produit non disponible');
        }

        // Récupérer des produits similaires (même catégories)
        $relatedProducts = [];
        if ($product->getCategories()->count() > 0) {
            $category = $product->getCategories()->first();
            $relatedProducts = $category->getProducts()
                ->filter(fn($p) => $p->getId() !== $product->getId() && $p->isActive())
                ->slice(0, 4);
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'relatedProducts' => $relatedProducts
        ]);
    }

    #[Route('/search', name: 'app_products_search')]
    public function search(
        Request $request, 
        ProductRepository $productRepository,
        PaginatorInterface $paginator
    ): Response {
        $query = $request->query->get('q', '');
        
        if (empty($query)) {
            return $this->redirectToRoute('app_products');
        }

        // Recherche dans le nom et la description
        $queryBuilder = $productRepository->createQueryBuilder('p')
            ->where('p.isActive = :active')
            ->andWhere('p.name LIKE :query OR p.description LIKE :query')
            ->setParameter('active', true)
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('p.name', 'ASC');

        // Pagination
        $products = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'pageTitle' => 'Résultats pour: "' . $query . '"',
            'searchQuery' => $query
        ]);
    }
}