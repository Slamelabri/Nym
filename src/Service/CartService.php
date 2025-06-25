<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class CartService
{
    private EntityManagerInterface $entityManager;
    private CartRepository $cartRepository;
    private $security;

    public function __construct(
        EntityManagerInterface $entityManager,
        CartRepository $cartRepository,
        \Symfony\Bundle\SecurityBundle\Security $security
    ) {
        $this->entityManager = $entityManager;
        $this->cartRepository = $cartRepository;
        $this->security = $security;
    }

    /**
     * Récupère le panier de l'utilisateur connecté
     */
    public function getCurrentCart(): ?Cart
    {
        $user = $this->security->getUser();
        
        if (!$user instanceof User) {
            return null;
        }

        // Si l'utilisateur a déjà un panier, le retourner
        if ($user->getCart()) {
            return $user->getCart();
        }

        // Sinon, créer un nouveau panier
        return $this->createCartForUser($user);
    }

    /**
     * Crée un nouveau panier pour un utilisateur
     */
    private function createCartForUser(User $user): Cart
    {
        $cart = new Cart();
        $cart->setOwner($user);
        $cart->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($cart);
        $this->entityManager->flush();

        return $cart;
    }

    /**
     * Ajoute un produit au panier
     */
    public function addProduct(Product $product, int $quantity = 1): bool
    {
        $cart = $this->getCurrentCart();
        
        if (!$cart) {
            return false; // Utilisateur non connecté
        }

        // Vérifier le stock disponible
        if ($product->getStock() < $quantity) {
            return false; // Stock insuffisant
        }

        // Vérifier si le produit est déjà dans le panier
        $existingItem = $cart->findItemByProduct($product);

        if ($existingItem) {
            // Mettre à jour la quantité
            $newQuantity = $existingItem->getQuantity() + $quantity;
            
            // Vérifier si la nouvelle quantité ne dépasse pas le stock
            if ($newQuantity > $product->getStock()) {
                return false;
            }
            
            $existingItem->setQuantity($newQuantity);
        } else {
            // Créer un nouvel article
            $cartItem = new CartItem();
            $cartItem->setCart($cart);
            $cartItem->setProduct($product);
            $cartItem->setQuantity($quantity);
            $cartItem->setAddedAt(new \DateTimeImmutable());

            $cart->addCartItem($cartItem);
            $this->entityManager->persist($cartItem);
        }

        $cart->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return true;
    }

    /**
     * Met à jour la quantité d'un produit dans le panier
     */
    public function updateQuantity(Product $product, int $quantity): bool
    {
        $cart = $this->getCurrentCart();
        
        if (!$cart) {
            return false;
        }

        $cartItem = $cart->findItemByProduct($product);
        
        if (!$cartItem) {
            return false; // Produit pas dans le panier
        }

        // Si quantité = 0, supprimer l'article
        if ($quantity <= 0) {
            return $this->removeProduct($product);
        }

        // Vérifier le stock
        if ($quantity > $product->getStock()) {
            return false;
        }

        $cartItem->setQuantity($quantity);
        $cart->setUpdatedAt(new \DateTimeImmutable());
        
        $this->entityManager->flush();
        
        return true;
    }

    /**
     * Supprime un produit du panier
     */
    public function removeProduct(Product $product): bool
    {
        $cart = $this->getCurrentCart();
        
        if (!$cart) {
            return false;
        }

        $cartItem = $cart->findItemByProduct($product);
        
        if (!$cartItem) {
            return false;
        }

        $cart->removeCartItem($cartItem);
        $this->entityManager->remove($cartItem);
        
        $cart->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return true;
    }

    /**
     * Vide complètement le panier
     */
    public function clearCart(): bool
    {
        $cart = $this->getCurrentCart();
        
        if (!$cart) {
            return false;
        }

        // Supprimer tous les articles
        foreach ($cart->getCartItems() as $item) {
            $this->entityManager->remove($item);
        }
        
        $cart->getCartItems()->clear();
        $cart->setUpdatedAt(new \DateTimeImmutable());
        
        $this->entityManager->flush();

        return true;
    }

    /**
     * Récupère le nombre total d'articles dans le panier
     */
    public function getTotalItems(): int
    {
        $cart = $this->getCurrentCart();
        
        if (!$cart) {
            return 0;
        }

        return $cart->getTotalItems();
    }

    /**
     * Récupère le prix total du panier
     */
    public function getTotalPrice(): float
    {
        $cart = $this->getCurrentCart();
        
        if (!$cart) {
            return 0.0;
        }

        return $cart->getTotalPrice();
    }

    /**
     * Vérifie si un produit est dans le panier
     */
    public function hasProduct(Product $product): bool
    {
        $cart = $this->getCurrentCart();
        
        if (!$cart) {
            return false;
        }

        return $cart->findItemByProduct($product) !== null;
    }

    /**
     * Récupère la quantité d'un produit dans le panier
     */
    public function getProductQuantity(Product $product): int
    {
        $cart = $this->getCurrentCart();
        
        if (!$cart) {
            return 0;
        }

        $cartItem = $cart->findItemByProduct($product);
        
        return $cartItem ? $cartItem->getQuantity() : 0;
    }

    /**
     * Valide le panier avant commande
     */
    public function validateCart(): array
    {
        $cart = $this->getCurrentCart();
        $errors = [];

        if (!$cart || $cart->isEmpty()) {
            $errors[] = 'Le panier est vide';
            return $errors;
        }

        foreach ($cart->getCartItems() as $item) {
            $product = $item->getProduct();
            
            // Vérifier si le produit est toujours actif
            if (!$product->isActive()) {
                $errors[] = sprintf('Le produit "%s" n\'est plus disponible', $product->getName());
                continue;
            }

            // Vérifier le stock
            if ($item->getQuantity() > $product->getStock()) {
                $errors[] = sprintf(
                    'Stock insuffisant pour "%s" (demandé: %d, disponible: %d)',
                    $product->getName(),
                    $item->getQuantity(),
                    $product->getStock()
                );
            }
        }

        return $errors;
    }
}