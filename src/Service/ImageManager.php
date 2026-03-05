<?php

namespace App\Service;

use App\Entity\Image;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private readonly string $uploadDir,
    ) {}

    public function create(UploadedFile $file, User $user): Image
    {
        if (!str_starts_with($file->getMimeType(), 'image/')) {
            throw new \InvalidArgumentException('Invalid image type.');
        }

        $image = new Image();
        $image->setMimeType($file->getMimeType());
        $image->setUser($user);

        $this->em->persist($image);
        $this->em->flush();

        $filename = $image->getId()->toRfc4122();
        $targetPath = $this->uploadDir . $filename;

        $mime = $file->getMimeType();
        $sourcePath = $file->getPathname();

        switch ($mime) {
            case 'image/jpeg':
                $resource = \imagecreatefromjpeg($sourcePath);
                imagejpeg($resource, $targetPath, 75);
                break;

            case 'image/png':
                $resource = \imagecreatefrompng($sourcePath);
                imagepng($resource, $targetPath, 6);
                break;

            case 'image/webp':
                $resource = \imagecreatefromwebp($sourcePath);
                imagewebp($resource, $targetPath, 75);
                break;

            default:
                throw new \InvalidArgumentException('Unsupported format.');
        }

        return $image;
    }

    public function delete(Image $image): void
    {
        $filename = $image->getId()->toRfc4122();
        $path = $this->uploadDir . $filename;

        if (file_exists($path)) {
            unlink($path);
        }

        $this->em->remove($image);
        $this->em->flush();
    }
}