<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Enum\AlimentaryRestriction;
use App\Enum\ReportType;
use App\Factory\CommentFactory;
use App\Factory\CommerceFactory;
use App\Factory\CommerceReportFactory;
use App\Factory\CommerceScheduleFactory;
use App\Factory\PostFactory;
use App\Factory\PostVoteFactory;
use App\Factory\ProductFactory;
use App\Factory\ProductReportFactory;
use App\Factory\ProductRestrictionFactory;
use App\Factory\ReviewFactory;
use App\Factory\ReviewVoteFactory;
use App\Factory\TagFactory;
use App\Factory\UserFactory;
use App\Factory\UserGamificationFactory;
use App\Service\GamificationManager;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function __construct(
        private GamificationManager $gm,
        private EntityManagerInterface $em,
    ) {}

    public function load(
        ObjectManager $manager
    ): void {
        ini_set('memory_limit', '8G');

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
            if ($commerce->isVerified() === false) {
                CommerceReportFactory::createOne([
                    'commerce' => $commerce,
                    'user' => $users[array_rand($users)],
                    'type' => ReportType::SUBMISSION,
                    'date' => DateTimeImmutable::createFromTimestamp(time()-7200)
                ]);
                CommerceReportFactory::createOne([
                    'commerce' => $commerce,
                    'user' => $users[array_rand($users)],
                    'type' => ReportType::REBUTTAL,
                    'date' => new DateTimeImmutable()
                ]);
                continue;
            } else {
                CommerceReportFactory::createOne([
                    'commerce' => $commerce,
                    'user' => $users[array_rand($users)],
                    'type' => ReportType::SUBMISSION,
                    'date' => DateTimeImmutable::createFromTimestamp(time()-7200),
                    'resolved' => true,
                ]);
                CommerceReportFactory::createOne([
                    'commerce' => $commerce,
                    'user' => $users[array_rand($users)],
                    'type' => ReportType::CONFIRMATION,
                    'date' => DateTimeImmutable::createFromTimestamp(time()-3600),
                    'resolved' => true,
                ]);
                CommerceReportFactory::createOne([
                    'commerce' => $commerce,
                    'user' => $admin,
                    'type' => ReportType::VERIFICATION,
                    'date' => new DateTimeImmutable(),
                    'resolved' => true,
                ]);
                $this->gm->verifyCommerce($commerce, true);
            }

            // Products
            $products = ProductFactory::createMany(rand(1,5), [
                'commerce' => $commerce
            ]);
    
            foreach ($products as $product) {
                // Product restrictions
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

                // ProductReports
                if ($product->isVerified() === false) {
                    ProductReportFactory::createOne([
                        'product' => $product,
                        'user' => $users[array_rand($users)],
                        'type' => ReportType::SUBMISSION,
                        'date' => DateTimeImmutable::createFromTimestamp(time()-7200),
                    ]);
                    ProductReportFactory::createOne([
                        'product' => $product,
                        'user' => $users[array_rand($users)],
                        'type' => ReportType::REBUTTAL,
                        'date' => new DateTimeImmutable(),
                    ]);
                    continue;
                } else {
                    ProductReportFactory::createOne([
                        'product' => $product,
                        'user' => $users[array_rand($users)],
                        'type' => ReportType::SUBMISSION,
                        'date' => DateTimeImmutable::createFromTimestamp(time()-7200),
                        'resolved' => true,
                    ]);
                    ProductReportFactory::createOne([
                        'product' => $product,
                        'user' => $users[array_rand($users)],
                        'type' => ReportType::CONFIRMATION,
                        'date' => DateTimeImmutable::createFromTimestamp(time()-3600),
                        'resolved' => true,
                    ]);
                    ProductReportFactory::createOne([
                        'product' => $product,
                        'user' => $admin,
                        'type' => ReportType::VERIFICATION,
                        'date' => new DateTimeImmutable(),
                        'resolved' => true,
                    ]);
                    $this->gm->verifyProduct($product, true);
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

        // Tags
        $tags1 = [
            TagFactory::createOne(['name' => 'receta']),
            TagFactory::createOne(['name' => 'pregunta']),
            TagFactory::createOne(['name' => 'experiencia']),
            TagFactory::createOne(['name' => 'consejo']),
        ];

        $tags2 = [
            TagFactory::createOne(['name' => 'celiaco']),
            TagFactory::createOne(['name' => 'diabetico']),
            TagFactory::createOne(['name' => 'hipertension']),
        ];

        // Posts
        $posts = PostFactory::createMany(20, function() use ($users, $tags1, $tags2) {
            return [
                'user' => $users[array_rand($users)],
                'tags' => [
                    $tags1[array_rand($tags1)],
                    $tags2[array_rand($tags2)],
                ],
            ];
        });

        // Comments
        foreach ($posts as $post) {
            $comments = CommentFactory::createMany(rand(0, 5), [
                'user' => $users[array_rand($users)],
                'post' => $post,
            ]);

            foreach ($comments as $comment) {
                CommentFactory::createMany(rand(0, 2), [
                    'user' => $users[array_rand($users)],
                    'post' => $post,
                    'replyingTo' => $comment,
                ]);
            }
        }

        // Votes
        for ($i = 0 ; $i < 100 ; $i++) {
            $pvote = PostVoteFactory::createOne();
            $post = $pvote->getPost();
            $post->setUpvotes($post->getUpvotes() + match ($pvote->isPositive()) {
                true => 1,
                null => 0,
                false => -1,
            });
            $manager->persist($post);

            $rvote = ReviewVoteFactory::createOne();
            $review = $rvote->getReview();
            $review->setUseful($review->getUseful() + match ($rvote->isPositive()) {
                true => 1,
                null => 0,
                false => -1,
            });
            $manager->persist($review);
        }

        $manager->flush();
    }
}
