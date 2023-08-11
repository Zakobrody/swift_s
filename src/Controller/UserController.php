<?php

namespace App\Controller;

use App\Form\UploadUserType;
use App\Service\File\Csv;
use App\Service\ImportUser;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Security("is_granted('ROLE_USER')")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/user", name="app_user", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    /**
     * @Route("/user/import", name="app_user_import", methods={"GET","POST"})
     */
    public function importUser(Request $request, ImportUser $importUserService, Csv $csvFile): Response
    {
        $form = $this->createForm(UploadUserType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var UploadedFile $filename */
                $fileName = $form->get('file')->getData();

                $filePath = $csvFile->moveFile($fileName);
                $csvData = $csvFile->readFile($filePath);

                $responseData = $importUserService->processFile($csvData);

                $this->addFlash('success', "Pomyślne zaimportowano plik z listą użytkowników. Pobrane rekordy z pliku: {$responseData['total']}");
                if (!empty($responseData['done'])) {
                    $this->addFlash('success', "Rekordy dodane do bazy: {$responseData['done']}");
                }
                if (!empty($responseData['error'])) {
                    $this->addFlash('danger', "Błędne dane: {$responseData['error']}");
                }
                $this->redirectToRoute('app_user');
            } catch (Exception $e) {
                $this->addFlash('danger', 'Wystąpił błąd podczas importu danych.');
                $this->redirectToRoute('app_user');
            }
        }

        return $this->render('user/import.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
