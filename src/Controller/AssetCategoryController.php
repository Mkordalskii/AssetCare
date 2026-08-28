<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\AssetCategory;
use App\Form\AssetCategoryType;
use App\Repository\AssetCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/categories', name: 'app_asset_category_')]
final class AssetCategoryController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, AssetCategoryRepository $repository): Response
    {
        $status = $request->query->getString('status', 'active') === 'inactive' ? 'inactive' : 'active';
        $query = trim($request->query->getString('q'));

        return $this->render('asset_category/index.html.twig', [
            'categories' => $repository->search($status === 'active', $query),
            'status' => $status,
            'query' => $query,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $manager): Response
    {
        return $this->handleForm(new AssetCategory(), $request, $manager, true);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(AssetCategory $category, Request $request, EntityManagerInterface $manager): Response
    {
        return $this->handleForm($category, $request, $manager, false);
    }

    #[Route('/{id}/deactivate', name: 'deactivate', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function deactivate(AssetCategory $category, Request $request, EntityManagerInterface $manager): Response
    {
        return $this->changeStatus($category, $request, $manager, false);
    }

    #[Route('/{id}/restore', name: 'restore', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function restore(AssetCategory $category, Request $request, EntityManagerInterface $manager): Response
    {
        return $this->changeStatus($category, $request, $manager, true);
    }

    private function handleForm(AssetCategory $category, Request $request, EntityManagerInterface $manager, bool $isNew): Response
    {
        $form = $this->createForm(AssetCategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($isNew) {
                $manager->persist($category);
            } else {
                $category->setUpdatedAt(new \DateTimeImmutable());
            }
            $manager->flush();
            $this->addFlash('success', $isNew ? 'Category was created successfully.' : 'Category was updated successfully.');

            return $this->redirectToRoute('app_asset_category_index');
        }

        return $this->render('asset_category/form.html.twig', [
            'form' => $form,
            'isNew' => $isNew,
        ]);
    }

    private function changeStatus(AssetCategory $category, Request $request, EntityManagerInterface $manager, bool $active): Response
    {
        $action = $active ? 'restore' : 'deactivate';
        if (!$this->isCsrfTokenValid($action . '_category_' . $category->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $category->setIsActive($active)->setUpdatedAt(new \DateTimeImmutable());
        $manager->flush();
        $this->addFlash('success', $active ? 'Category was restored successfully.' : 'Category was deactivated successfully.');

        return $this->redirectToRoute('app_asset_category_index', $active ? ['status' => 'inactive'] : []);
    }
}
