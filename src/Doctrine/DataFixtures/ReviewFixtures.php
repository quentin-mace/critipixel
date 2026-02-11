<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Review;
use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use App\Rating\CalculateAverageRating;
use App\Rating\CountRatingsPerValue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

final class ReviewFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly Generator $faker,
        private readonly CalculateAverageRating $calculateAverageRating,
        private readonly CountRatingsPerValue $countRatingsPerValue,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)->findAll();
        $videoGames = $manager->getRepository(VideoGame::class)->findBy([], limit: 40);

        $reviews = [];
        for ($i = 0; $i < 500; ++$i) {
            $review = new Review();
            $review
                ->setVideoGame($videoGames[rand(0, count($videoGames) - 1)])
                ->setUser($users[rand(0, count($users) - 1)])
                ->setRating(rand(1, 5))
                ->setComment(0 === $i % 2 ? null : $this->faker->paragraphs(1, true));

            $reviews[] = $review;
        }

        array_walk($reviews, [$manager, 'persist']);

        $manager->flush();

        $videoGames = $manager->getRepository(VideoGame::class)->findAll();

        foreach ($videoGames as $videoGame) {
            $this->calculateAverageRating->calculateAverage($videoGame);
            $this->countRatingsPerValue->countRatingsPerValue($videoGame);
        }

        array_walk($videoGames, [$manager, 'persist']);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            VideoGameFixtures::class,
        ];
    }
}
