<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Enum\AlimentaryRestriction;
use App\Enum\ReportType;
use App\Factory\CommerceFactory;
use App\Factory\CommerceReportFactory;
use App\Factory\CommerceScheduleFactory;
use App\Factory\ProductFactory;
use App\Factory\ProductRestrictionFactory;
use App\Factory\ReviewFactory;
use App\Factory\UserFactory;
use App\Factory\UserGamificationFactory;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(
        ObjectManager $manager
    ): void {
        // Users
        $admin = UserFactory::createOne([
            'username' => 'viennacode',
            'email' => 'viennacode@gmail.com',
            'verification' => null,
            'roles' => ['ROLE_USER', 'ROLE_ADMIN']
        ]);
        $users = UserFactory::createMany(15, function() {
            return [
                'roles' => ['ROLE_USER'],
                'verification' => null
            ];
        });
        $unverifiedUsers = UserFactory::createMany(2);

        foreach($users as $user) {
            if ($user->getRoles() !== ['ROLE_USER']) continue;

            // UserGamifications
            UserGamificationFactory::createMany(rand(1,6), [
                'user' => $user
            ]);
        }

        // Commerce
        $commerces = CommerceFactory::createMany(20);

        foreach ($commerces as $commerce) {
            // CommerceSchedule
            for ($i = 0 ; $i <= 6 ; $i++) {
                if (rand(1,7) === 1) continue;
                CommerceScheduleFactory::createOne([
                    'commerce' => $commerce,
                    'weekday' => $i
                ]);
            }

            // CommerceReports
            CommerceReportFactory::createOne([
                'commerce' => $commerce,
                'user' => $users[array_rand($users)],
                'type' => ReportType::SUBMISSION,
                'date' => DateTimeImmutable::createFromTimestamp(time()-100),
            ]);
            if ($commerce->isVerified() === false) {
                CommerceReportFactory::createOne([
                    'commerce' => $commerce,
                    'user' => $users[array_rand($users)],
                    'type' => ReportType::REBUTTAL
                ]);
                continue;
            }
            CommerceReportFactory::createOne([
                'commerce' => $commerce,
                'user' => $users[array_rand($users)],
                'type' => ReportType::CONFIRMATION
            ]);
            CommerceReportFactory::createOne([
                'commerce' => $commerce,
                'user' => $admin,
                'type' => ReportType::VERIFICATION
            ]);

            // Products
            $products = ProductFactory::createMany(rand(1,5), [
                'commerce' => $commerce
            ]);
    
            // Product restrictions
            foreach ($products as $product) {
                $restrictions = [];
                $restrictions = array_filter(AlimentaryRestriction::cases(), function() {
                    return rand(1, 4) < 4;
                });
                if (empty($restrictions)) $restrictions = [AlimentaryRestriction::CELIAC];
                foreach ($restrictions as $restriction) {
                    ProductRestrictionFactory::createOne([
                        'product' => $product,
                        'restriction' => $restriction
                    ]);
                }
            }
            
            // Reviews
            $reviews = ReviewFactory::createMany(rand(0,5), [
                'commerce' => $commerce
            ]);
            foreach ($reviews as $review) {
                $commerce->setTotalReviews(totalReviews: $commerce->getTotalReviews() + 1);
                if ($review->isPositive()) {
                    $commerce->setPositiveReviews($commerce->getPositiveReviews() + 1);
                }
            }
            $manager->persist($commerce);
        }
        $manager->flush();
    }
}
