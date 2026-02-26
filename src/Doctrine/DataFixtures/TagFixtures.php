<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class TagFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $tags = [];
        for ($i = 0; $i < 5; ++$i) {
            $tag = new Tag();
            $tag->setName(sprintf('tag+%d', $i));

            $tags[] = $tag;
        }

        array_walk($tags, [$manager, 'persist']);

        $manager->flush();
    }
}
