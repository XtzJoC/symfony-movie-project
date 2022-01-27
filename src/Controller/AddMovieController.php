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
use App\Entity\voteData;
use App\Entity\Movie;

class AddMovieController extends AbstractController
{
    #[Route('/add-movie', name: 'add_movie')]
    public function addMovie(Request $request, OMBdAPIConnector $ombdAPIConnector, ManagerRegistry $doctrine): Response
    {
        $defaultData = ['name' => '', 'vote' => 5, 'email' => 'yonk@gmail.com'];

        $form = $this->createFormBuilder($defaultData)
            ->add('name', TextType::class)
            ->add('vote', IntegerType::class, ['attr' => ['min' => 1, 'max' => 10]])
            ->add('email', EmailType::class)
            ->add('imageFile', FileType::class, [
                'label' => 'Image du film',
                'attr' => [
                    'accept' => '.png, .jpeg, .jpg',
                ],
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                        ],
                    ])
                ],
            ])
            ->add('submit', SubmitType::class, ['label' => 'Voter'])
            ->getForm();

        $form->handleRequest($request);

        $error = NULL;

        if($form->isSubmitted() && $form->isValid()){
            $voteData = $form->getData();
            $imageFile = $form->get('imageFile')->getData();

            $movieFromAPI = $ombdAPIConnector->getMovieFromAPI($voteData['name']);

            if($movieFromAPI){
                $name = $movieFromAPI['name'];

                if($voteData['name'] != $name){
                    $error = "Le film '".$voteData['name']."' n'existe pas. Voulez-vous dire '".$name."' ?";
                }else{
                    $entityManager = $doctrine->getManager();
                    $movie = $entityManager->getRepository(Movie::class)->findByName($name);
        
                    if($movie == NULL){
                        // Le film n'existe pas dans la base de données
                        $description = $movieFromAPI['description'];
                        $imgURL = $movieFromAPI['imgURL'];
                        
                        $movie = new Movie();
                        $movie->setName($name);
                        $movie->setDescription($description);
                        $movie->setScore($voteData['vote']);
                        $movie->setNbVotes(1);

                        $entityManager->persist($movie);
                        $entityManager->flush();

                        // Une image custom à été renséignée
                        if($imageFile){
                            $imgURL = $imageFile->getPathname();
                        }

                        // Sauvegarde de l'image du film
                        $file_name = 'movie_imgs/'.$movie->getId().'.png';
                        file_put_contents($file_name, file_get_contents($imgURL));
                    }else{
                        // Le film éxiste dans la base de données
                        $average = $movie->getScore();
                        $size = $movie->getNbVotes();
                        $value = $voteData['vote'];

                        $newScore = ($size * $average + $value) / ($size + 1);

                        $movie->setScore($newScore);
                        $entityManager->flush();
                    }
        
                    return $this->redirectToRoute('show_movie', ['id' => $movie->getId()]);
                }
            }else{
                $error = "Le film '".$voteData['name']."' n'existe pas.";
            }
        }

        return $this->renderForm('add_movie/index.html.twig', [
            'form' => $form,
            'error_msg' => $error,
        ]);
    }
}
