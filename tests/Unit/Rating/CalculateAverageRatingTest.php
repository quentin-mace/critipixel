<?php

namespace App\Tests\Unit\Rating;

use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Rating\CalculateAverageRating;
use App\Rating\RatingHandler;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Collections\Collection;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class CalculateAverageRatingTest extends RatingTestCase
{
    /**
     * @dataProvider ratingsForVideoGame
     */
    public function testCalculateAverage(array $ratings, ?int $average): void
    {
        $videoGame = $this->createVideoGameWithRatings($ratings);

        $ratingHandler = new RatingHandler();
        $ratingHandler->calculateAverage($videoGame);

        $this->assertEquals($average, $videoGame->getAverageRating());
    }

    public function ratingsForVideoGame(): array
    {
        return [
            [
                [1],
                1
            ],
            [
                [2,2,2,2],
                2
            ],
            [
                [1,2,3,5,5],
                4
            ],
            [
                [],
                null
            ],
            [
                [1,1,1,5,5,5],
                3
            ]
        ];
    }
}
