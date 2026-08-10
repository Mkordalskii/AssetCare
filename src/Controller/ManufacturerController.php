<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ManufacturerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\ManufacturerType;
use App\Service\ManufacturerService;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Manufacturer;

#[Route('/manufacturers', name: 'app_manufacturer_')]
final class ManufacturerController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(ManufacturerRepository $manufacturerRepository): Response
    {
        $manufacturers = $manufacturerRepository->findAllActive();

        return $this->render('manufacturer/index.html.twig', [
            'manufacturers' => $manufacturers,
        ]);
    }
    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        ManufacturerService $manufacturerService
    ): Response {
        $manufacturer = new Manufacturer();

        $form = $this->createForm(
            ManufacturerType::class,
            $manufacturer
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manufacturerService->createManufacturer($manufacturer);

            $this->addFlash(
                'success',
                'Manufacturer was created successfully.'
            );

            return $this->redirectToRoute('app_manufacturer_index');
        }

        return $this->render('manufacturer/create.html.twig', [
            'form' => $form,
        ]);
    }
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Manufacturer $manufacturer,
        Request $request,
        ManufacturerService $manufacturerService
    ): Response {
        $form = $this->createForm(
            ManufacturerType::class,
            $manufacturer,
            [
                'submit_label' => 'Update manufacturer',
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manufacturerService->updateManufacturer($manufacturer);

            $this->addFlash(
                'success',
                'Manufacturer was updated successfully.'
            );

            return $this->redirectToRoute('app_manufacturer_index');
        }

        return $this->render('manufacturer/edit.html.twig', [
            'form' => $form,
            'manufacturer' => $manufacturer,
        ]);
    }
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(
        Manufacturer $manufacturer,
        Request $request,
        ManufacturerService $manufacturerService
    ): Response {
        if (!$this->isCsrfTokenValid(
            'delete_manufacturer_' . $manufacturer->getId(),
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException(
                'Invalid CSRF token.'
            );
        }

        $manufacturerService->deactivateManufacturer($manufacturer);

        $this->addFlash(
            'success',
            'Manufacturer was deactivated successfully.'
        );

        return $this->redirectToRoute('app_manufacturer_index');
    }
}
