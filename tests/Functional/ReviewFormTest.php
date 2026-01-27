<?php

namespace App\Tests\Functional;

use App\Doctrine\Repository\VideoGameRepository;
use App\Model\Entity\Review;
use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ReviewFormTest extends WebTestCase
{
    private ?KernelBrowser $client = null;
    private ?EntityRepository $gamesRepository = null;
    private ?EntityRepository $userRepository = null;
    private ?EntityRepository $reviewRepository = null;
    private ?VideoGame $randomVideoGame = null;
    private ?User $testUser = null;
    private ?Router $urlGenerator = null;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->gamesRepository = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(VideoGame::class);
        $this->userRepository =  $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->reviewRepository =  $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Review::class);

        $videoGames = $this->gamesRepository->findAll();
        $this->randomVideoGame = $videoGames[rand(0, count($videoGames)-1)];

        $this->testUser = $this->userRepository->findOneBy(['email'=>'user+0@email.com']);
        $this->urlGenerator = $this->client->getContainer()->get('router.default');
    }

    public function testReviewFormWithValidData(): void
    {
        // Connecter un utilisateur
        $this->client->loginUser($this->testUser);

        // Supprimer toutes les reviews de cet utilisateur sur un jeu random
        $reviewsToDelete = $this->reviewRepository->findBy(['user'=>$this->testUser, 'videoGame'=>$this->randomVideoGame]);
        foreach ($reviewsToDelete as $reviewToDelete) {
            $this->client->getContainer()->get('doctrine.orm.entity_manager')->remove($reviewToDelete);
            $this->client->getContainer()->get('doctrine.orm.entity_manager')->flush();
        }

        // Se connecter à la page de ce jeu
        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('video_games_show', ['slug' => $this->randomVideoGame->getSlug()]));
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Selectionner le formulaire de note et remplir
        $form = $crawler->selectButton('Poster')->form();
        $form['review[rating]'] = 5;
        $form['review[comment]'] = 'Un commentaire random';

        // Envoyer le formulaire
        $this->client->submit($form);

        // Verifier que l'on est bien redirigé (302)
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $crawler = $this->client->followRedirect();

        // Verifier que la note est bien présente en BDD
        $reviews = $this->reviewRepository->findBy(['user'=>$this->testUser, 'videoGame'=>$this->randomVideoGame]);
        $this->assertCount(1, $reviews);
        $this->assertSame(5, $reviews[0]->getRating());
        $this->assertSame('Un commentaire random', $reviews[0]->getComment());

        // Vérifier que le formulaire d'ajout de note n'est plus disponible
        $this->assertSelectorNotExists('form [name="review"]', 'Poster');
    }

    public function testReviewFormWithInvalidRating(): void
    {
        // Connecter un utilisateur
        $this->client->loginUser($this->testUser);

        // Supprimer toutes les reviews de cet utilisateur sur un jeu random
        $reviewsToDelete = $this->reviewRepository->findBy(['user'=>$this->testUser, 'videoGame'=>$this->randomVideoGame]);
        foreach ($reviewsToDelete as $reviewToDelete) {
            $this->client->getContainer()->get('doctrine.orm.entity_manager')->remove($reviewToDelete);
            $this->client->getContainer()->get('doctrine.orm.entity_manager')->flush();
        }

        // Se connecter à la page de ce jeu
        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('video_games_show', ['slug' => $this->randomVideoGame->getSlug()]));

        // Selectionner le formulaire de note et remplir
        $form = $crawler->selectButton('Poster')->form();
        $form->disableValidation();
        $form['review[rating]'] = 6;
        $form['review[comment]'] = 'Un commentaire random';

        // Envoyer le formulaire
        $this->client->submit($form);

        // Verifier que l'on à bien une erreur de validation (422)
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testReviewFormWithDisconectedUser(): void
    {
        $slug = $this->randomVideoGame->getSlug();

        // Se connecter à la page de ce jeu
        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('video_games_show', ['slug' => $slug]));
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Vérifier que le formulaire d'ajout de note n'est pas disponible
        $this->assertSelectorNotExists('form [name="review"]', 'Poster');

        // Tenter de poster comme si on envoyait le formulaire
        $data = ['review[rating]' => 5, 'review[comment]' => 'Un commentaire random' ];
        $jsonData = json_encode($data);

        $this->client->request(
            Request::METHOD_POST,
            $this->urlGenerator->generate('video_games_show', ['slug' => $slug]),
            content: $jsonData
        ); // ToDo : Finir that

        // Verifier l'obtention d'une 401
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
