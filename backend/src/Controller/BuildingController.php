<?php
namespace App\Controller;

use App\Entity\Building;
use App\Entity\City;
use App\Entity\Street;
use App\Entity\District;
use App\Entity\FolkDistrict;
use App\Entity\BigFolkDistrict;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/buildings')]
#[OA\Tag(name: 'Buildings')]
class BuildingController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        summary: 'Получить список всех зданий',
        description: 'Возвращает массив всех зданий в системе'
    )]
    #[OA\Response(
        response: 200,
        description: 'Список зданий',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'string'),
                    new OA\Property(property: 'number', type: 'string', nullable: true),
                    new OA\Property(property: 'city', type: 'string', nullable: true),
                    new OA\Property(property: 'street', type: 'string', nullable: true),
                    new OA\Property(property: 'district', type: 'string', nullable: true),
                    new OA\Property(property: 'isFake', type: 'boolean')
                ]
            )
        )
    )]
    public function list(): JsonResponse {
        $items = $this->em->getRepository(Building::class)->findAll();
        $data = array_map(fn(Building $b) => [
            'id' => $b->getId(),
            'number' => $b->getNumber(),
            'city' => $b->getCity()?->getId(),
            'street' => $b->getStreet()?->getId(),
            'district' => $b->getDistrict()?->getId(),
            'isFake' => $b->isFake(),
        ], $items);
        return $this->json($data);
    }

    #[Route('/{id}', methods: ['GET'])]
    #[OA\Get(
        summary: 'Получить информацию о здании',
        description: 'Возвращает детальную информацию о здании по указанному ID'
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'UUID здания',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'uuid')
    )]
    #[OA\Response(
        response: 200,
        description: 'Детальная информация о здании',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'string'),
                new OA\Property(property: 'number', type: 'string', nullable: true),
                new OA\Property(property: 'coordinates', type: 'array', items: new OA\Items(type: 'number'), nullable: true),
                new OA\Property(property: 'blackouts', type: 'array', items: new OA\Items(type: 'string'))
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Здание не найдено',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'not found')
            ]
        )
    )]
    public function get(string $id): JsonResponse {
        $b = $this->em->getRepository(Building::class)->find($id);
        if (!$b) return $this->json(['error' => 'not found'], 404);
        return $this->json([
            'id' => $b->getId(),
            'number' => $b->getNumber(),
            'coordinates' => $b->getCoordinates(),
            'blackouts' => array_map(fn($bo)=>$bo->getId(), $b->getBlackouts()->toArray())
        ]);
    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(
        summary: 'Создать новое здание',
        description: 'Создает новую запись о здании в системе'
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'string', description: 'UUID здания (опционально, генерируется автоматически)', example: '550e8400-e29b-41d4-a716-446655440000'),
                new OA\Property(property: 'number', type: 'string', description: 'Номер здания', example: '15А'),
                new OA\Property(property: 'type', type: 'string', description: 'Тип здания', example: 'residential'),
                new OA\Property(property: 'isFake', type: 'boolean', description: 'Флаг временного здания', example: false),
                new OA\Property(property: 'city_id', type: 'string', description: 'UUID города', example: '550e8400-e29b-41d4-a716-446655440002'),
                new OA\Property(property: 'street_id', type: 'string', description: 'UUID улицы', example: '550e8400-e29b-41d4-a716-446655440003'),
                new OA\Property(property: 'district_id', type: 'string', description: 'UUID района', example: '550e8400-e29b-41d4-a716-446655440004'),
                new OA\Property(property: 'folk_district_id', type: 'string', description: 'UUID народного района', example: '550e8400-e29b-41d4-a716-446655440005'),
                new OA\Property(property: 'big_folk_district_id', type: 'string', description: 'UUID большого народного района', example: '550e8400-e29b-41d4-a716-446655440006'),
                new OA\Property(property: 'coordinates', type: 'array', description: 'Географические координаты [долгота, широта]', items: new OA\Items(type: 'number'))
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Здание успешно создано',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'string', description: 'UUID созданного здания')
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

        $b = new Building();

        // id generation if not provided: 32 hex chars
        $id = $data['id'] ?? bin2hex(random_bytes(16));
        $b->setId($id);

        $b->setNumber($data['number'] ?? null);
        $b->setType($data['type'] ?? null);
        $b->setIsFake(!empty($data['isFake']));

        if (!empty($data['city_id'])) {
            $c = $this->em->getRepository(City::class)->find($data['city_id']);
            if ($c) $b->setCity($c);
        }
        if (!empty($data['street_id'])) {
            $s = $this->em->getRepository(Street::class)->find($data['street_id']);
            if ($s) $b->setStreet($s);
        }
        if (!empty($data['district_id'])) {
            $d = $this->em->getRepository(District::class)->find($data['district_id']);
            if ($d) $b->setDistrict($d);
        }
        if (!empty($data['folk_district_id'])) {
            $fd = $this->em->getRepository(FolkDistrict::class)->find($data['folk_district_id']);
            if ($fd) $b->setFolkDistrict($fd);
        }
        if (!empty($data['big_folk_district_id'])) {
            $bfd = $this->em->getRepository(BigFolkDistrict::class)->find($data['big_folk_district_id']);
            if ($bfd) $b->setBigFolkDistrict($bfd);
        }
        if (array_key_exists('coordinates', $data)) {
            $b->setCoordinates($data['coordinates']);
        }

        $this->em->persist($b);
        $this->em->flush();

        return $this->json(['id' => $b->getId()], 201);
    }

    #[Route('/{id}', methods: ['PUT','PATCH'])]
    #[OA\Put(
        summary: 'Обновить информацию о здании',
        description: 'Обновляет информацию о здании (полное обновление)'
    )]
    #[OA\Patch(
        summary: 'Частично обновить информацию о здании',
        description: 'Частично обновляет информацию о здании'
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'UUID здания',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'uuid')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'number', type: 'string', description: 'Номер здания', example: '15А'),
                new OA\Property(property: 'type', type: 'string', description: 'Тип здания', example: 'residential'),
                new OA\Property(property: 'isFake', type: 'boolean', description: 'Флаг временного здания', example: false),
                new OA\Property(property: 'coordinates', type: 'array', description: 'Географические координаты [долгота, широта]', items: new OA\Items(type: 'number'))
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Здание успешно обновлено',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'ok', type: 'boolean', example: true)
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Здание не найдено',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'not found')
            ]
        )
    )]
    public function update(string $id, Request $req): JsonResponse {
        $b = $this->em->getRepository(Building::class)->find($id);
        if (!$b) return $this->json(['error' => 'not found'], 404);
        $data = json_decode($req->getContent(), true);
        if ($data === null) return $this->json(['error' => 'invalid json'], 400);

        if (array_key_exists('number', $data)) $b->setNumber($data['number']);
        if (array_key_exists('type', $data)) $b->setType($data['type']);
        if (array_key_exists('isFake', $data)) $b->setIsFake((bool)$data['isFake']);
        if (array_key_exists('coordinates', $data)) $b->setCoordinates($data['coordinates']);

        $this->em->flush();
        return $this->json(['ok' => true]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Удалить здание',
        description: 'Удаляет запись о здании по указанному ID'
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'UUID здания',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'uuid')
    )]
    #[OA\Response(
        response: 200,
        description: 'Здание успешно удалено',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'ok', type: 'boolean', example: true)
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Здание не найдено',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'not found')
            ]
        )
    )]
    public function delete(string $id): JsonResponse {
        $b = $this->em->getRepository(Building::class)->find($id);
        if (!$b) return $this->json(['error' => 'not found'], 404);
        $this->em->remove($b);
        $this->em->flush();
        return $this->json(['ok' => true]);
    }
}
