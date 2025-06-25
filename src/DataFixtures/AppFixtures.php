<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Créer des utilisateurs CLIENT
        $client1 = new User();
        $client1->setEmail('client@example.com');
        $client1->setFirstName('Jean');
        $client1->setLastName('Dupont');
        $client1->setRoles(['ROLE_CLIENT']);
        $client1->setIsVerified(true);
        $client1->setCreatedAt(new \DateTimeImmutable());
        $client1->setPassword($this->passwordHasher->hashPassword($client1, 'password123'));
        $manager->persist($client1);

        $client2 = new User();
        $client2->setEmail('marie@example.com');
        $client2->setFirstName('Marie');
        $client2->setLastName('Martin');
        $client2->setRoles(['ROLE_CLIENT']);
        $client2->setIsVerified(true);
        $client2->setCreatedAt(new \DateTimeImmutable());
        $client2->setPassword($this->passwordHasher->hashPassword($client2, 'password123'));
        $manager->persist($client2);

        // Créer des utilisateurs PRO
        $pro1 = new User();
        $pro1->setEmail('pro@techstore.com');
        $pro1->setFirstName('Pierre');
        $pro1->setLastName('Vendeur');
        $pro1->setRoles(['ROLE_PRO']);
        $pro1->setIsVerified(true);
        $pro1->setCreatedAt(new \DateTimeImmutable());
        $pro1->setCompanyName('TechStore SARL');
        $pro1->setPassword($this->passwordHasher->hashPassword($pro1, 'password123'));
        $manager->persist($pro1);

        $pro2 = new User();
        $pro2->setEmail('contact@fashionboutique.fr');
        $pro2->setFirstName('Sophie');
        $pro2->setLastName('Commerce');
        $pro2->setRoles(['ROLE_PRO']);
        $pro2->setIsVerified(true);
        $pro2->setCreatedAt(new \DateTimeImmutable());
        $pro2->setCompanyName('Fashion Boutique');
        $pro2->setPassword($this->passwordHasher->hashPassword($pro2, 'password123'));
        $manager->persist($pro2);

        // Créer un admin
        $admin = new User();
        $admin->setEmail('admin@nym.com');
        $admin->setFirstName('Admin');
        $admin->setLastName('System');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsVerified(true);
        $admin->setCreatedAt(new \DateTimeImmutable());
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // Créer des catégories
        $categoryElectronics = new Category();
        $categoryElectronics->setName('Électronique');
        $categoryElectronics->setDescription('Appareils électroniques et high-tech');
        $categoryElectronics->setSlug('electronique');
        $categoryElectronics->setIsActive(true);
        $manager->persist($categoryElectronics);

        $categoryClothing = new Category();
        $categoryClothing->setName('Vêtements');
        $categoryClothing->setDescription('Mode et vêtements pour tous');
        $categoryClothing->setSlug('vetements');
        $categoryClothing->setIsActive(true);
        $manager->persist($categoryClothing);

        $categoryBooks = new Category();
        $categoryBooks->setName('Livres');
        $categoryBooks->setDescription('Livres et littérature');
        $categoryBooks->setSlug('livres');
        $categoryBooks->setIsActive(true);
        $manager->persist($categoryBooks);

        $categoryHome = new Category();
        $categoryHome->setName('Maison & Jardin');
        $categoryHome->setDescription('Tout pour la maison et le jardin');
        $categoryHome->setSlug('maison-jardin');
        $categoryHome->setIsActive(true);
        $manager->persist($categoryHome);

        // Créer des produits
        // Produits TechStore
        $laptop = new Product();
        $laptop->setName('MacBook Pro 14"');
        $laptop->setDescription('Ordinateur portable haute performance avec puce M3');
        $laptop->setPrice('2499.99');
        $laptop->setStock(15);
        $laptop->setIsActive(true);
        $laptop->setCreatedAt(new \DateTimeImmutable());
        $laptop->setSeller($pro1);
        $laptop->addCategory($categoryElectronics);
        $manager->persist($laptop);

        $smartphone = new Product();
        $smartphone->setName('iPhone 15 Pro');
        $smartphone->setDescription('Smartphone dernière génération avec caméra Pro');
        $smartphone->setPrice('1229.00');
        $smartphone->setStock(25);
        $smartphone->setIsActive(true);
        $smartphone->setCreatedAt(new \DateTimeImmutable());
        $smartphone->setSeller($pro1);
        $smartphone->addCategory($categoryElectronics);
        $manager->persist($smartphone);

        $headphones = new Product();
        $headphones->setName('AirPods Pro 2');
        $headphones->setDescription('Écouteurs sans fil avec réduction de bruit active');
        $headphones->setPrice('279.00');
        $headphones->setStock(40);
        $headphones->setIsActive(true);
        $headphones->setCreatedAt(new \DateTimeImmutable());
        $headphones->setSeller($pro1);
        $headphones->addCategory($categoryElectronics);
        $manager->persist($headphones);

        // Produits Fashion Boutique
        $dress = new Product();
        $dress->setName('Robe d\'été fleurie');
        $dress->setDescription('Robe légère parfaite pour l\'été, motifs floraux');
        $dress->setPrice('79.99');
        $dress->setStock(30);
        $dress->setIsActive(true);
        $dress->setCreatedAt(new \DateTimeImmutable());
        $dress->setSeller($pro2);
        $dress->addCategory($categoryClothing);
        $manager->persist($dress);

        $jeans = new Product();
        $jeans->setName('Jean slim noir');
        $jeans->setDescription('Jean coupe slim, couleur noir délavé');
        $jeans->setPrice('89.90');
        $jeans->setStock(20);
        $jeans->setIsActive(true);
        $jeans->setCreatedAt(new \DateTimeImmutable());
        $jeans->setSeller($pro2);
        $jeans->addCategory($categoryClothing);
        $manager->persist($jeans);

        $sweater = new Product();
        $sweater->setName('Pull en laine mérinos');
        $sweater->setDescription('Pull chaud et confortable, 100% laine mérinos');
        $sweater->setPrice('129.00');
        $sweater->setStock(15);
        $sweater->setIsActive(true);
        $sweater->setCreatedAt(new \DateTimeImmutable());
        $sweater->setSeller($pro2);
        $sweater->addCategory($categoryClothing);
        $manager->persist($sweater);

        // Quelques produits supplémentaires
        $book = new Product();
        $book->setName('Guide du développeur Symfony');
        $book->setDescription('Livre complet pour apprendre Symfony 7');
        $book->setPrice('39.99');
        $book->setStock(50);
        $book->setIsActive(true);
        $book->setCreatedAt(new \DateTimeImmutable());
        $book->setSeller($pro1);
        $book->addCategory($categoryBooks);
        $manager->persist($book);

        $plant = new Product();
        $plant->setName('Plante verte Monstera');
        $plant->setDescription('Belle plante d\'intérieur, facile d\'entretien');
        $plant->setPrice('24.99');
        $plant->setStock(12);
        $plant->setIsActive(true);
        $plant->setCreatedAt(new \DateTimeImmutable());
        $plant->setSeller($pro2);
        $plant->addCategory($categoryHome);
        $manager->persist($plant);

        $manager->flush();
    }
}