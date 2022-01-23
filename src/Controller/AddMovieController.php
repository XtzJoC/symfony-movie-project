<?php

namespace App\Controller;

use App\Service\OMBdAPIConnector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\MovieVote;
use App\Entity\Movie;

class AddMovieController extends AbstractController
{
    #[Route('/add-movie', name: 'add_movie')]
    public function addMovie(Request $request, OMBdAPIConnector $ombdAPIConnector, ManagerRegistry $doctrine): Response
    {
        $defaultData = ['name' => '', 'vote' => 5, 'email' => 'yonk@gmail.com', 'image' => NULL];

        $form = $this->createFormBuilder($defaultData)
            ->add('name', TextType::class)
            ->add('vote', IntegerType::class, ['attr' => ['min' => 1, 'max' => 10]])
            ->add('email', EmailType::class)
            ->add('image', FileType::class, [
                'label' => 'Image du film',
                'attr' => [
                    'accept' => 'image/png, image/jpeg',
                ],
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png,'
                        ],
                        'mimeTypesMessage' => 'Merci d\'uploader une image',
                    ])
                ],
            ])
            ->add('submit', SubmitType::class)
            ->getForm();


        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $movieVote = $form->getData();

            $apiMovie = $ombdAPIConnector->getMovieFromAPI($movieVote['name']);
            
            if($apiMovie){
                $movieName = $apiMovie['name'];
                $movieDescription = $apiMovie['description'];
                $movieImgURL = $apiMovie['imgURL'];
    
                $entityManager = $doctrine->getManager();
                $movie = $entityManager->getRepository(Movie::class)->findByName($movieName);
    
                if($movie == NULL){
                    // Le film n'existe pas dans la base de données
                    $movie = new Movie();
                    $movie->setName($movieName);
                    $movie->setDescription($movieDescription);
                    $movie->setScore($movieVote['vote']);
                    $movie->setNbVotes(1);

                    $entityManager->persist($movie);
                    $entityManager->flush();

                    // Une image custom à été renséignée
                    if($movieVote['image']){
                        $movieImgURL = $movieVote['image']->getPathname();
                    }

                    var_dump($movieImgURL);

                    // Sauvegarde de l'image du film
                    $file_name = 'movie_imgs/'.$movieName.'.png';
                    file_put_contents($file_name, file_get_contents($movieImgURL));
                }else{
                    // Le film éxiste dans la base de données
                    $average = $movie->getScore();
                    $size = $movie->getNbVotes();
                    $value = $movieVote['vote'];

                    $newScore = ($size * $average + $value) / ($size + 1);

                    $movie->setScore($newScore);
                    $entityManager->flush();
                }
    
                //return $this->redirectToRoute('home_page');
            }
        }

        return $this->renderForm('add_movie/index.html.twig', [
            'form' => $form,
        ]);
    }
}
