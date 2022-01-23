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

class ImportCSVController extends AbstractController
{
    #[Route('/import-csv', name: 'import_csv')]
    public function index(Request $request): Response
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
        if($form->isSubmitted() && $form->isValid()){
            $csvFile = $form->get('csvFile')->getData();

            $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
            $data = $serializer->decode(file_get_contents($csvFile->getPathname()), 'csv');
            var_dump($data);
        }

        return $this->renderForm('import_csv/index.html.twig', [
            'form' => $form,
            'data' => $data,
        ]);
    }
}
