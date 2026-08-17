<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Asset;
use App\Form\AssetType;
use App\Service\AssetListService;
use App\Service\AssetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/assets', name: 'app_asset_')]
final class AssetController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        AssetListService $assetListService,
    ): Response {
        $status = $request->query->getString('status', 'active');
        $query = trim($request->query->getString('q'));
        $categoryId = $this->getOptionalPositiveInt($request, 'category');
        $manufacturerId = $this->getOptionalPositiveInt($request, 'manufacturer');
        $requestedPage = max(1, $request->query->getInt('page', 1));
        $isActive = $status !== 'inactive';

        $assetPage = $assetListService->getPage(
            $isActive,
            $query,
            $categoryId,
            $manufacturerId,
            $requestedPage,
        );

        if ($assetPage->currentPage !== $requestedPage) {
            return $this->redirectToRoute('app_asset_index', [
                'status' => $isActive ? 'active' : 'inactive',
                'q' => $query,
                'category' => $categoryId,
                'manufacturer' => $manufacturerId,
                'page' => $assetPage->currentPage,
            ]);
        }

        return $this->render('asset/index.html.twig', [
            'assetPage' => $assetPage,
            'status' => $isActive ? 'active' : 'inactive',
            'query' => $query,
            'categories' => $assetListService->getCategories(),
            'manufacturers' => $assetListService->getManufacturers(),
            'selectedCategory' => $categoryId,
            'selectedManufacturer' => $manufacturerId,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        AssetService $assetService,
    ): Response {
        $asset = new Asset();
        $form = $this->createForm(AssetType::class, $asset);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $assetService->createAsset($asset);
            $this->addFlash('success', 'Asset was created successfully.');

            return $this->redirectToRoute('app_asset_index');
        }

        return $this->render('asset/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Asset $asset,
        Request $request,
        AssetService $assetService,
    ): Response {
        $form = $this->createForm(AssetType::class, $asset, [
            'submit_label' => 'Update asset',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $assetService->updateAsset($asset);
            $this->addFlash('success', 'Asset was updated successfully.');

            return $this->redirectToRoute('app_asset_index');
        }

        return $this->render('asset/edit.html.twig', [
            'form' => $form,
            'asset' => $asset,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(
        Asset $asset,
        Request $request,
        AssetService $assetService,
    ): Response {
        if (!$this->isCsrfTokenValid(
            'delete_asset_' . $asset->getId(),
            $request->request->getString('_token'),
        )) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $assetService->deactivateAsset($asset);
        $this->addFlash('success', 'Asset was deactivated successfully.');

        return $this->redirectToRoute('app_asset_index');
    }

    #[Route('/{id}/restore', name: 'restore', methods: ['POST'])]
    public function restore(
        Asset $asset,
        Request $request,
        AssetService $assetService,
    ): Response {
        if (!$this->isCsrfTokenValid(
            'restore_asset_' . $asset->getId(),
            $request->request->getString('_token'),
        )) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $assetService->activateAsset($asset);
        $this->addFlash('success', 'Asset was restored successfully.');

        return $this->redirectToRoute(
            'app_asset_index',
            ['status' => 'inactive'],
        );
    }

    private function getOptionalPositiveInt(Request $request, string $name): ?int
    {
        $value = $request->query->getInt($name);

        return $value > 0 ? $value : null;
    }
}
