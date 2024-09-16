<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\Projet;
use App\Form\ContactType;
use App\Form\ProjetType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, EntityManagerInterface $entityManager, NotifierInterface $notifier): Response
    {
        // ON écrit la logique du code ici.
        $projet = $entityManager->getRepository(Projet::class)->findAll();

        // Déclarer une variable pour créer ton formulaire.
        $image = new Projet();
        // On accepte les requêtes.
        $form = $this->createForm(ProjetType::class, $image);
        $form->handleRequest($request);
        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // On déclare une variable imageFile puis On récupère le fichier image
            $imageFile = $form->get('image')->getData();
            // On vérifie si un fichier a été uploadé

            if ($imageFile) {
                // On génère un nouveau nom de fichier
                $newFileName = uniqid() . '.' . $imageFile->guessExtension();
                // On déplace le fichier dans le répertoire des images
                $imageFile->move($this->getParameter('image_directory'), $newFileName);
                // On met à jour l'entité avec le nom du fichier
                $form->getData()->setImage($newFileName);
            }
            // On persist
            $entityManager->persist($form->getData());
            // On push.
            $entityManager->flush();

            $this->addFlash('success', 'Projet envoyé');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            //Déclaration de la variable
            'projet' => $projet,
            'form' => $form->createView()
        ]);
    }

    // Créer l'entity
    // Créer le formulaire
    // Le traité dans ta route de ton controlleur.
    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, EntityManagerInterface $entityManager, NotifierInterface $notifier)
    {

        $contact = new Contact();
        //On accepte les requêtes
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // On persist
            $entityManager->persist($contact);
            // On push.
            $entityManager->flush();

            // Ici on va injecter ton service.

            $this->addFlash('success', 'Formulaire envoyé');

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig',
            ['controller_name' => 'ContactController',
                'contact' => $contact,
                'form' => $form->createView()
                ]);
    }


}
