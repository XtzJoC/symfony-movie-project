<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Movie;

class ExportCSVController extends AbstractController
{
    #[Route('/export-csv', name: 'export_csv')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $movies = $entityManager->getRepository(Movie::class)->findAll();

        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);

        $data = [];

        foreach($movies as $movie){
            $data[] = [
                'name' => $movie->getName(),
                'description' => $movie->getDescription(),
                'vote' => $movie->getScore(),
            ];
        }

        $fileName = 'exports/export.csv';
        
        file_put_contents(
            $fileName,
            $serializer->encode($data, 'csv')
        );

        return $this->render('export_csv/index.html.twig', [
            'file_name' => $fileName,
        ]);
    }
}
