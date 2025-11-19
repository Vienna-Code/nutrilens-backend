<?php

namespace App\Controller;

use App\Dto\Commerces;
use App\Dto\_NestedObjects\ContactInfo;
use App\Dto\_NestedObjects\CommerceSchedule;
use App\Entity\Commerce;
use App\Entity\User;
use App\Enum\ReportType;
use App\Enum\UserRank;
use App\Factory\CommerceFactory;
use App\Factory\CommerceReportFactory;
use App\Factory\CommerceScheduleFactory;
use App\Repository\CommerceRepository;
use App\Service\ValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CommerceController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidationService $validation,
        private LoggerInterface $logger,
        private CommerceRepository $commerceRepository,
    ) {}

    #[Route('/commerces', methods: ['GET'], name: 'app_commerce_list')]
    public function list(Request $request): JsonResponse
    {
        // Obtener parametros URL
        $data = $request->query->all();

        // Validación con DTO
        $errors = $this->validation->validate(new Commerces\ListCommerces($data));
        if ($errors) return $errors;

        // Encontrar comercios
        $commerces = $this->commerceRepository->findByFilters($data);

        // Setear rating
        foreach ($commerces as &$commerce) {
            $commerce[0]->setRating($commerce['reviewsCount'],$commerce['positiveCount']);
            $commerce = $commerce[0];
        }

        // Responder
        return $this->json([
            'data' => $commerces
        ], 200, [], ['groups' => ['commerce:list']]);
    }

    #[Route('/commerces/{id}', methods: ['GET'], name: 'app_commerce_get')]
    public function get(int $id): JsonResponse
    {
        // Encontrar comercio
        $commerce = $this->commerceRepository->findOneById($id);
        if (!$commerce[0]) {
            return $this->json([
                'error' => ['message' => 'Comercio no encontrado.']
            ], 404);
        }
        
        // Setear rating
        $commerce[0]->setRating($commerce['reviewsCount'],$commerce['positiveCount']);
        $commerce = $commerce[0];

        // Responder
        return $this->json([
            'data' => $commerce
        ], 200, [], ['groups' => ['commerce:read']]);
    }

    #[Route('/commerces', methods: ['POST'], name: 'app_commerce_post')]
    public function post(Request $request): JsonResponse
    {
        // Control de acceso
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json([
                'error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']
            ], 401);
        }

        // Parseo del request JSON
        $data = json_decode($request->getContent(), true);
        
        // Validación con DTO
        $errors = $this->validation->validate(new Commerces\PostCommerces($data));
        if ($errors) return $errors;
        $errors = $this->validation->validate(new ContactInfo($data['contactInfo']));
        if ($errors) return $errors;
        foreach ($data['commerceSchedules'] as $commerceSchedule) {
            $errors = $this->validation->validate(new CommerceSchedule($commerceSchedule));
            if ($errors) return $errors;
        }

        // Crear comercio, horarios & reportes
        $this->em->beginTransaction();

        $commerceData = [
            'name' => $data['name'],
            'type' => $data['type'],
            'coordsLat' => $data['coordsLat'],
            'coordsLon' => $data['coordsLon'],
            'address' => $data['address'],
            'verified' => false,
            'contactInfo' => $data['contactInfo'],
            'paymentMethods' => $data['paymentMethods'],
        ];

        if (
            $user->getUserRank() === UserRank::PLATINUM || // Usuario con rango platino
            \in_array('ROLE_ADMIN', $user->getRoles()) // Administrador
        ) {
            $commerceData['verified'] = true;
            $commerce = CommerceFactory::createOne($commerceData);
            CommerceReportFactory::createOne([
                'commerce' => $commerce,
                'user' => $user,
                'type' => ReportType::SUBMISSION,
            ]);
            CommerceReportFactory::createOne([
                'commerce' => $commerce,
                'type' => ReportType::VERIFICATION,
            ]);
        } else {
            $commerce = CommerceFactory::createOne($commerceData);
            CommerceReportFactory::createOne([
                'commerce' => $commerce,
                'user' => $user,
                'type' => ReportType::SUBMISSION,
            ]);
        }

        foreach ($data['commerceSchedules'] as &$schedule) {
            CommerceScheduleFactory::createOne([
                'weekday' => $schedule['weekday'],
                'opensAt' => new \DateTimeImmutable($schedule['opensAt']),
                'closesAt' => new \DateTimeImmutable($schedule['closesAt']),
            ]);
        }

        $this->em->flush();
        $this->em->commit();
        
        // Responder
        return $this->json([
            'message' => 'Comercio registrado correctamente.',
            'data' => $commerce,
        ], 201, [], ['groups' => ['commerce:create']]);
    }
}
