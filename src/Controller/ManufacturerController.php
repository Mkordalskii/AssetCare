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
    public function index(
        Request $request,
        ManufacturerRepository $manufacturerRepository
    ): Response {
        $status = $request->query->get('status', 'active');
        $query = trim((string) $request->query->get('q', ''));
        $offset = max(0, $request->query->getInt('offset', 0));

        $isActive = $status !== 'inactive';

        $perPage = ManufacturerRepository::MANUFACTURERS_PER_PAGE;

        $manufacturers = $manufacturerRepository->getManufacturerPaginator(
            $isActive,
            $query,
            $offset
        );

        $currentPage = intdiv($offset, $perPage) + 1;

        $totalPages = max(
            1,
            (int) ceil(count($manufacturers) / $perPage)
        );

        return $this->render('manufacturer/index.html.twig', [
            'manufacturers' => $manufacturers,
            'status' => $isActive ? 'active' : 'inactive',
            'query' => $query,
            'previous' => $offset - $perPage,
            'next' => $offset + $perPage,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
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
    #[Route('/{id}/restore', name: 'restore', methods: ['POST'])]
    public function restore(
        Manufacturer $manufacturer,
        Request $request,
        ManufacturerService $manufacturerService
    ): Response {
        if (!$this->isCsrfTokenValid(
            'restore_manufacturer_' . $manufacturer->getId(),
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException(
                'Invalid CSRF token.'
            );
        }

        $manufacturerService->activateManufacturer($manufacturer);

        $this->addFlash(
            'success',
            'Manufacturer was restored successfully.'
        );

        return $this->redirectToRoute(
            'app_manufacturer_index',
            ['status' => 'inactive']
        );
    }
}
