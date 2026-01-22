<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReviewFormTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Hello World');

        // Connecter un utilisateur

        // Se connecter à la page d'un jeu random

        // Supprimer toutes les reviews de cet utilisateur sur ce jeu

        // Selectionner le formulaire de note et remplir

        // Envoyer le formulaire

        // Verifier que l'on est bien redirigé (302)

        // Verifier que la note est bien présente en BDD

        // Vérifier que le formulaire d'ajout de note n'est plus disponible
    }
}
