<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use OpenApi\Annotations as OA;

class RegistrationController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private UserRepository $userRepository;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher,UserRepository $userRepository, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->userRepository = $userRepository;
        $this->logger = $logger;
    }
    /**
     * @OA\Post(
     *     path="/api/registerAPI",
     *     tags={"User"},
     *     summary="Register a new User",
     *     @OA\RequestBody(
     *         description="Professional to be registered",
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="email",
     *                 description="User's email",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 description="User's password",
     *                 type="string"
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Professional registered successfully"),
     *     @OA\Response(response=400, description="Invalid input")
     * )
     */
    #[Route('/api/registerAPI', name: 'user_registration', methods: ['POST'])]
    public function register(Request $request): Response
    {
        return $this->registerUser($request, ['ROLE_USER']);
    }

    /**
     *
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login with username and password to obtain JWT token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         description="Professional to be registered",
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="username",
     *                 description="User's email",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 description="User's password",
     *                 type="string"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Returned when successful",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="token", type="string", example="your_jwt_token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Returned when the login credentials are incorrect"
     *     )
     * )
     */
    #[Route('/api/login', name: 'login', methods: ['POST'])]
    public function login(): void
    {
        // Ce code ne sera jamais exécuté,
        // car la route est gérée par le pare-feu de sécurité JWT.
    }
    /**
     * @OA\Post(
     *     path="/api/registerProfessionalAPI",
     *     tags={"User"},
     *     summary="Register a new professional",
     *     @OA\RequestBody(
     *         description="Professional to be registered",
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="email",
     *                 description="User's email",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 description="User's password",
     *                 type="string"
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Professional registered successfully"),
     *     @OA\Response(response=400, description="Invalid input")
     * )
     */

    #[Route('/api/registerProfessionalAPI', name: 'professional_registration', methods: ['POST'])]
    public function registerProfessional(Request $request): Response
    {
        return $this->registerUser($request, ['ROLE_PROFESSIONAL']);
    }

    private function registerUser(Request $request, array $roles): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!$data['email'] || !$data['password']) {
            return $this->json(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setRoles($roles);
        $passwordHasher = $this->passwordHasher->hashPassword($user, $data['password']);
        $this->logger->debug("Mot de passe : ". $passwordHasher);
        $user->setPassword($passwordHasher);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json(['status' => 'Registration successful!', 'user_id' => $user->getId()], Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/professionals",
     *     tags={"User"},
     *     summary="Retrieve a list of professionals",
     *     @OA\Response(response=200, description="List of professionals retrieved successfully"),
     *     @OA\Response(response=400, description="Invalid input")
     * )
     */
    #[Route('/api/professionals', name: 'get_professionals', methods: ['GET'])]
    public function getProfessionals(): JsonResponse
    {
        $professionals = $this->userRepository->findProfessionals();

        $data = [];
        foreach ($professionals as $professional) {
            $data[] = [
                'id' => $professional->getId(),
                'email' => $professional->getEmail(),
            ];
        }

        return $this->json($data);
    }

    /**
     * @OA\Get(
     *     path="/api/users",
     *     tags={"User"},
     *     summary="Retrieve a list of users",
     *     @OA\Response(response=200, description="List of user retrieved successfully"),
     *     @OA\Response(response=400, description="Invalid input")
     * )
     */
    #[Route('/api/users', name: 'get_users', methods: ['GET'])]
    public function getUsers(): JsonResponse
    {
        $professionals = $this->userRepository->findUsers();

        $data = [];
        foreach ($professionals as $professional) {
            $data[] = [
                'id' => $professional->getId(),
                'email' => $professional->getEmail(),
            ];
        }

        return $this->json($data);
    }

    /**
     * @Route("/api/howiam", methods="GET")
     */
    public function getUserDetails(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        return new JsonResponse([
            'roles'    => $user->getRoles()
        ]);
    }
}
