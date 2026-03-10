<?php

namespace App\Service;

use App\Entity\Commerce;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\ProductReport;
use App\Entity\ProductRestriction;
use App\Enum\ReportType;
use App\Enum\UserRank;
use App\Enum\AlimentaryRestriction;
use App\Enum\ProductCategory;
use App\Repository\ImageRepository;
use App\Service\GamificationManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class ProductManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private GamificationManager $gm,
        private ImageRepository $imageRepository,
    ) {}

    public function create(array &$data, User &$user, Commerce &$commerce): Product
    {
        $userRank = $user->getUserRank();
        $isAdmin = \in_array('ROLE_ADMIN', $user->getRoles());

        $data['verifiedUser'] =
            $userRank === UserRank::PLATINUM ||
            $userRank === UserRank::GOLD ||
            $isAdmin;

        $data['category'] = ProductCategory::tryFrom($data['category'] ?? null);
        $product = new Product();
        $product->setName($data['name']);
        $product->setBrand($data['brand']);
        $product->setCategory($data['category']);
        $product->setPrice($data['price']);
        $product->setVerified($data['verifiedUser']);
        $commerce->addProduct($product);

        $submissionReport = new ProductReport();
        $submissionReport->setUser($user);
        $submissionReport->setType(ReportType::SUBMISSION);
        $product->addProductReport($submissionReport);

        if ($data['verifiedUser']) {
            $verificationReport = new ProductReport();
            $verificationReport->setType(ReportType::VERIFICATION);
            $product->addProductReport($verificationReport);
        }

        foreach ($data['aptFor'] as $productRestriction) {
            $restriction = new ProductRestriction();
            $restriction->setRestriction(AlimentaryRestriction::tryFrom($productRestriction));
            $product->addAptFor($restriction);
        }

        // Imágenes
        $uuids = $data['images'] ?? [];
        if (!empty($uuids)) {
            $uuidObjects = array_map(
                fn (string $uuid) => Uuid::fromString($uuid),
                $uuids
            );
            $images = $this->imageRepository->findBy([
                'id' => $uuidObjects,
            ]);
            if (\count($images) !== \count($uuids)) {
                throw new \InvalidArgumentException('Una o más imagenes no fueron encontradas.');
            }
            foreach ($images as $image) {
                if ($image->getUser() !== $user && \in_array('ROLE_ADMIN', $user->getRoles())) {
                    throw new \InvalidArgumentException('Imagen de ID ' . $image->getId()->toRfc4122() . ' no fue subida por el usuario.');
                }
            }
            $product->setProductImages($data['images']);
        }

        $this->em->persist($product);
        $this->em->flush();

        return $product;
    }

    public function update(array &$data, Product &$product, User &$user): Product|false
    {
        $userRank = $user->getUserRank();
        $isAdmin = \in_array('ROLE_ADMIN', $user->getRoles());

        // Usuario es bronce
        if ($userRank === UserRank::BRONZE && !$isAdmin) {
            return false;
        }

        $submissionReport = new ProductReport();
        $submissionReport->setUser($user);
        $submissionReport->setType(ReportType::MODIFICATION);

        $product->setPrice($data['price'] ?? $product->getPrice());
        if (isset($data['aptFor'])) {
            foreach ($product->getAptFor() as $pr) {
                $product->removeAptFor($pr);
            }
            foreach ($data['aptFor'] as &$restrictionData) {
                $productRestriction = new ProductRestriction();
                $productRestriction->setRestriction(AlimentaryRestriction::tryFrom($restrictionData));
                $product->addAptFor($productRestriction);
            }
        }

        // Imágenes
        if (isset($data['images'])) {
            $incoming = $data['images'] ?? [];
            $current  = $product->getProductImages() ?? [];
            $isAdmin  = \in_array('ROLE_ADMIN', $user->getRoles(), true);
            $toAdd    = array_diff($incoming, $current);
            $toRemove = array_diff($current, $incoming);

            if (!$isAdmin && (!empty($toAdd) || !empty($toRemove))) {
                $affectedUuids = array_unique(\array_merge($toAdd, $toRemove));
                $uuidObjects = array_map(
                    fn (string $uuid) => Uuid::fromString($uuid),
                    $affectedUuids
                );
                $images = $this->imageRepository->findBy([
                    'id' => $uuidObjects,
                ]);
                
                if (\count($images) !== \count($affectedUuids)) {
                    throw new \InvalidArgumentException('Una o más imagenes no fueron encontradas.');
                }
            }

            $product->setProductImages(array_values(array_unique($incoming)));
        }

        // Funciones solo para admin
        if ($isAdmin) {
            $data['category'] = ProductCategory::tryFrom($data['category'] ?? null);
            $product->setName($data['name'] ?? $product->getName());
            $product->setBrand($data['brand'] ?? $product->getBrand());
            $product->setCategory($data['category'] ?? $product->getCategory());
            $product->setPrice($data['price'] ?? $product->getPrice());
        }

        // Verificacion del producto
        if (
            isset($data['verified']) &&
            ($userRank === UserRank::PLATINUM || $userRank === UserRank::GOLD || $isAdmin) &&
            $product->isVerified() !== $data['verified']
        ) {
            $product->setVerified($data['verified']);
            if ($data['verified']) {
                $submissionReport->setType(ReportType::VERIFICATION);
            } else {
                $submissionReport->setType(ReportType::UNVERIFICATION);
            }
        }

        $product->addProductReport($submissionReport);
        if ($submissionReport->getType() === ReportType::VERIFICATION) {
            $this->gm->verifyProduct($product);
        }
        
        $this->em->persist($product);
        $this->em->flush();

        return $product;
    }

    public function delete(Product $product): void
    {
        $this->em->remove($product);
        $this->em->flush();
    }
}