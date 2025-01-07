<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Cases;

use Hyperf\Testing\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 * @coversNothing
 */
class UserTest extends TestCase
{
    private array $usersIds = [];

    protected function tearDown(): void
    {
        foreach ($this->usersIds as $userId) {
            $this->deleteUser($userId);
        }
        parent::tearDown();
    }

    public function testAddValidUser(): void
    {
        $user = $this->getUserData(__FUNCTION__);
        $response = $this->createUser($user);
        $this->assertSame(201, $response->getStatusCode());
        $responseContent = $this->getResponseContent($response);
        $this->assertArrayHasKey('id', $responseContent);
        $this->assertArrayHasKey('updated_at', $responseContent);
        $this->assertArrayHasKey('created_at', $responseContent);
    }

    public function testAddDuplicateUser(): void
    {
        $user = $this->getUserData(__FUNCTION__);
        $this->createUser($user);

        $response = $this->post('/users', $user);
        $this->assertSame(422, $response->getStatusCode());

        $responseContent = $this->getResponseContent($response);
        $this->assertSame('The email has already been taken.', $responseContent['message']);
    }

    public function testAddUserInvalidEmail(): void
    {
        $user = $this->getUserData(__FUNCTION__, email: 'invalid-email');
        $response = $this->createUser($user, 422, 'The email must be a valid email address.');

        $user['email'] = str_repeat('a', 256) . '@example.com';
        $this->createUser($user, 422, 'The email may not be greater than 255 characters.');
    }

    public function testAddUserInvalidPassword(): void
    {
        $user = $this->getUserData(__FUNCTION__, password: 'weakpassword');
        $this->createUser($user, 422, 'The password format is invalid.');
    }

    public function testAddUserInvalidName(): void
    {
        $user = $this->getUserData(__FUNCTION__, name: 'J1');
        $this->createUser($user, 422, 'The name format is invalid.');

        $user['name'] = str_repeat('a', 256);
        $this->createUser($user, 422, 'The name may not be greater than 255 characters.');

        $user['name'] = 'J';
        $this->createUser($user, 422, 'The name must be at least 2 characters.');
    }

    public function testGetAllUsers(): void
    {
        $response = $this->get('/users', [], ['Authorization' => $this->getToken()]);
        $this->assertSame(200, $response->getStatusCode());

        $responseContent = $this->getResponseContent($response);
        $this->assertIsArray($responseContent);
        $this->assertNotEmpty($responseContent);
    }

    public function testGetOneValidUser(): void
    {
        $user = $this->getUserData(__FUNCTION__);
        $response = $this->createUser($user);

        $userId = $this->getResponseContent($response)['id'];
        $response = $this->get('/users/' . $userId, [], ['Authorization' => $this->getToken()]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotEmpty($this->getResponseContent($response));
    }

    public function testGetOneInvalidUser(): void
    {
        $response = $this->get('/users/invalid-id', [], ['Authorization' => $this->getToken()]);
        $this->assertErrorMessage($response, 422, 'Invalid user ID.');

        $response = $this->delete('/users/' . Uuid::uuid4()->toString(), [], ['Authorization' => $this->getToken()]);
        $this->assertErrorMessage($response, 404, 'User not found.');
    }

    public function testDeleteValidUser(): void
    {
        $user = $this->getUserData(__FUNCTION__);
        $response = $this->createUser($user);

        $userId = $this->getResponseContent($response)['id'];
        $response = $this->deleteUser($userId);
    }

    public function testDeleteInvalidUser(): void
    {
        $response = $this->delete('/users/invalid-id', [], ['Authorization' => $this->getToken()]);
        $this->assertErrorMessage($response, 422, 'Invalid user ID.');

        $response = $this->delete('/users/' . Uuid::uuid4()->toString(), [], ['Authorization' => $this->getToken()]);
        $this->assertErrorMessage($response, 404, 'User not found.');
    }

    private function getToken(): string
    {
        $user = $this->getUserData(__FUNCTION__);
        $this->createUser($user);

        $authData = ['email' => $user['email'], 'password' => $user['password']];
        $responseAuth = $this->post('/auth', $authData);
        $this->assertSame(200, $responseAuth->getStatusCode());

        return 'Bearer ' . $this->getResponseContent($responseAuth)['data']['token'];
    }

    private function createUser(array $user, int $expectedStatusCode = 201, ?string $expectedMessage = null)
    {
        $response = $this->post('/users', $user);
        $this->assertSame($expectedStatusCode, $response->getStatusCode());

        if ($expectedMessage) {
            $responseContent = $this->getResponseContent($response);
            $this->assertSame($expectedMessage, $responseContent['message']);
        } else {
            $this->usersIds[] = $this->getResponseContent($response)['id'];
        }

        return $response;
    }

    private function deleteUser(string $userId): void
    {
        $response = $this->delete('/users/' . $userId, [], ['Authorization' => $this->getToken()]);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotEmpty($this->getResponseContent($response));
    }

    private function getUserData(string $functionName, string $name = 'John Doe', ?string $email = null, string $password = 'Password1@'): array
    {
        return [
            'name' => $name,
            'email' => $email ?? $this->generateRandomEmail($functionName),
            'password' => $password,
        ];
    }

    private function getResponseContent($response): array
    {
        return json_decode($response->getBody()->getContents(), true);
    }

    private function assertErrorMessage($response, int $statusCode, string $message): void
    {
        $this->assertSame($statusCode, $response->getStatusCode());
        $this->assertSame($message, $this->getResponseContent($response)['message']);
    }

    private function generateRandomEmail(string $functionName = 'example'): string
    {
        $rand = rand();
        return "user{$rand}@{$functionName}.com";
    }
}
