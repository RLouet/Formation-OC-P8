<?php

namespace App\Tests\App\Controller;

use App\Tests\App\TestCase;

/**
 * @internal
 * @coversDefaultClass \App\Controller\TaskController
 */
final class TaskControllerTest extends TestCase
{
    /**
     * @covers ::listAction
     */
    public function testListNotConnected()
    {
        $this->client->request('GET', '/tasks');

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('login');
    }

    /**
     * @covers ::listAction
     */
    public function testListValid()
    {
        $this->loginAs('user');
        $this->client->request('GET', '/tasks');

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('task_list');
    }

    /**
     * @covers ::listAction
     */
    public function testListDone()
    {
        $this->loginAs('user');
        $this->client->request('GET', '/tasks/done');

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('task_list');
        $this->assertSelectorExists('a:contains("DONE Task")');
        $this->assertSelectorExists('a:contains("Anonymous DONE Task")');
        $this->assertSelectorNotExists('a:contains("TODO Task")');
    }

    /**
     * @covers ::listAction
     */
    public function testListTodo()
    {
        $this->loginAs('user');
        $this->client->request('GET', '/tasks/todo');

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('task_list');
        $this->assertSelectorNotExists('a:contains("DONE Task")');
        $this->assertSelectorNotExists('a:contains("Anonymous DONE Task")');
        $this->assertSelectorExists('a:contains("TODO Task")');
    }

    /**
     * @covers ::createAction
     */
    public function testCreateNotConnected()
    {
        $this->client->request('GET', '/tasks/create');

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('login');
    }

    /**
     * @covers ::createAction
     * @dataProvider createValidProvider
     */
    public function testCreateValid(string $user)
    {
        $this->loginAs($user);
        $this->client->request('GET', '/tasks/create');

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('task_create');
        $this->assertSelectorExists('span:contains("Auteur : '.$user.'")');
    }

    /**
     * @covers ::createAction
     * @dataProvider createSubmitInvalidProvider
     */
    public function testCreateSubmitInvalid(string $user, string $input, int $errorsCount, array $errorText)
    {
        $this->loginAs($user);
        $this->client->request('GET', '/tasks/create');
        $crawler = $this->client->submitForm('Ajouter', [
            'task[title]' => $input,
            'task[content]' => $input,
        ]);

        $formErrors = $crawler->filter('.invalid-feedback');

        $this->assertCount($errorsCount, $formErrors);

        foreach ($formErrors as $formError) {
            $this->assertContainsEquals($formError->textContent, $errorText);
        }

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('task_create');
    }

    /**
     * @covers ::createAction
     * @dataProvider createValidProvider
     */
    public function testCreateSubmitValid(string $user)
    {
        $this->loginAs($user);
        $this->client->request('GET', '/tasks/create');
        $this->client->submitForm('Ajouter', [
            'task[title]' => 'New valid task title',
            'task[content]' => 'New valid task content',
        ]);

        $this->assertResponseRedirects();
        $crawler = $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('task_list');
        $editedDiv = $crawler->filter('a:contains("New valid task title")')->closest('article');
        $this->assertCount(1, $editedDiv);
        $editedDiv = $editedDiv->first();

        $this->assertSame($editedDiv->filter('h6')->innerText(), "Auteur : $user");
        $this->assertSame($editedDiv->filter('p')->innerText(), "New valid task content");
        $this->assertSelectorExists('div.alert.alert-success:contains("La tâche a été bien été ajoutée.")');
    }

    /**
     * @covers ::editAction
     */
    public function testEditNotConnected()
    {
        $this->client->request('GET', '/tasks/2/edit');

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('login');
    }

