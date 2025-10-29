<?php
namespace App\Controller;

use App\Entity\Blackout;
use App\Entity\Building;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/blackouts')]
class BlackoutController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('', methods:['GET'])]
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
    public function delete(string $id): JsonResponse {
        $b = $this->em->getRepository(Blackout::class)->find($id);
        if (!$b) return $this->json(['error' => 'not found'],404);
        $this->em->remove($b);
        $this->em->flush();
        return $this->json(['ok' => true]);
    }
}
