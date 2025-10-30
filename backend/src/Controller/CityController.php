<?php

namespace App\Controller;

use App\Entity\City;
use App\Form\CityType;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/cities')]
final class CityController extends AbstractController
{
    #[Route(name: 'app_city_index', methods: ['GET'])]
    public function index(CityRepository $cityRepository): JsonResponse
    {
        $cities = $cityRepository->findAll();
        return $this->json([
            'cities' => $cities,
        ], context: ['groups' => ['city:list']]);
    }

    #[Route(name: 'app_city_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $city = new City();
        $city->setName($data['name'] ?? '');

        $errors = $validator->validate($city);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($city);
        $entityManager->flush();

        return $this->json(
            $city, 
            Response::HTTP_CREATED,
            context: ['groups' => ['city:detail']]
        );
    }

    #[Route('/{id}', name: 'app_city_show', methods: ['GET'])]
    public function show(City $city): JsonResponse
    {
        return $this->json(
            $city, 
            context: ['groups' => ['city:detail']]
        );
    }

    #[Route('/{id}', name: 'app_city_edit', methods: ['PUT'])]
    public function edit(Request $request, City $city, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $city->setName($data['name'] ?? $city->getName());

        $errors = $validator->validate($city);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return $this->json(
            $city,
            context: ['groups' => ['city:detail']]
        );
    }

    #[Route('/{id}', name: 'app_city_delete', methods: ['DELETE'])]
    public function delete(City $city, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($city);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}