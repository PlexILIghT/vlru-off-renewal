<?php

namespace App\Controller;

use App\Entity\FolkDistrict;
use App\Form\FolkDistrictType;
use App\Repository\FolkDistrictRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/folk_districts')]
final class FolkDistrictController extends AbstractController
{
    #[Route(name: 'app_folk_district_index', methods: ['GET'])]
    public function index(FolkDistrictRepository $folkDistrictRepository): JsonResponse
    {
        $folkDistricts = $folkDistrictRepository->findAll();
        return $this->json([
            'folk_districts' => $folkDistricts,
        ], context: ['groups' => ['folk_district:list']]);
    }

    #[Route(name: 'app_folk_district_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $folkDistrict = new FolkDistrict();
        $folkDistrict->setName($data['name'] ?? '');

        $errors = $validator->validate($folkDistrict);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($folkDistrict);
        $entityManager->flush();

        return $this->json(
            $folkDistrict, 
            Response::HTTP_CREATED,
            context: ['groups' => ['folk_district:detail']]
        );
    }

    #[Route('/{id}', name: 'app_folk_district_show', methods: ['GET'])]
    public function show(FolkDistrict $folkDistrict): JsonResponse
    {
        return $this->json(
            $folkDistrict, 
            context: ['groups' => ['folk_district:detail']]
        );
    }

    #[Route('/{id}', name: 'app_folk_district_edit', methods: ['PUT'])]
    public function edit(Request $request, FolkDistrict $folkDistrict, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $folkDistrict->setName($data['name'] ?? $folkDistrict->getName());

        $errors = $validator->validate($folkDistrict);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return $this->json(
            $folkDistrict,
            context: ['groups' => ['folk_district:detail']]
        );
    }

    #[Route('/{id}', name: 'app_folk_district_delete', methods: ['DELETE'])]
    public function delete(FolkDistrict $folkDistrict, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($folkDistrict);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}