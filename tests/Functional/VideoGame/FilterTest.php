<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

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

    public function testShouldFilterVideoGamesByTags(): void
    {
        $data = [0, 2];
        // Récupérer la liste des tags depuis la BDD
        $entityManager = $this->getEntityManager();
        $tags = $entityManager->getRepository(Tag::class)->findAll();
        $tagsIds = array_map(fn (int $value) => $tags[$value]->getId(), $data);

        $videoGames = $entityManager->getRepository(VideoGame::class)->findBy(['tags' => $tagsIds]);
        dd($videoGames);

        // se connecter à la liste des jeux
        $this->get('/');

        // Soumettre le formulaire de filtre avec les données fournies
        $this->client->submitForm('Filtrer', ['filter[tags]' => $tagsIds], 'GET');

        // Vérifier que le nombre de résultats corresponds à ce qui est présent en BDD


    }
}
