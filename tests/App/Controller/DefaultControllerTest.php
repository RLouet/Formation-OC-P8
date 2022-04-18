<?php

namespace App\Tests\App\Controller;

use App\Tests\App\TestCase;

/**
 * @internal
 * @coversDefaultClass \App\Controller\DefaultController
 */
final class DefaultControllerTest extends TestCase
{
    /**
     * @covers ::indexAction
     */
    public function testIndexNotConnected()
    {
        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', "Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !");
        $this->assertSelectorNotExists('a:contains("Se déconnecter")');
        $this->assertSelectorExists('a:contains("Se connecter")');
        $this->assertSelectorNotExists('a:contains("Créer une nouvelle tâche")');
        $this->assertSelectorNotExists('a:contains("Consulter la liste des tâches à faire")');
        $this->assertSelectorNotExists('a:contains("Consulter la liste des tâches terminées")');
        $this->assertSelectorNotExists('a:contains("Gérer les utilisateurs")');
        $this->assertSelectorNotExists('a:contains("Créer un utilisateur")');
    }

    /**
     * @covers ::indexAction
     */
    public function testIndexAsUser()
    {
        $this->loginAs('user');
        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', "Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !");
        $this->assertSelectorExists('a:contains("Se déconnecter")');
        $this->assertSelectorNotExists('a:contains("Se connecter")');
        $this->assertSelectorExists('a:contains("Créer une nouvelle tâche")');
        $this->assertSelectorExists('a:contains("Consulter la liste des tâches à faire")');
        $this->assertSelectorExists('a:contains("Consulter la liste des tâches terminées")');
        $this->assertSelectorNotExists('a:contains("Gérer les utilisateurs")');
        $this->assertSelectorNotExists('a:contains("Créer un utilisateur")');
    }

    /**
     * @covers ::indexAction
     */
    public function testIndexAsAdmin()
    {
        $this->loginAs('admin');
        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', "Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !");
        $this->assertSelectorExists('a:contains("Se déconnecter")');
        $this->assertSelectorNotExists('a:contains("Se connecter")');
        $this->assertSelectorExists('a:contains("Créer une nouvelle tâche")');
        $this->assertSelectorExists('a:contains("Consulter la liste des tâches à faire")');
        $this->assertSelectorExists('a:contains("Consulter la liste des tâches terminées")');
        $this->assertSelectorExists('a:contains("Gérer les utilisateurs")');
        $this->assertSelectorExists('a:contains("Créer un utilisateur")');
    }
}
