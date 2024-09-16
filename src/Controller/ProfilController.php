<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\AddressType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfilController extends AbstractController
{
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/profil/adresse', name: 'app_profil')]
    public function index(Security $security, Request $request, UserRepository $userRepository,
                          EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'user connecter
        $user = $security->getUser();
        // Récupérer les adresses de l'utilisateurs
        $addresses = $user->getAddresses();

        $address = new Address();
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address->setUser($user);

            // On enregistre en bdd
            $entityManager->persist($address);
            $entityManager->flush();

            $this->addFlash('success', 'Votre adresse à bien été enregistrée');
            return $this->redirectToRoute('app_home');
        }
        return $this->render('profil/index.html.twig', [
            'controller_name' => 'ProfilController',
            'infoUser' => $userRepository->find($user->getId()),
            'addresses' => $addresses,
            'form' => $form->createView(),
        ]);
    }
}
