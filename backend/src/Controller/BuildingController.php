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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/buildings')]
class BuildingController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    #[Route('', methods: ['GET'])]
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
    public function delete(string $id): JsonResponse {
        $b = $this->em->getRepository(Building::class)->find($id);
        if (!$b) return $this->json(['error' => 'not found'], 404);
        $this->em->remove($b);
        $this->em->flush();
        return $this->json(['ok' => true]);
    }
}
