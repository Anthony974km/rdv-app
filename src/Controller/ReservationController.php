<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;

class ReservationController extends AbstractController
{

    #[Route('/api/reservation/create', name: 'app_create_reservation', methods: ['POST'])]
    public function createReservation(EntityManagerInterface $em, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $requestData = json_decode($request->getContent(), true);
        $debut = new \DateTime($requestData['debut']);
        $client = $this->getUser();
        $reservation = new Reservation();
        $reservation->setClient($client);
        $reservation->setDebut($debut);
        $em->persist($reservation);
        $em->flush();

        return $this->json(['message' => 'Réserver avec succès']);
    }
}
