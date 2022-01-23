<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Movie;

class ShowMovieController extends AbstractController
{
    #[Route('/show-movie/{id}', name: 'show_movie')]
    public function index(Movie $movie, Request $request, ManagerRegistry $doctrine): Response
    {
        $defaultData = ['password' => ''];

        $form = $this->createFormBuilder($defaultData)
            ->add('password', PasswordType::class)
            ->add('submit', SubmitType::class, ['label' => 'Supprimer'])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();
            $password = $data['password'];

            if($password == $this->getParameter('admin_password')){
                $entityManager = $doctrine->getManager();

                $entityManager->remove($movie);
                $entityManager->flush();

                return $this->redirectToRoute('home_page');
            }            
        }

        return $this->renderForm('show_movie/index.html.twig', [
            'movie' => $movie,
            'movie_img' => 'movie_imgs/'.$movie->getName().'.png',
            'form' => $form,
        ]);
    }
}