    /**
     * @covers ::editAction
     */
    public function testEditNotExisting()
    {
        $this->loginAs("admin");
        $this->client->request('GET', '/tasks/9999999999/edit');

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @covers ::editAction
     * @dataProvider editValidProvider
     */
    public function testEditValid(string $user, int $taskId, string $author)
    {
        $this->loginAs($user);
        $crawler = $this->client->request('GET', "/tasks/$taskId/edit");

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('task_edit');
        $authorText = $crawler->filter('span.h6.text-muted:contains(\'Auteur :\')')->first()->innerText();
        $this->assertSame($authorText, "Auteur : $author");
    }

    /**
     * @covers ::editAction
     * @dataProvider editSubmitInvalidProvider
     */
    public function testEditSubmitInvalid(string $user, int $taskId, string $input, int $errorsCount, array $errorText, string $author)
    {
        $this->loginAs($user);
        $this->client->request('GET', "/tasks/$taskId/edit");
        $crawler = $this->client->submitForm('Modifier', [
            'task[title]' => $input,
            'task[content]' => $input,
        ]);

        $formErrors = $crawler->filter('.invalid-feedback');

        $this->assertCount($errorsCount, $formErrors);

        foreach ($formErrors as $formError) {
            $this->assertContainsEquals($formError->textContent, $errorText);
        }

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('task_edit', ['id' => $taskId]);
        $authorText = $crawler->filter('span.h6.text-muted:contains(\'Auteur :\')')->first()->innerText();
        $this->assertSame($authorText, "Auteur : $author");
    }

    /**
     * @covers ::editAction
     * @dataProvider editValidProvider
     */
    public function testEditSubmitValid(string $user, int $taskId, string $author)
    {
        $this->loginAs($user);
        $this->client->request('GET', "/tasks/$taskId/edit");
        $this->client->submitForm('Modifier', [
            'task[title]' => 'Edited valid task title',
            'task[content]' => 'Edited valid task content',
        ]);

        $this->assertResponseRedirects();
        $crawler = $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('task_list');
        $editedDiv = $crawler->filter("a[href=\"/tasks/$taskId/edit\"]")->closest('article');
        $this->assertCount(1, $editedDiv);
        $editedDiv = $editedDiv->first();

        $this->assertSame($editedDiv->filter('a')->innerText(), 'Edited valid task title');
        $this->assertSame($editedDiv->filter('h6')->innerText(), "Auteur : $author");
        $this->assertSame($editedDiv->filter('p')->innerText(), 'Edited valid task content');
        $this->assertSelectorExists('div.alert.alert-success:contains("La tâche a bien été modifiée.")');
    }

    /**
     * @covers ::toggleTaskAction
     */
    public function testToggleTaskNotConnected()
    {
        $this->client->request('GET', '/tasks/2/toggle');

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('login');
    }

    /**
     * @covers ::toggleTaskAction
     */
    public function testToggleTaskNotExisting()
    {
        $this->loginAs('admin');
        $this->client->request('GET', '/tasks/9999999999/toggle');

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @covers ::toggleTaskAction
     * @dataProvider toggleTaskValidProvider
     */
    public function testToggleTaskValid(string $user, int $taskId, string $taskName, bool $done)
    {
        $this->loginAs($user);
        $this->client->request('GET', "/tasks/$taskId/toggle");

        $this->assertResponseRedirects();
        $crawler = $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('task_list');
        $editedDivToggleForm = $crawler->filter("form[action=\"/tasks/$taskId/toggle\"]");
        $editedDiv = $editedDivToggleForm->closest('article');
        $this->assertCount(1, $editedDiv);
        $editedDiv = $editedDiv->first();
        $statusPicto = $editedDiv->filter('i.bi');
        $toggleButton =  $editedDivToggleForm->filter('button');

        if ($done) {
            $this->assertSame('bi bi-x-lg', $statusPicto->attr('class'));
            $this->assertSame('Marquer comme faite', $toggleButton->innerText());
            $this->assertSelectorExists("div.alert.alert-success:contains(\"La tâche $taskName a bien été marquée comme non terminée.\")");
        } else {
            $this->assertSame('bi bi-check-lg', $statusPicto->attr('class'));
            $this->assertSame('Marquer non terminée', $toggleButton->innerText());
            $this->assertSelectorExists("div.alert.alert-success:contains(\"La tâche $taskName a bien été marquée comme faite.\")");
        }
    }

    /**
     * @covers ::deleteTaskAction
     */
    public function testDeleteTaskNotConnected()
    {
        $this->client->request('GET', '/tasks/3/delete');

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('login');
    }

    /**
     * @covers ::deleteTaskAction
     * @dataProvider deleteTaskNotExistingProvider
     */
    public function testDeleteTaskNotExisting(string $user)
    {
        $this->loginAs($user);
        $this->client->request('GET', '/tasks/9999999999/delete');

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @covers ::deleteTaskAction
     * @dataProvider deleteTaskForbiddenProvider
     */
    public function testDeleteTaskForbidden(string $user, int $taskId)
    {
        $this->loginAs($user);
        $this->client->request('GET', "/tasks/$taskId/delete");

        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * @covers ::deleteTaskAction
     * @dataProvider deleteTaskValidProvider
     */
    public function testDeleteTaskValid(string $user, int $taskId)
    {
        $this->loginAs($user);
        $this->client->request('GET', "/tasks/$taskId/delete");

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('task_list');
        $this->assertSelectorNotExists("a[href=\"/tasks/$taskId/edit\"]");
        $this->assertSelectorExists('div.alert.alert-success:contains("La tâche a bien été supprimée.")');
    }

    public function createValidProvider(): \Generator
    {
        yield 'As user' => [
            'user',
        ];

        yield 'As admin' => [
            'admin',
        ];
    }

    public function createSubmitInvalidProvider(): \Generator
    {
        yield 'As user blank' => [
            'user',
            '',
            4,
            [
                'This value should not be blank.',
                'This value is too short. It should have 3 characters or more.',
            ],
        ];

        yield 'As admin blank' => [
            'admin',
            '',
            4,
            [
                'This value should not be blank.',
                'This value is too short. It should have 3 characters or more.',
            ],
        ];

        yield 'As user too short' => [
            'user',
            'aa',
            2,
            ['This value is too short. It should have 3 characters or more.'],
        ];

        yield 'As admin too short' => [
            'admin',
            'aa',
            2,
            ['This value is too short. It should have 3 characters or more.'],
        ];
    }

    public function editValidProvider(): \Generator
    {
        yield 'As user - admin task' => [
            'user',
            2,
            'admin',
        ];

        yield 'As admin - user task' => [
            'admin',
            1,
            'user',
        ];

        yield 'As user - user task' => [
            'user',
            1,
            'user',
        ];

        yield 'As admin - admin task' => [
            'admin',
            2,
            'admin',
        ];

        yield 'As user - anonymous task' => [
            'user',
            3,
            '[Anonyme]',
        ];

        yield 'As admin - anonymous task' => [
            'admin',
            3,
            '[Anonyme]',
        ];
    }

    public function editSubmitInvalidProvider(): \Generator
    {
        yield 'As user - admin task - blank' => [
            'user',
            2,
            '',
            4,
            [
                'This value should not be blank.',
                'This value is too short. It should have 3 characters or more.',
            ],
            'admin',
        ];

        yield 'As admin - user task - blank' => [
            'admin',
            1,
            '',
            4,
            [
                'This value should not be blank.',
                'This value is too short. It should have 3 characters or more.',
            ],
            'user',
        ];

        yield 'As user - user task - blank' => [
            'user',
            1,
            '',
            4,
            [
                'This value should not be blank.',
                'This value is too short. It should have 3 characters or more.',
            ],
            'user',
        ];

        yield 'As admin - admin task - blank' => [
            'admin',
            2,
            '',
            4,
            [
                'This value should not be blank.',
                'This value is too short. It should have 3 characters or more.',
            ],
            'admin',
        ];

        yield 'As user - anonymous task - blank' => [
            'user',
            3,
            '',
            4,
            [
                'This value should not be blank.',
                'This value is too short. It should have 3 characters or more.',
            ],
            '[Anonyme]',
        ];

        yield 'As admin - anonymous task - blank' => [
            'admin',
            3,
            '',
            4,
            [
                'This value should not be blank.',
                'This value is too short. It should have 3 characters or more.',
            ],
            '[Anonyme]',
        ];

        yield 'As user - admin task - too short' => [
            'user',
            2,
            'aa',
            2,
            ['This value is too short. It should have 3 characters or more.'],
            'admin',
        ];

        yield 'As admin - user task - too short' => [
            'admin',
            1,
            'aa',
            2,
            ['This value is too short. It should have 3 characters or more.'],
            'user',
        ];

        yield 'As user - user task - too short' => [
            'user',
            1,
            'aa',
            2,
            ['This value is too short. It should have 3 characters or more.'],
            'user',
        ];

        yield 'As admin - admin task - too short' => [
            'admin',
            2,
            'aa',
            2,
            ['This value is too short. It should have 3 characters or more.'],
            'admin',
        ];

        yield 'As user - anonymous task - too short' => [
            'user',
            3,
            'aa',
            2,
            ['This value is too short. It should have 3 characters or more.'],
            '[Anonyme]',
        ];

        yield 'As admin - anonymous task - too short' => [
            'admin',
            3,
            'aa',
            2,
            ['This value is too short. It should have 3 characters or more.'],
            '[Anonyme]',
        ];
    }

    public function editSubmitValidProvider(): \Generator
    {
        yield 'As user - admin task' => [
            'user',
            2,
            'admin',
        ];

        yield 'As admin - user task' => [
            'admin',
            1,
            'user',
        ];

        yield 'As user - user task' => [
            'user',
            1,
            'user',
        ];

        yield 'As admin - admin task' => [
            'admin',
            2,
            'admin',
        ];

        yield 'As user - anonymous task' => [
            'user',
            3,
            'user',
        ];

        yield 'As admin - anonymous task' => [
            'admin',
            3,
            'admin',
        ];
    }

    public function toggleTaskValidProvider(): \Generator
    {
        yield 'As user - user todo task' => [
            'user',
            1,
            'TODO Task',
            false,
        ];

        yield 'As admin - user todo task' => [
            'admin',
            1,
            'TODO Task',
            false,
        ];

        yield 'As user - admin done task' => [
            'user',
            2,
            'DONE Task',
            true,
        ];

        yield 'As admin - admin done task' => [
            'admin',
            2,
            'DONE Task',
            true,
        ];

        yield 'As user - user done task' => [
            'user',
            3,
            'Anonymous DONE Task',
            true,
        ];

        yield 'As admin - user done task' => [
            'admin',
            3,
            'Anonymous DONE Task',
            true,
        ];
    }

    public function deleteTaskNotExistingProvider(): \Generator
    {
        yield 'as user' => ['user'];
        yield 'As admin' => ['admin'];
    }

    public function deleteTaskForbiddenProvider(): \Generator
    {
        yield 'As user - not mine' => [
            'user',
            2,
        ];

        yield 'As admin - not mine' => [
            'admin',
            1,
        ];

        yield 'As user - anonymous' => [
            'user',
            3,
        ];
    }

    public function deleteTaskValidProvider(): \Generator
    {
        yield 'As user - mine' => [
            'user',
            1,
        ];

        yield 'As admin - mine' => [
            'admin',
            2,
        ];

        yield 'As admin - anonymous' => [
            'admin',
            3,
        ];
    }
}
