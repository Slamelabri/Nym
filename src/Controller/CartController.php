<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cart')]
class CartController extends AbstractController
{
    private CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Affiche le panier
     */
    #[Route('', name: 'app_cart_index')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        $cart = $this->cartService->getCurrentCart();
        $errors = [];

        if ($cart && !$cart->isEmpty()) {
            $errors = $this->cartService->validateCart();
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'errors' => $errors,
            'totalItems' => $this->cartService->getTotalItems(),
            'totalPrice' => $this->cartService->getTotalPrice(),
        ]);
    }

    /**
     * Ajoute un produit au panier
     */
    #[Route('/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function add(Product $product, Request $request): Response
    {
        // Vérifier que le produit est actif
        if (!$product->isActive()) {
            $this->addFlash('error', 'Ce produit n\'est plus disponible.');
            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        }

        // Récupérer la quantité depuis le formulaire
        $quantity = (int) $request->request->get('quantity', 1);
        
        if ($quantity <= 0) {
            $quantity = 1;
        }

        // Ajouter au panier
        $success = $this->cartService->addProduct($product, $quantity);

        if ($success) {
            $this->addFlash('success', sprintf(
                '%d × "%s" ajouté%s au panier !',
                $quantity,
                $product->getName(),
                $quantity > 1 ? 's' : ''
            ));
        } else {
            $this->addFlash('error', 'Impossible d\'ajouter ce produit au panier (stock insuffisant).');
        }

        // Rediriger selon le referer
        $referer = $request->headers->get('referer');
        if ($referer && str_contains($referer, 'product')) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_cart_index');
    }

    /**
     * Ajoute un produit au panier en AJAX
     */
    #[Route('/add-ajax/{id}', name: 'app_cart_add_ajax', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addAjax(Product $product, Request $request): JsonResponse
    {
        if (!$product->isActive()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Ce produit n\'est plus disponible.'
            ], 400);
        }

        $quantity = (int) $request->request->get('quantity', 1);
        
        if ($quantity <= 0) {
            $quantity = 1;
        }

        $success = $this->cartService->addProduct($product, $quantity);

        if ($success) {
            return new JsonResponse([
                'success' => true,
                'message' => sprintf('%d × "%s" ajouté au panier !', $quantity, $product->getName()),
                'cartCount' => $this->cartService->getTotalItems(),
                'cartTotal' => number_format($this->cartService->getTotalPrice(), 2, ',', ' ') . ' €'
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'message' => 'Stock insuffisant pour cette quantité.'
        ], 400);
    }

    /**
     * Met à jour la quantité d'un produit
     */
    #[Route('/update/{id}', name: 'app_cart_update', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function update(Product $product, Request $request): Response
    {
        $quantity = (int) $request->request->get('quantity', 1);
        
        $success = $this->cartService->updateQuantity($product, $quantity);

        if ($success) {
            if ($quantity > 0) {
                $this->addFlash('success', 'Quantité mise à jour !');
            } else {
                $this->addFlash('success', 'Produit supprimé du panier !');
            }
        } else {
            $this->addFlash('error', 'Impossible de mettre à jour la quantité.');
        }

        return $this->redirectToRoute('app_cart_index');
    }

    /**
     * Met à jour la quantité en AJAX
     */
    #[Route('/update-ajax/{id}', name: 'app_cart_update_ajax', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function updateAjax(Product $product, Request $request): JsonResponse
    {
        $quantity = (int) $request->request->get('quantity', 1);
        
        $success = $this->cartService->updateQuantity($product, $quantity);

        if ($success) {
            return new JsonResponse([
                'success' => true,
                'message' => $quantity > 0 ? 'Quantité mise à jour !' : 'Produit supprimé !',
                'cartCount' => $this->cartService->getTotalItems(),
                'cartTotal' => number_format($this->cartService->getTotalPrice(), 2, ',', ' ') . ' €',
                'itemTotal' => $quantity > 0 ? number_format((float)$product->getPrice() * $quantity, 2, ',', ' ') . ' €' : '0,00 €'
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'message' => 'Impossible de mettre à jour la quantité.'
        ], 400);
    }

    /**
     * Supprime un produit du panier
     */
    #[Route('/remove/{id}', name: 'app_cart_remove', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function remove(Product $product): Response
    {
        $success = $this->cartService->removeProduct($product);

        if ($success) {
            $this->addFlash('success', sprintf('"%s" supprimé du panier !', $product->getName()));
        } else {
            $this->addFlash('error', 'Impossible de supprimer ce produit.');
        }

        return $this->redirectToRoute('app_cart_index');
    }

    /**
     * Supprime un produit en AJAX
     */
    #[Route('/remove-ajax/{id}', name: 'app_cart_remove_ajax', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function removeAjax(Product $product): JsonResponse
    {
        $success = $this->cartService->removeProduct($product);

        if ($success) {
            return new JsonResponse([
                'success' => true,
                'message' => sprintf('"%s" supprimé du panier !', $product->getName()),
                'cartCount' => $this->cartService->getTotalItems(),
                'cartTotal' => number_format($this->cartService->getTotalPrice(), 2, ',', ' ') . ' €'
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'message' => 'Impossible de supprimer ce produit.'
        ], 400);
    }

    /**
     * Vide complètement le panier
     */
    #[Route('/clear', name: 'app_cart_clear', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function clear(): Response
    {
        $success = $this->cartService->clearCart();

        if ($success) {
            $this->addFlash('success', 'Panier vidé !');
        } else {
            $this->addFlash('error', 'Impossible de vider le panier.');
        }

        return $this->redirectToRoute('app_cart_index');
    }

    /**
     * Récupère les informations du panier en AJAX
     */
    #[Route('/info', name: 'app_cart_info', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function info(): JsonResponse
    {
        return new JsonResponse([
            'cartCount' => $this->cartService->getTotalItems(),
            'cartTotal' => number_format($this->cartService->getTotalPrice(), 2, ',', ' ') . ' €',
            'isEmpty' => $this->cartService->getTotalItems() === 0
        ]);
    }

    /**
     * Page de confirmation avant commande
     */
    #[Route('/checkout', name: 'app_cart_checkout')]
    #[IsGranted('ROLE_USER')]
    public function checkout(): Response
    {
        $cart = $this->cartService->getCurrentCart();

        if (!$cart || $cart->isEmpty()) {
            $this->addFlash('error', 'Votre panier est vide !');
            return $this->redirectToRoute('app_products');
        }

        // Valider le panier
        $errors = $this->cartService->validateCart();

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->addFlash('error', $error);
            }
            return $this->redirectToRoute('app_cart_index');
        }

        return $this->render('cart/checkout.html.twig', [
            'cart' => $cart,
            'totalItems' => $this->cartService->getTotalItems(),
            'totalPrice' => $this->cartService->getTotalPrice(),
        ]);
    }
}