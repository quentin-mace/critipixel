<?php

namespace App\Tests\Unit\Rating;

use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use PHPUnit\Framework\TestCase;

class RatingTestCase extends TestCase
{
    /**
     * @param array<int, int> $ratings
     */
    protected function createVideoGameWithRatings(array $ratings): VideoGame
    {
        $videoGame = new VideoGame();
        foreach ($ratings as $rating) {
            $review = new Review();
            $review->setRating($rating);
            $videoGame->getReviews()->add($review);
        }

        return $videoGame;
    }
}
