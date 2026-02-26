<?php

namespace App\Tests\Unit\Rating;

use App\Rating\RatingHandler;

class CalculateAverageRatingTest extends RatingTestCase
{
    /**
     * @dataProvider ratingsForVideoGame
     *
     * @param array<int> $ratings
     */
    public function testCalculateAverage(array $ratings, ?int $average): void
    {
        $videoGame = $this->createVideoGameWithRatings($ratings);

        $ratingHandler = new RatingHandler();
        $ratingHandler->calculateAverage($videoGame);

        $this->assertEquals($average, $videoGame->getAverageRating());
    }

    /**
     * @return array<array{array<int>, ?int}>
     */
    public function ratingsForVideoGame(): array
    {
        return [
            [
                [1],
                1,
            ],
            [
                [2, 2, 2, 2],
                2,
            ],
            [
                [1, 2, 3, 5, 5],
                4,
            ],
            [
                [],
                null,
            ],
            [
                [1, 1, 1, 5, 5, 5],
                3,
            ],
        ];
    }
}
