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
        // Récupérer la liste des tags depuis la BDD
        $entityManager = $this->getEntityManager();

        $tags = $entityManager->getRepository(Tag::class)->findAll();
        $selectedTagIds = array_map(fn (int $value) => $tags[$value]->getId(), $data);

        $videoGameRepository = $this->service(VideoGameRepository::class);
        $videoGames = $videoGameRepository->getVideoGamesByTagIds($selectedTagIds);

        // se connecter à la liste des jeux
        $crawler = $this->get('/');

        // Soumettre le formulaire de filtre avec les données fournies
        $form = $crawler->selectButton('Filtrer')->form();
        $select = $form['filter[tags]'];
        foreach ($data as $value){
            $checkbox = $select[$value];
            $checkbox->tick();
        }
        $this->client->submit($form);

        // Vérifier que le nombre de résultats correspond à ce qui est présent en BDD
        $shouldFilter = $data !== [];
        $expectedResults = $data !== [] ? count($videoGames) : 10;
        self::assertResponseIsSuccessful();
        self::assertSelectorCount($expectedResults, 'article.game-card');

        // ToDo : Trouver comment gerer le submit d'un tag inexistant

    }

    public function provideTagsData(): iterable
    {
        return [
            [[0, 1]],
            [[2]],
            [[0, 2, 3]],
            [[]],
        ];
    }
}
