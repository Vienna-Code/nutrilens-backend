<?php

namespace App\Controller;

use App\Repository\ImageRepository;
use App\Service\ImageManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

final class ImageController extends ApiController
{
    public function __construct(
        protected ValidatorInterface $validator,
        private LoggerInterface $logger,

        private ImageRepository $imageRepository,
        private ImageManager $imageManager,
    ) {}

    #[Route('/images/{id}', methods: ['GET'], name: 'app_image_get')]
    public function get(string $id): Response
    {
        // Validación
        $this->validate(
            ['id' => $id],
            new Assert\Collection([
                'fields' => [
                    'id' => [
                        new Assert\NotBlank(),
                        new Assert\Uuid(),
                    ],
                ],
                'allowExtraFields' => true,
            ])
        );

        // Encontrar imagen
        $image = $this->imageRepository->find($id);
        if (!$image) {
            return $this->json([
                'error' => ['message' => 'Imagen no encontrada.']
            ], 404);
        }

        $path = $this->getParameter('kernel.project_dir')
            . '/var/uploads/'
            . $image->getId()->toRfc4122();

        if (!file_exists($path)) {
            return $this->json([
                'error' => ['message' => 'Imagen no encontrada.']
            ], 404);
        }

        // Responder
        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', $image->getMimeType());
        return $response;
    }

    #[Route('/images', methods: ['POST'], name: 'app_image_post')]
    public function post(Request $request): JsonResponse
    {
        // Control de Acceso
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if (!$user) {
            return $this->json([
                'error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']
            ], 401);
        }

        // Obtener archivo
        $file = $request->files->get('file'); /** @var UploadedFile|null $file */
        if (!$file) {
            return $this->json([
                'error' => ['message' => 'Archivo de imagen no encontrado (Subir en form-data bajo key "file")']
            ], 400);
        }

        // Validar archivo
        $allowedMimeTypes = ['image/png', 'image/jpeg', 'image/webp',];
        $mimeType = $file->getMimeType();

        if (!\in_array($mimeType, $allowedMimeTypes, true)) {
            return $this->json([
                'error' => ['message' => 'Tipo de archivo inválido. Solo PNG, JPEG o WEBP.']
            ], 400);
        }

        $info = @getimagesize($file->getPathname());
        if ($info === false) {
            return $this->json([
                'error' => ['message' => 'Archivo de imagen inválido.']
            ], 400);
        }
        if ($file->getSize() > 50_000_000) {
            return $this->json([
                'error' => ['message' => 'Archivo demasiado grande (Máximo de 50MB).']
            ], 400);
        }

        // Procesar y subir imagen
        $image = $this->imageManager->create($file, $user);

        // Responder
        return $this->json([
            'data' => $image
        ], 201, [], ['groups' => ['image:create']]);
    }

    #[Route('/images/{id}', methods: ['DELETE'], name: 'app_image_delete')]
    public function delete(string $id): JsonResponse
    {
        // Control de acceso (SOLO ADMINS)
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if (!$user) {
            return $this->json(['error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']], 401);
        }
        if (!\in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['error' => ['message' => 'No tienes permisos suficientes para aceder a este endpoint.']], 403);
        }

        // Validación
        $this->validate(
            ['id' => $id],
            new Assert\Collection([
                'fields' => [
                    'id' => [
                        new Assert\NotBlank(),
                        new Assert\Uuid(),
                    ],
                ],
                'allowExtraFields' => true,
            ])
        );

        // Encontrar imagen
        $image = $this->imageRepository->find($id);
        if (!$image) {
            return $this->json([
                'error' => ['message' => 'Imagen no encontrada.']
            ], 404);
        }

        // Eliminar imagen
        $this->imageManager->delete($image);

        return $this->json([
            'message' => 'Imagen eliminada.'
        ], 200);
    }
}
