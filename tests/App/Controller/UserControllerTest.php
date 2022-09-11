<?php

namespace App\Tests\App\Controller;

use App\Tests\App\TestCase;

/**
 * @internal
 * @coversDefaultClass \App\Controller\UserController
 */
final class UserControllerTest extends TestCase
{
    /**
     * @covers ::listAction
     */
    public function testListNotConnected()
    {
        $this->client->request('GET', '/users');

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('login');
    }

    /**
     * @covers ::listAction
     */
    public function testListForbidden()
    {
        $this->loginAs('user');

        $this->client->request('GET', '/users');

        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * @covers ::listAction
     */
    public function testListValid()
    {
        $this->loginAs('admin');
        $this->client->request('GET', '/users');

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('user_list');
    }

    /**
     * @covers ::createAction
     */
    public function testCreateValid()
    {
        $this->loginAs('admin');
        $this->client->request('GET', '/users/create');

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('user_create');
        $this->assertSelectorExists('h1:contains("Créer un utilisateur")');
    }

    /**
     * @covers ::createAction
     * @dataProvider createSubmitInvalidProvider
     */
    public function testCreateSubmitInvalid(array $input, int $errorsCount, array $errorText)
    {
        $this->loginAs('admin');
        $this->client->request('GET', '/users/create');
        $crawler = $this->client->submitForm('Ajouter', [
            'user[username]' => $input['username'],
            'user[password][first]' => $input['password_first'],
            'user[password][second]' => $input['password_second'],
            'user[email]' => $input['email'],
            'user[roles]' => 'ROLE_USER',
        ]);

        $this->assertResponseIsSuccessful();

        $formErrors = $crawler->filter('.invalid-feedback');

        $this->assertCount($errorsCount, $formErrors);

        foreach ($formErrors as $formError) {
            $this->assertContainsEquals($formError->textContent, $errorText);
        }

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('user_create');
    }

    /**
     * @covers ::createAction
     * @dataProvider createSubmitValidProvider
     */
    public function testCreateSubmitValid(string $role)
    {
        $this->loginAs('admin');
        $this->client->request('GET', '/users/create');
        $this->client->submitForm('Ajouter', [
            'user[username]' => 'NewUser',
            'user[password][first]' => 'MyValidPassword',
            'user[password][second]' => 'MyValidPassword',
            'user[email]' => 'newuser@todolist.com',
            'user[roles]' => $role,
        ]);

        $this->assertResponseRedirects();
        $crawler = $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('user_list');
        $addedRow = $crawler->filter('td:contains("newuser@todolist.com")')->closest('tr');
        $this->assertCount(1, $addedRow);
        $this->assertSelectorExists('div.alert.alert-success:contains("L\'utilisateur a bien été ajouté.")');
    }

    public function createSubmitInvalidProvider(): \Generator
    {
        yield 'blank' => [
            [
                'username' => '',
                'password_first' => '',
                'password_second' => '',
                'email' => '',
            ],
            3,
            [
                'This value should not be blank.',
            ],
        ];

        yield 'too short username' => [
            [
                'username' => 'aa',
                'password_first' => 'MyValidPassword',
                'password_second' => 'MyValidPassword',
                'email' => 'newuser@todolist.com',
            ],
            1,
            ['This value is too short. It should have 3 characters or more.'],
        ];

        yield 'too short password' => [
            [
                'username' => 'NewUser',
                'password_first' => 'Short12',
                'password_second' => 'Short12',
                'email' => 'newuser@todolist.com',
            ],
            1,
            ['This value is too short. It should have 8 characters or more.'],
        ];

        yield 'not same password' => [
            [
                'username' => 'NewUser',
                'password_first' => 'MyValidPassword',
                'password_second' => 'MyValidPasword',
                'email' => 'newuser@todolist.com',
            ],
            1,
            ['Les deux mots de passe doivent correspondre.'],
        ];

        yield 'existing username' => [
            [
                'username' => 'user',
                'password_first' => 'MyValidPassword',
                'password_second' => 'MyValidPassword',
                'email' => 'newuser@todolist.com',
            ],
            1,
            ['This value is already used.'],
        ];

        yield 'existing email' => [
            [
                'username' => 'NewUser',
                'password_first' => 'MyValidPassword',
                'password_second' => 'MyValidPassword',
                'email' => 'admin@todolist.test',
            ],
            1,
            ['This value is already used.'],
        ];
    }

    public function createSubmitValidProvider(): \Generator
    {
        yield 'user' => ['ROLE_ADMIN'];
        yield 'admin' => ['ROLE_USER'];
    }
}
