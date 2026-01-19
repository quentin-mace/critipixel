<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Tag;
use App\Model\Entity\VideoGame;
use App\Rating\CalculateAverageRating;
use App\Rating\CountRatingsPerValue;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

use function array_fill_callback;

final class VideoGameFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly Generator $faker,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $tags = $manager->getRepository(Tag::class)->findAll();

        $videoGames = array_fill_callback(0, 50, fn (int $index): VideoGame => (new VideoGame)
            ->setTitle(sprintf('Jeu vidéo %d', $index))
            ->setDescription($this->faker->paragraphs(10, true))
            ->setReleaseDate(new DateTimeImmutable())
            ->setTest($this->faker->paragraphs(6, true))
            ->setRating(rand(1,5))
            ->setImageName(sprintf('video_game_%d.png', $index))
            ->setImageSize(2_098_872)
            ->addTag($index % 2 === 0 ? $tags[rand(0, count($tags)-1)] : null)
            ->addTag($index % 3 === 0 ? $tags[rand(0, count($tags)-1)] : null)
        );

        array_walk($videoGames, [$manager, 'persist']);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TagFixtures::class,
        ];
    }
}
