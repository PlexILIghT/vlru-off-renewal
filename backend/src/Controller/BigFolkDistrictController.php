<?php

namespace App\Controller;

use App\Entity\BigFolkDistrict;
use App\Form\BigFolkDistrictType;
use App\Repository\BigFolkDistrictRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/big_folk_districts')]
final class BigFolkDistrictController extends AbstractController
{
    #[Route(name: 'app_big_folk_district_index', methods: ['GET'])]
    public function index(BigFolkDistrictRepository $bigFolkDistrictRepository): JsonResponse
    {
        $bigFolkDistricts = $bigFolkDistrictRepository->findAll();
        return $this->json([
            'big_folk_districts' => $bigFolkDistricts,
        ], context: ['groups' => ['big_folk_district:list']]);
    }

    #[Route(name: 'app_big_folk_district_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $bigFolkDistrict = new BigFolkDistrict();
        $bigFolkDistrict->setName($data['name'] ?? '');

        $errors = $validator->validate($bigFolkDistrict);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($bigFolkDistrict);
        $entityManager->flush();

        return $this->json(
            $bigFolkDistrict, 
            Response::HTTP_CREATED,
            context: ['groups' => ['big_folk_district:detail']]
        );
    }

    #[Route('/{id}', name: 'app_big_folk_district_show', methods: ['GET'])]
    public function show(BigFolkDistrict $bigFolkDistrict): JsonResponse
    {
        return $this->json(
            $bigFolkDistrict, 
            context: ['groups' => ['big_folk_district:detail']]
        );
    }

    #[Route('/{id}', name: 'app_big_folk_district_edit', methods: ['PUT'])]
    public function edit(Request $request, BigFolkDistrict $bigFolkDistrict, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $bigFolkDistrict->setName($data['name'] ?? $bigFolkDistrict->getName());

        $errors = $validator->validate($bigFolkDistrict);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return $this->json(
            $bigFolkDistrict,
            context: ['groups' => ['big_folk_district:detail']]
        );
    }

    #[Route('/{id}', name: 'app_big_folk_district_delete', methods: ['DELETE'])]
    public function delete(BigFolkDistrict $bigFolkDistrict, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($bigFolkDistrict);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}