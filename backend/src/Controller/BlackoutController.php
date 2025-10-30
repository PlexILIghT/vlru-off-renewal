<?php

namespace App\Controller;

use App\Entity\Blackout;
use App\Entity\Building;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/api/blackouts')]
#[OA\Tag(name: 'Blackouts')]
class BlackoutController extends AbstractController
{
    #[Route('/search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');

        $blackouts = $this->em->getRepository(Blackout::class)
            ->createQueryBuilder('b')
            ->leftJoin('b.buildings', 'building')
            ->leftJoin('building.street', 'street')
            ->leftJoin('building.city', 'city')
            ->where('b.description LIKE :query')
            ->orWhere('street.name LIKE :query')
            ->orWhere('city.name LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();

        $data = array_map(fn(Blackout $b) => $this->serializeBlackout($b), $blackouts);

        return $this->json($data);
    }

    #[Route('/active', name: 'blackouts_active', methods: ['GET'])]
    public function active(): JsonResponse
    {
        $now = new \DateTime();

        $blackouts = $this->em->getRepository(Blackout::class)
            ->createQueryBuilder('b')
            ->where('b.startDate <= :now')
            ->andWhere('b.endDate >= :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();

        $data = array_map(fn(Blackout $b) => $this->serializeBlackout($b), $blackouts);

        return $this->json($data);
    }

    public function __construct(private EntityManagerInterface $em) {}

    #[Route('', methods:['GET'])]
    #[OA\Get(
        description: 'Возвращает массив всех записей об отключениях',
        summary: 'Получить список всех отключений',
    )]
    #[OA\Response(
        response: 200,
        description: 'Список отключений',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'string'),
                    new OA\Property(property: 'start', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'end', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'buildings', type: 'array', items: new OA\Items(type: 'string'))
                ]
            )
        )
    )]
    public function list(): JsonResponse {
        $items = $this->em->getRepository(Blackout::class)->findAll();
        $data = array_map(fn(Blackout $b) => [
            'id' => $b->getId(),
            'start' => $b->getStartDate()?->format('c'),
            'end' => $b->getEndDate()?->format('c'),
            'buildings' => array_map(fn($bb)=>$bb->getId(), $b->getBuildings()->toArray())
        ], $items);
        return $this->json($data);
    }

    #[Route('/{id}', methods:['GET'])]
    #[OA\Get(
        summary: 'Получить информацию об отключении',
        description: 'Возвращает детальную информацию об отключении по указанному ID'
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'UUID отключения',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'uuid')
    )]
    #[OA\Response(
        response: 200,
        description: 'Детальная информация об отключении',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'string'),
                new OA\Property(property: 'start', type: 'string', format: 'date-time'),
                new OA\Property(property: 'end', type: 'string', format: 'date-time'),
                new OA\Property(property: 'description', type: 'string', nullable: true),
                new OA\Property(property: 'buildings', type: 'array', items: new OA\Items(type: 'string'))
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Отключение не найдено',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'not found')
            ]
        )
    )]
    public function get(string $id): JsonResponse {
        $b = $this->em->getRepository(Blackout::class)->find($id);
        if (!$b) return $this->json(['error' => 'not found'],404);
        return $this->json([
            'id' => $b->getId(),
            'start' => $b->getStartDate()?->format('c'),
            'end' => $b->getEndDate()?->format('c'),
            'description' => $b->getDescription(),
            'buildings' => array_map(fn($bb)=>$bb->getId(), $b->getBuildings()->toArray())
        ]);
    }

    #[Route('', methods:['POST'])]
    #[OA\Post(
        summary: 'Создать новое отключение',
        description: 'Создает новую запись об отключении электроэнергии'
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'string', description: 'UUID отключения (опционально, генерируется автоматически)', example: '550e8400-e29b-41d4-a716-446655440000'),
                new OA\Property(property: 'start_date', type: 'string', format: 'date-time', description: 'Дата и время начала отключения', example: '2024-01-15T10:00:00+03:00'),
                new OA\Property(property: 'end_date', type: 'string', format: 'date-time', description: 'Дата и время окончания отключения', example: '2024-01-15T12:00:00+03:00'),
                new OA\Property(property: 'description', type: 'string', description: 'Описание отключения', example: 'Плановые работы на подстанции'),
                new OA\Property(property: 'type', type: 'string', description: 'Тип отключения', example: 'planned'),
                new OA\Property(property: 'initiator_name', type: 'string', description: 'Инициатор отключения', example: 'Энергосбыт'),
                new OA\Property(property: 'source', type: 'string', description: 'Источник информации', example: 'hotline'),
                new OA\Property(
                    property: 'building_ids',
                    type: 'array',
                    description: 'Массив UUID зданий',
                    items: new OA\Items(type: 'string', example: '550e8400-e29b-41d4-a716-446655440001')
                )
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Отключение успешно создано',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'string', description: 'UUID созданного отключения')
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Некорректный запрос',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'invalid json')
            ]
        )
    )]
    public function create(Request $req): JsonResponse {
        $data = json_decode($req->getContent(), true);
        if ($data === null) return $this->json(['error' => 'invalid json'], 400);

        $b = new Blackout();
        $id = $data['id'] ?? bin2hex(random_bytes(16));
        $b->setId($id);

        if (!empty($data['start_date'])) $b->setStartDate(new \DateTime($data['start_date']));
        if (!empty($data['end_date'])) $b->setEndDate(new \DateTime($data['end_date']));
        $b->setDescription($data['description'] ?? null);
        $b->setType($data['type'] ?? null);
        $b->setInitiatorName($data['initiator_name'] ?? null);
        $b->setSource($data['source'] ?? null);

        if (!empty($data['building_ids']) && is_array($data['building_ids'])) {
            foreach ($data['building_ids'] as $bid) {
                $bb = $this->em->getRepository(Building::class)->find($bid);
                if ($bb) $b->addBuilding($bb);
            }
        }

        $this->em->persist($b);
        $this->em->flush();
        return $this->json(['id' => $b->getId()], 201);
    }

    #[Route('/{id}', methods:['DELETE'])]
    #[OA\Delete(
        summary: 'Удалить отключение',
        description: 'Удаляет запись об отключении по указанному ID'
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'UUID отключения',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'uuid')
    )]
    #[OA\Response(
        response: 200,
        description: 'Отключение успешно удалено',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'ok', type: 'boolean', example: true)
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Отключение не найдено',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'not found')
            ]
        )
    )]
    public function delete(string $id): JsonResponse {
        $b = $this->em->getRepository(Blackout::class)->find($id);
        if (!$b) return $this->json(['error' => 'not found'],404);
        $this->em->remove($b);
        $this->em->flush();
        return $this->json(['ok' => true]);
    }

    #[Route('/type/{type}', methods: ['GET'])]
    public function byType(string $type): JsonResponse
    {
        $blackouts = $this->em->getRepository(Blackout::class)
            ->findBy(['type' => $type]);

        $data = array_map(fn(Blackout $b) => $this->serializeBlackout($b), $blackouts);

        return $this->json($data);
    }

    private function serializeBlackout(Blackout $blackout): array
    {
        return [
            'id' => $blackout->getId(),
            'reason' => $blackout->getDescription(),
            'status' => $this->calculateStatus($blackout),
            'organization' => [
                'id' => $blackout->getInitiatorName(), // временно
                'name' => $blackout->getInitiatorName(),
                'serviceType' => [$blackout->getType()]
            ],
            'startTime' => $blackout->getStartDate()?->format('c'),
            'endTime' => $blackout->getEndDate()?->format('c'),
            'outageType' => $blackout->getType(),
            'houses' => array_map(fn($building) => $this->serializeBuilding($building),
                $blackout->getBuildings()->toArray())
        ];
    }

    private function calculateStatus(Blackout $blackout): string
    {
        $now = new \DateTime();
        $start = $blackout->getStartDate();
        $end = $blackout->getEndDate();

        if (!$start || !$end) return 'planned';

        if ($now < $start) return 'planned';
        if ($now > $end) return 'completed';
        return 'active';
    }

    private function serializeBuilding(Building $building): array
    {
        return [
            'id' => $building->getId(),
            'address' => $this->formatAddress($building),
            'hotWaterStatus' => 'connected', // нужно вычислять на основе blackouts
            'heatingStatus' => 'connected',
            'coldWaterStatus' => 'connected'
        ];
    }

    private function formatAddress(Building $building): string
    {
        $parts = [];
        if ($building->getStreet()?->getName()) {
            $parts[] = $building->getStreet()->getName();
        }
        if ($building->getNumber()) {
            $parts[] = $building->getNumber();
        }
        return implode(', ', $parts);
    }
}
