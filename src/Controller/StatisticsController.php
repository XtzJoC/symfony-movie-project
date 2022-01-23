<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Movie;

class StatisticsController extends AbstractController
{
    #[Route('/statistics', name: 'statistics')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $moviesBetween02 = $entityManager->getRepository(Movie::class)->countWhereBetween(0, 2);
        $moviesBetween24 = $entityManager->getRepository(Movie::class)->countWhereBetween(2, 4);
        $moviesBetween46 = $entityManager->getRepository(Movie::class)->countWhereBetween(4, 6);
        $moviesBetween68 = $entityManager->getRepository(Movie::class)->countWhereBetween(6, 8);
        $moviesBetween810 = $entityManager->getRepository(Movie::class)->countWhereBetween(8, 10);
        
        $pieChart = new PieChart();
        $pieChart->getData()->setArrayToDataTable(
            [
                ['Vote', 'Nombre de film ayant ce vote'],
                ['2 ou moins', $moviesBetween02],
                ['2 à 4', $moviesBetween24],
                ['4 à 6', $moviesBetween46],
                ['6 à 8', $moviesBetween68],
                ['8 ou plus', $moviesBetween810]
            ]
        );

        $pieChart->getOptions()->setTitle('Statistiques des votes sur les films');
        $pieChart->getOptions()->setHeight(500);
        $pieChart->getOptions()->setWidth(900);
        $pieChart->getOptions()->getTitleTextStyle()->setBold(true);
        $pieChart->getOptions()->getTitleTextStyle()->setColor('#000000');
        $pieChart->getOptions()->getTitleTextStyle()->setItalic(true);
        $pieChart->getOptions()->getTitleTextStyle()->setFontName('Arial');
        $pieChart->getOptions()->getTitleTextStyle()->setFontSize(20);
        
        return $this->render('statistics/index.html.twig', [
            'piechart' => $pieChart, 
        ]);
    }
}
