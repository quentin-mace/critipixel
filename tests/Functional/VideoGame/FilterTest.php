<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Doctrine\Repository\VideoGameRepository;
use App\Model\Entity\Tag;
use App\Model\Entity\VideoGame;
use App\Tests\Functional\FunctionalTestCase;

final class FilterTest extends FunctionalTestCase
{
    public function testShouldListTenVideoGames(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        $this->client->clickLink('2');
        self::assertResponseIsSuccessful();
    }

    public function testShouldFilterVideoGamesBySearch(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        $this->client->submitForm('Filtrer', ['filter[search]' => 'Jeu vidéo 49'], 'GET');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(1, 'article.game-card');
    }

    /**
     * @dataProvider provideTagsData
     */
    public function testShouldFilterVideoGamesByTags(array $data = []): void
    {
        // Récupérer les JV avec ces tags
        $entityManager = $this->getEntityManager();
        $videoGameRepository = $this->service(VideoGameRepository::class);

        $tags = $entityManager->getRepository(Tag::class)->findAll();
        $tagCount = count($tags);
        foreach ($data as $key => $value){
            if ($value < 0 || $value >= $tagCount){
                unset($data[$key]);
            }
        }

        $selectedTagIds = array_map(fn (int $value) => $tags[$value]->getId(), $data);
        $videoGames = $videoGameRepository->getVideoGamesByTagIds($selectedTagIds);

        // se connecter à la liste des jeux
        $crawler = $this->get('/');

        // Soumettre le formulaire de filtre avec les données fournies
        $form = $crawler->selectButton('Filtrer')->form();
        $this->tickCheckboxes($form, 'filter[tags]', $data);
        $this->client->submit($form);

        // Vérifier que le nombre de résultats correspond à ce qui est présent en BDD
        $shouldFilter = $data !== [];
        $expectedResults = $shouldFilter ? count($videoGames) : 10;
        self::assertResponseIsSuccessful();
        self::assertSelectorCount($expectedResults, 'article.game-card');
    }

    public function provideTagsData(): iterable
    {
        return [
            [[0, 1]],
            [[2]],
            [[0, 2, 3]],
            [[7]],
            [[]]
        ];
    }
}
