<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): Response {
        // Si l'utilisateur est déjà connecté, rediriger
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $error = null;

        if ($request->isMethod('POST')) {
            $email = trim($request->request->get('email'));
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');
            $firstName = trim($request->request->get('first_name'));
            $lastName = trim($request->request->get('last_name'));
            $accountType = $request->request->get('account_type');
            $companyName = trim($request->request->get('company_name'));

            // Validation des données
            $validationErrors = $this->validateRegistrationData(
                $email, 
                $password, 
                $confirmPassword, 
                $firstName, 
                $lastName, 
                $accountType, 
                $companyName,
                $entityManager
            );

            if (!empty($validationErrors)) {
                $error = implode('<br>', $validationErrors);
            } else {
                try {
                    // Créer l'utilisateur
                    $user->setEmail($email);
                    $user->setFirstname($firstName);
                    $user->setLastname($lastName);
                    $user->setIsVerified(true); // Pour simplifier, on considère l'email vérifié
                    $user->setCreatedAt(new \DateTimeImmutable()); // ✅ AJOUTÉ

                    // Définir le rôle selon le type de compte
                    if ($accountType === 'pro') {
                        $user->setRoles(['ROLE_PRO']);
                        if (!empty($companyName)) {
                            $user->setCompanyName($companyName);
                        }
                    } else {
                        $user->setRoles(['ROLE_CLIENT']);
                    }

                    // Hasher le mot de passe
                    $user->setPassword(
                        $userPasswordHasher->hashPassword($user, $password)
                    );

                    // Validation avec les contraintes de l'entité
                    $violations = $validator->validate($user);
                    if (count($violations) > 0) {
                        $error = $violations[0]->getMessage();
                    } else {
                        $entityManager->persist($user);
                        $entityManager->flush();

                        // Rediriger vers la page de connexion avec un message de succès
                        $this->addFlash('success', 'Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.');
                        
                        // ✅ CORRIGÉ: Redirection compatible Turbo avec statut 303
                        return $this->redirectToRoute('app_login', [], 303);
                    }
                } catch (\Exception $e) {
                    $error = 'Une erreur est survenue lors de la création du compte. Veuillez réessayer.';
                    // Log l'erreur pour le debug
                    // $this->logger->error('Registration error: ' . $e->getMessage());
                }
            }
        }

        return $this->render('auth/register.html.twig', [
            'user' => $user,
            'error' => $error,
        ]);
    }

    /**
     * Valide les données d'inscription
     */
    private function validateRegistrationData(
        ?string $email,
        ?string $password,
        ?string $confirmPassword,
        ?string $firstName,
        ?string $lastName,
        ?string $accountType,
        ?string $companyName,
        EntityManagerInterface $entityManager
    ): array {
        $errors = [];

        // Validation des champs obligatoires
        if (empty($email)) {
            $errors[] = 'L\'adresse email est obligatoire.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'adresse email n\'est pas valide.';
        }

        if (empty($password)) {
            $errors[] = 'Le mot de passe est obligatoire.';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        }

        if (empty($firstName)) {
            $errors[] = 'Le prénom est obligatoire.';
        } elseif (strlen($firstName) < 2) {
            $errors[] = 'Le prénom doit contenir au moins 2 caractères.';
        }

        if (empty($lastName)) {
            $errors[] = 'Le nom est obligatoire.';
        } elseif (strlen($lastName) < 2) {
            $errors[] = 'Le nom doit contenir au moins 2 caractères.';
        }

        // Validation du type de compte
        if (!in_array($accountType, ['client', 'pro'])) {
            $errors[] = 'Le type de compte sélectionné n\'est pas valide.';
        }

        // Validation du nom d'entreprise pour les pros
        if ($accountType === 'pro' && empty($companyName)) {
            $errors[] = 'Le nom de l\'entreprise est obligatoire pour un compte professionnel.';
        }

        // Vérifier si l'email existe déjà
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($existingUser) {
                $errors[] = 'Cette adresse email est déjà utilisée.';
            }
        }

        return $errors;
    }
}