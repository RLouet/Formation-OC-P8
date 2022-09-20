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
     * @dataProvider routesProvider
     */
    public function testNotConnected(string $route)
    {
        $this->client->request('GET', $route);

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('login');
    }

    /**
     * @dataProvider routesProvider
     */
    public function testForbidden(string $route)
    {
        $this->loginAs('user');

        $this->client->request('GET', $route);

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
    /**
     * @covers ::editAction
     */
    public function testEditNotExisting()
    {
        $this->loginAs('admin');
        $this->client->request('GET', '/tasks/9999999999/edit');

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @covers ::editAction
     * @dataProvider editValidProvider
     */
    public function testEditValid(int $userId, string $username)
    {
        $this->loginAs('admin');
        $crawler = $this->client->request('GET', "/users/$userId/edit");

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('user_edit');
        $title = $crawler->filter('h1:contains("Modifier ")')->first()->html();
        $this->assertSame($title, "Modifier <strong>$username</strong>");
    }

    /**
     * @covers ::editAction
     * @dataProvider editSubmitInvalidProvider
     */
    public function testEditSubmitInvalid(int $userId, array $input, int $errorsCount, array $errorText)
    {
        $this->loginAs('admin');
        $this->client->request('GET', "/users/$userId/edit");
        $crawler = $this->client->submitForm('Modifier', [
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
        $this->assertRouteSame('user_edit', ['id' => $userId]);
        $title = $crawler->filter('h1:contains("Modifier ")')->first()->html();
        $username = $input['username'];
        $this->assertSame($title, "Modifier <strong>$username</strong>");
    }

    /**
     * @covers ::editAction
     * @dataProvider editSubmitValidProvider
     */
    public function testEditSubmitValid(int $userId, string $role)
    {
        $this->loginAs('admin');
        $this->client->request('GET', "/users/$userId/edit");
        $this->client->submitForm('Modifier', [
            'user[username]' => 'MyNewUsername',
            'user[password][first]' => 'MyStrongPassword123',
            'user[password][second]' => 'MyStrongPassword123',
            'user[email]' => 'new@user.com',
            'user[roles]' => 'ROLE_ADMIN',
        ]);

        $this->assertResponseRedirects();
        $crawler = $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('user_list');
        $editedRow = $crawler->filter("a[href=\"/users/$userId/edit\"]")->closest('tr');
        $this->assertCount(1, $editedRow);
        $editedRow = $editedRow->first();

        $columns = $editedRow->filter('td');
        $this->assertSame($columns->getNode(0)->textContent, 'MyNewUsername');
        $this->assertSame($columns->getNode(1)->textContent, "new@user.com");
        $this->assertSelectorExists('div.alert.alert-success:contains("L\'utilisateur a bien été modifié")');
    }

    /**
     * @covers ::editAction
     */
    public function testEditMeBecomeUser()
    {
        $this->loginAs('admin');
        $this->client->request('GET', "/users/1/edit");
        $this->client->submitForm('Modifier', [
            'user[username]' => 'admin',
            'user[password][first]' => 'MyStrongPassword123',
            'user[password][second]' => 'MyStrongPassword123',
            'user[email]' => 'new@user.com',
            'user[roles]' => 'ROLE_USER',
        ]);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('login');
    }

    public function routesProvider(): \Generator
    {
        yield 'list' => ['/users'];
        yield 'create' => ['/users/create'];
        yield 'edit' => ['/users/1/edit'];
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
            4,
            [
                'This value should not be blank.',
                'This value is too short. It should have 3 characters or more.',
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

        yield 'invalid email' => [
            [
                'username' => 'NewUser',
                'password_first' => 'MyValidPassword',
                'password_second' => 'MyValidPassword',
                'email' => 'admin@todolist',
            ],
            1,
            ['This value is not a valid email address.'],
        ];
    }

    public function createSubmitValidProvider(): \Generator
    {
        yield 'user' => ['ROLE_ADMIN'];
        yield 'admin' => ['ROLE_USER'];
    }

    public function editValidProvider(): \Generator
    {
        yield 'user' => [
            2,
            'user',
        ];

        yield 'admin' => [
            1,
            'admin',
        ];

    }

    public function editSubmitInvalidProvider(): \Generator
    {
        yield 'blank' => [
            1,
            [
                'username' => '',
                'password_first' => 'password',
                'password_second' => 'password',
                'email' => '',
            ],
            3,
            [
                'This value should not be blank.',
                'This value is too short. It should have 3 characters or more.',
            ],
        ];

        yield 'too short username' => [
            2,
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
            1,
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
            2,
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
            1,
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
            2,
            [
                'username' => 'NewUser',
                'password_first' => 'MyValidPassword',
                'password_second' => 'MyValidPassword',
                'email' => 'admin@todolist.test',
            ],
            1,
            ['This value is already used.'],
        ];

        yield 'invalid email' => [
            2,
            [
                'username' => 'NewUser',
                'password_first' => 'MyValidPassword',
                'password_second' => 'MyValidPassword',
                'email' => 'admin@todolist',
            ],
            1,
            ['This value is not a valid email address.'],
        ];
    }

    public function editSubmitValidProvider(): \Generator
    {
        yield 'user' => [
            2,
            'ROLE_ADMIN',
        ];

        yield 'admin' => [
            1,
            'ROLE_USER',
        ];

    }
}
