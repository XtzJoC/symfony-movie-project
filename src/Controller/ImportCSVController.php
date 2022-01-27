<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Movie;

class ImportCSVController extends AbstractController
{
    #[Route('/import-csv', name: 'import_csv')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $defaultData = [];

        $form = $this->createFormBuilder($defaultData)
            ->add('csvFile', FileType::class, [
                'label' => 'Fichier CSV (.csv)',
                'attr' => [
                    'accept' => 'text/csv',
                ],
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'text/csv',
                        ],
                        'mimeTypesMessage' => 'Merci d\'uploader un fichier .csv',
                    ])
                ],
            ])
            ->add('submit', SubmitType::class, ['label' => 'Importer'])
            ->getForm();

        $data = [];

        $form->handleRequest($request);

        $error = NULL;

        if($form->isSubmitted() && $form->isValid()){
            $csvFile = $form->get('csvFile')->getData();

            $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
            $data = $serializer->decode(file_get_contents($csvFile->getPathname()), 'csv');
            
            foreach($data as $datum){
                if(!isset($datum['name']) || !isset($datum['description']) || !isset($datum['note'])){
                    $error = "Le fichier csv n'a pas le bon format (name | description | note)";
                    break;
                }

                $entityManager = $doctrine->getManager();

                $movie = new Movie();
                $movie->setName($datum['name']);
                $movie->setDescription($datum['description']);
                $movie->setScore($datum['note']);
                $movie->setNbVotes(1);

                $entityManager->persist($movie);
                $entityManager->flush();
            }

            if($error == NULL){
                return $this->redirectToRoute('home_page');
            }
        }

        return $this->renderForm('import_csv/index.html.twig', [
            'form' => $form,
            'data' => $data,
            'error' => $error,
        ]);
    }
}
