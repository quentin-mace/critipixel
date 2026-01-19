<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Review;
use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use App\Rating\CalculateAverageRating;
use App\Rating\CountRatingsPerValue;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

use function array_fill_callback;

final class ReviewFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly Generator $faker,
        private readonly CalculateAverageRating $calculateAverageRating,
        private readonly CountRatingsPerValue $countRatingsPerValue
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)->findAll();
        $videoGames = $manager->getRepository(VideoGame::class)->findBy([], limit: 40);

        $reviews = array_fill_callback(0, 500, fn (int $index): Review => (new Review())
            ->setVideoGame($videoGames[rand(0,count($videoGames)-1)])
            ->setUser($users[rand(0, count($users)-1)])
            ->setRating(rand(1, 5))
            ->setComment($index % 2 === 0 ? null : $this->faker->paragraphs(1, true))
        );

        array_walk($reviews, [$manager, 'persist']);

        $manager->flush();

        $videoGames = $manager->getRepository(VideoGame::class)->findBy([], limit: 40);

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
