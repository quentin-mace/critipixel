<?php

namespace App\Tests\Unit\Rating;

use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Rating\RatingHandler;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Collections\Collection;

class CalculateAverageRatingTest extends TestCase
{
    /**
     * @dataProvider ratingsForVideoGame
     */
    public function testCalculateAverage($array, $average): void
    {
        $videoGame = new VideoGame();
        foreach ($array as $value) {
            $review = $this
                ->getMockBuilder(Review::class)
                ->disableOriginalConstructor()
                ->getMock();
            $review->method('getRating')->willReturn($value);
            $videoGame->getReviews()->add($review);
        }

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
