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
    /**
     * @OA\Put(
     *     path="/api/reservation/{id}",
     *     tags={"Reservation"},
     *     summary="Modify an existing reservation",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         description="Updated reservation data",
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="debut", type="string", format="date-time"),
     *             @OA\Property(property="valide", type="boolean"),
     *             @OA\Property(property="professionel_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Reservation updated successfully"),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=401, description="Not authorized"),
     *     @OA\Response(response=403, description="You can only modify reservations you are associated with or created"),
     *     @OA\Response(response=404, description="Reservation or Professional not found"),
     * @sec(name="Bearer")
     * )
     */
    #[Route('/api/reservation/{id}', name: 'modify_reservation', methods: ['PUT'])]
    public function modify(int $id, Request $request): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if (!$user) {
            return $this->json(['error' => 'Not authorized'], Response::HTTP_UNAUTHORIZED);
        }

        $reservation = $this->entityManager->getRepository(Reservation::class)->find($id);

        if (!$reservation) {
            return $this->json(['error' => 'Reservation not found'], Response::HTTP_NOT_FOUND);
        }

        // Checking if the authenticated user is the professional associated with the reservation or the client who created it
        if ($reservation->getProfessionel()->getId() !== $user->getId() && $reservation->getClient()->getId() !== $user->getId()) {
            return $this->json(['error' => 'You can only modify reservations you are associated with or created'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['debut'])) {
            $reservation->setDebut(new \DateTime($data['debut']));
        }

        if (isset($data['valide'])) {
            $reservation->setValide($data['valide']);
        }

        if (isset($data['professionel_id'])) {
            $professionel = $this->entityManager->getRepository(User::class)->find($data['professionel_id']);
            if (!$professionel) {
                return $this->json(['error' => 'Professional not found'], Response::HTTP_NOT_FOUND);
            }
            $reservation->setProfessionel($professionel);
        }

        $this->entityManager->flush();

        return $this->json(['status' => 'Reservation updated successfully!'], Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/reservation/{id}",
     *     tags={"Reservation"},
     *     summary="Delete a reservation",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Reservation deleted successfully"),
     *     @OA\Response(response=401, description="Not authorized"),
     *     @OA\Response(response=403, description="You can only delete your own reservations"),
     *     @OA\Response(response=404, description="Reservation not found"),
     * @sec(name="Bearer")
     * )
     */
    #[Route('/api/reservation/{id}', name: 'delete_reservation', methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if (!$user) {
            return $this->json(['error' => 'Not authorized'], Response::HTTP_UNAUTHORIZED);
        }

        $reservation = $this->entityManager->getRepository(Reservation::class)->find($id);

        if (!$reservation) {
            return $this->json(['error' => 'Reservation not found'], Response::HTTP_NOT_FOUND);
        }

        if ($reservation->getClient()->getId() !== $user->getId()) {
            return $this->json(['error' => 'You can only delete your own reservations'], Response::HTTP_FORBIDDEN);
        }

        $this->entityManager->remove($reservation);
        $this->entityManager->flush();

        return $this->json(['status' => 'Reservation deleted successfully!'], Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/reservations/me",
     *     tags={"Reservation"},
     *     summary="Retrieve reservations of authenticated client",
     *     @OA\Response(response=200, description="List of reservations"),
     *     @OA\Response(response=401, description="Not authorized"),
     * @sec(name="Bearer")
     * )
     */
    #[Route('/api/reservations/me', name: 'get_my_reservations', methods: ['GET'])]
    public function getMyReservations(): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if (!$user) {
            return $this->json(['error' => 'Not authorized'], Response::HTTP_UNAUTHORIZED);
        }

        $reservations = $this->entityManager->getRepository(Reservation::class)->findBy(['client' => $user]);

        $data = [];
        foreach ($reservations as $reservation) {
            $data[] = [
                'id' => $reservation->getId(),
                'debut' => $reservation->getDebut()->format('Y-m-d H:i:s'),
                'valide' => $reservation->isValide(),
                'professionel_id' => $reservation->getProfessionel()->getId()
            ];
        }

        return $this->json(['reservations' => $data], Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/professional/reservations",
     *     tags={"Reservation"},
     *     summary="Get all reservations of the authenticated professional",
     *     @OA\Response(response=200, description="List of reservations for the professional"),
     *     @OA\Response(response=401, description="Not authorized"),
     *     @OA\Response(response=403, description="Insufficient permissions. Only professionals can view reservations."),
     *     @OA\Response(response=404, description="Professional not found"),
     * @sec(name="Bearer")
     * )
     */
    #[Route('/api/professional/reservations', name: 'get_professional_reservations', methods: ['GET'])]
    public function getReservationsForProfessional(): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        // Check if the user is authenticated
        if (!$user) {
            return $this->json(['error' => 'Not authorized'], Response::HTTP_UNAUTHORIZED);
        }

        // Check if the user has the ROLE_PROFESSIONAL
        if (!in_array('ROLE_PROFESSIONAL', $user->getRoles())) {
            return $this->json(['error' => "Insufficient permissions. Only professionals can view reservations."], Response::HTTP_FORBIDDEN);
        }

        $reservations = $this->entityManager->getRepository(Reservation::class)->findBy(['professionel' => $user]);

        // Check if there are any reservations
        if (!$reservations) {
            return $this->json(['error' => 'No reservations found for this professional'], Response::HTTP_NOT_FOUND);
        }

        $data = [];
        foreach ($reservations as $reservation) {
            $data[] = [
                'id' => $reservation->getId(),
                'debut' => $reservation->getDebut()->format('Y-m-d H:i:s'),
                'valide' => $reservation->isValide(),
                'client_id' => $reservation->getClient()->getId(),
            ];
        }

        return $this->json($data, Response::HTTP_OK);
    }

}
