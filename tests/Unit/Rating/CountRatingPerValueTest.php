<?php

namespace App\Tests\Unit\Rating;

use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Rating\RatingHandler;
use PHPUnit\Framework\TestCase;

class CountRatingPerValueTest extends RatingTestCase
{
    /**
     * @dataProvider ratingsForCountRating
     */
    public function testCountRatingsPerValue(array $ratings, array $expectedValues): void
    {
        $videoGame = $this->createVideoGameWithRatings($ratings);

        $ratingHandler = new RatingHandler();
        $ratingHandler->countRatingsPerValue($videoGame);

        $this->assertEquals($expectedValues[0], $videoGame->getNumberOfRatingsPerValue()->getNumberOfOne());
        $this->assertEquals($expectedValues[1], $videoGame->getNumberOfRatingsPerValue()->getNumberOfTwo());
        $this->assertEquals($expectedValues[2], $videoGame->getNumberOfRatingsPerValue()->getNumberOfThree());
        $this->assertEquals($expectedValues[3], $videoGame->getNumberOfRatingsPerValue()->getNumberOfFour());
        $this->assertEquals($expectedValues[4], $videoGame->getNumberOfRatingsPerValue()->getNumberOfFive());
    }

    public function ratingsForCountRating(): array
    {
        return [
            [
                [1],
                [1,0,0,0,0]
            ],
            [
                [2,2,2,2],
                [0,4,0,0,0]
            ],
            [
                [1,2,3,4,5],
                [1,1,1,1,1]
            ],
            [
                [],
                [0,0,0,0,0]
            ],
            [
                [1,1,1,5,5,5],
                [3,0,0,0,3]
            ]
        ];
    }
}
