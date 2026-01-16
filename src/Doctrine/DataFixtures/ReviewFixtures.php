<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Review;
use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
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
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)->findAll();
        $videoGames = $manager->getRepository(VideoGame::class)->findBy([], limit: 40);

        $reviews = array_fill_callback(0, 500, fn (int $index): Review => (new Review())
            ->setVideoGame($videoGames[$index % count($videoGames)])
            ->setUser($users[$index % count($users)])
            ->setRating(($index % 5) + 1)
            ->setComment($index % 2 === 0 ? null : $this->faker->paragraphs(1, true))
        );

        array_walk($reviews, [$manager, 'persist']);

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
