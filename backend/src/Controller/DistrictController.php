<?php

namespace App\Controller;

use App\Entity\District;
use App\Form\DistrictType;
use App\Repository\DistrictRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/districts')]
final class DistrictController extends AbstractController
{
    #[Route(name: 'app_district_index', methods: ['GET'])]
    public function index(DistrictRepository $districtRepository): JsonResponse
    {
        $districts = $districtRepository->findAll();
        return $this->json([
            'districts' => $districts,
        ], context: ['groups' => ['district:list']]);
    }

    #[Route(name: 'app_district_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $district = new District();
        $district->setName($data['name'] ?? '');

        $errors = $validator->validate($district);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($district);
        $entityManager->flush();

        return $this->json(
            $district, 
            Response::HTTP_CREATED,
            context: ['groups' => ['district:detail']]
        );
    }

    #[Route('/{id}', name: 'app_district_show', methods: ['GET'])]
    public function show(District $district): JsonResponse
    {
        return $this->json(
            $district, 
            context: ['groups' => ['district:detail']]
        );
    }

    #[Route('/{id}', name: 'app_district_edit', methods: ['PUT'])]
    public function edit(Request $request, District $district, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $district->setName($data['name'] ?? $district->getName());

        $errors = $validator->validate($district);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return $this->json(
            $district,
            context: ['groups' => ['district:detail']]
        );
    }

    #[Route('/{id}', name: 'app_district_delete', methods: ['DELETE'])]
    public function delete(District $district, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($district);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}