<?php

namespace App\Controller;

use App\Entity\Street;
use App\Form\StreetType;
use App\Repository\StreetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/streets')]
final class StreetController extends AbstractController
{
    #[Route(name: 'app_street_index', methods: ['GET'])]
    public function index(StreetRepository $streetRepository): JsonResponse
    {
        $streets = $streetRepository->findAll();
        return $this->json([
            'streets' => $streets,
        ], context: ['groups' => ['street:list']]);
    }

    #[Route(name: 'app_street_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator, CityRepository $cityRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $street = new Street();
        $street->setName($data['name'] ?? '');
        
        if (isset($data['city_id'])) {
            $city = $cityRepository->find($data['city_id']);
            if ($city) {
                $street->setCity($city);
            }
        }

        $errors = $validator->validate($street);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($street);
        $entityManager->flush();

        return $this->json(
            $street, 
            Response::HTTP_CREATED,
            context: ['groups' => ['street:detail']]
        );
    }

    #[Route('/{id}', name: 'app_street_show', methods: ['GET'])]
    public function show(Street $street): JsonResponse
    {
        return $this->json(
            $street, 
            context: ['groups' => ['street:detail']]
        );
    }

    #[Route('/{id}', name: 'app_street_edit', methods: ['PUT'])]
    public function edit(Request $request, Street $street, EntityManagerInterface $entityManager, ValidatorInterface $validator, CityRepository $cityRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $street->setName($data['name'] ?? $street->getName());
        
        if (isset($data['city_id'])) {
            $city = $cityRepository->find($data['city_id']);
            if ($city) {
                $street->setCity($city);
            }
        }

        $errors = $validator->validate($street);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return $this->json(
            $street,
            context: ['groups' => ['street:detail']]
        );
    }

    #[Route('/{id}', name: 'app_street_delete', methods: ['DELETE'])]
    public function delete(Street $street, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($street);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}