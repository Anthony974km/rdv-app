<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Security as sec;

class ReservationController extends AbstractController
{
    private Security $security;
    private EntityManagerInterface $entityManager;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }
    /**
     * @OA\Post(
     *     path="/api/reservation/create",
     *     tags={"Reservation"},
     *     summary="Create a new reservation",
     *     @OA\RequestBody(
     *         description="Reservation to be created",
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="debut",
     *                 description="Start time of the reservation",
     *                 type="string",
     *                 format="date-time"
     *             ),
     *             @OA\Property(
     *                 property="valide",
     *                 description="Indicates if the reservation is valid",
     *                 type="boolean"
     *             ),
     *                 @OA\Property(
     *                     property="professionel_id",
     *                     description="ID of the professional",
     *                     type="integer"
     *
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Reservation created successfully"),
     *     @OA\Response(response=400, description="Invalid input"),
     * @sec(name="Bearer")
     * )
     */

    #[Route('/api/reservation/create', name: 'create_reservation', methods: ['POST'])]
    public function create(Request $request): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if (!$user) {
            return $this->json(['error' => 'Not authorized'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data['debut'] || !$data['professionel_id']) {
            return $this->json(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        }

        $professionel = $this->entityManager
            ->getRepository(User::class)
            ->find($data['professionel_id']);

        if (!$professionel) {
            return $this->json(['error' => 'Professional not found'], Response::HTTP_NOT_FOUND);
        }

        $reservation = new Reservation();
        $reservation->setClient($user);
        $reservation->setProfessionel($professionel);
        $reservation->setDebut(new \DateTime($data['debut']));
        $reservation->setValide($data['valide'] ?? false);

        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

        return $this->json(['status' => 'Reservation created successfully!', 'reservation_id' => $reservation->getId()], Response::HTTP_OK);
    }
}
