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
        $token = $this->getToken();

        foreach ($this->usersIds as $userId) {
            $this->delete('/users/' . $userId, [], ['Authorization' => $token]);
        }

        parent::tearDown();
    }

    public function testAddValidUser(): void
    {
        $user = [
            'name' => 'John Doe',
            'email' => $this->generateRandomEmail(__FUNCTION__),
            'password' => 'Password1@',
        ];
        $response = $this->post('/users', $user);
        $this->assertSame(201, $response->getStatusCode());
        $responseContent = json_decode($response->getBody()->getContents(), true);
        $this->usersIds[] = $responseContent['id'];

        $this->assertArrayHasKey('id', $responseContent);
        $this->assertArrayHasKey('updated_at', $responseContent);
        $this->assertArrayHasKey('created_at', $responseContent);
        unset($responseContent['id'], $user['password'], $responseContent['updated_at'], $responseContent['created_at']);

        $this->assertSame($user, $responseContent);
    }

    public function testAddDuplicateUser(): void
    {
        $randomEmail = $this->generateRandomEmail(__FUNCTION__);
        $user = [
            'name' => 'John Doe',
            'email' => $randomEmail,
            'password' => 'Password1@',
        ];
        $response = $this->post('/users', $user);
        $this->assertSame(201, $response->getStatusCode());
        $responseContent = json_decode($response->getBody()->getContents(), true);
        $this->usersIds[] = $responseContent['id'];

        $response = $this->post('/users', $user);
        $this->assertSame(422, $response->getStatusCode());
        $responseContent = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('The email has already been taken.', $responseContent['message']);
    }

    public function testAddUserInvalidEmail(): void
    {
        $user = [
            'name' => 'John Doe',
            'email' => 'johnexample.com',
            'password' => 'Password1@',
        ];
        $response = $this->post('/users', $user);

        $this->assertSame(422, $response->getStatusCode());
        $responseContent = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('The email must be a valid email address.', $responseContent['message']);

        $user['email'] = str_repeat('a', 256) . '@example.com';
        $response = $this->post('/users', $user);

        $this->assertSame(422, $response->getStatusCode());
        $responseContent = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('The email may not be greater than 255 characters.', $responseContent['message']);
    }

    public function testAddUserInvalidPassword(): void
    {
        $user = [
            'name' => 'John Doe',
            'email' => $this->generateRandomEmail(__FUNCTION__),
            'password' => 'password',
        ];
        $response = $this->post('/users', $user);

        $this->assertSame(422, $response->getStatusCode());
        $responseContent = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('The password format is invalid.', $responseContent['message']);
    }

    public function testAddUserInvalidName(): void
    {
        $user = [
            'name' => 'J1',
            'email' => $this->generateRandomEmail(__FUNCTION__),
            'password' => 'Password1@',
        ];
        $response = $this->post('/users', $user);

        $this->assertSame(422, $response->getStatusCode());
        $responseContent = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('The name format is invalid.', $responseContent['message']);

        $user['name'] = str_repeat('a', 256);
        $response = $this->post('/users', $user);

        $this->assertSame(422, $response->getStatusCode());
        $responseContent = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('The name may not be greater than 255 characters.', $responseContent['message']);

        $user['name'] = 'J';
        $response = $this->post('/users', $user);

        $this->assertSame(422, $response->getStatusCode());
        $responseContent = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('The name must be at least 2 characters.', $responseContent['message']);
    }

    public function testGetAllUsers(): void
    {
        $response = $this->get('/users', [], ['Authorization' => $this->getToken()]);

        $this->assertSame(200, $response->getStatusCode());
        $responseContent = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($responseContent);
        $this->assertNotEmpty($responseContent);
    }

    public function testGetOneValidUser(): void
    {
        $user = [
            'name' => 'John Doe',
            'email' => $this->generateRandomEmail(__FUNCTION__),
            'password' => 'Password1@',
        ];
        $response = $this->post('/users', $user);
        $this->assertSame(201, $response->getStatusCode());
        $responseContent = json_decode($response->getBody()->getContents(), true);
        $this->usersIds[] = $responseContent['id'];

        $response = $this->get('/users/' . $responseContent['id'], [], ['Authorization' => $this->getToken()]);

        $this->assertSame(200, $response->getStatusCode());
        $responseContent = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($responseContent);
        $this->assertNotEmpty($responseContent);
    }

    public function testGetOneInvalidUser(): void
    {
        $response = $this->get('/users/invalid-id', [], ['Authorization' => $this->getToken()]);

        $this->assertSame(422, $response->getStatusCode());
        $responseContent = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('Invalid user ID.', $responseContent['message']);

        $response = $this->delete('/users/' . Uuid::uuid4()->toString(), [], ['Authorization' => $this->getToken()]);

        $this->assertSame(404, $response->getStatusCode());
        $responseContent = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('User not found.', $responseContent['message']);
    }

    public function testDeleteValidUser(): void
    {
        $user = [
            'name' => 'John Doe',
            'email' => $this->generateRandomEmail(__FUNCTION__),
            'password' => 'Password1@',
        ];
        $response = $this->post('/users', $user);
        $this->assertSame(201, $response->getStatusCode());
        $responseContent = json_decode($response->getBody()->getContents(), true);
        $this->usersIds[] = $responseContent['id'];

        $response = $this->delete('/users/' . $responseContent['id'], [], ['Authorization' => $this->getToken()]);

        $this->assertSame(200, $response->getStatusCode());
        $responseContent = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($responseContent);
        $this->assertNotEmpty($responseContent);
    }

    public function testDeleteInvalidUser(): void
    {
        $response = $this->delete('/users/invalid-id', [], ['Authorization' => $this->getToken()]);

        $this->assertSame(422, $response->getStatusCode());
        $responseContent = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('Invalid user ID.', $responseContent['message']);

        $response = $this->delete('/users/' . Uuid::uuid4()->toString(), [], ['Authorization' => $this->getToken()]);

        $this->assertSame(404, $response->getStatusCode());
        $responseContent = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('User not found.', $responseContent['message']);
    }

    private function getToken(): string
    {
        $user = [
            'name' => 'John Doe',
            'email' => $this->generateRandomEmail(__FUNCTION__),
            'password' => 'Password1@',
        ];
        $response = $this->post('/users', $user);
        $this->assertSame(201, $response->getStatusCode());
        $responseContent = json_decode($response->getBody()->getContents(), true);
        $this->usersIds[] = $responseContent['id'];

        $authData = $user;
        unset($authData['name']);
        $responseAuth = $this->post('/auth', $authData);
        $this->assertSame(200, $responseAuth->getStatusCode());
        $responseContentAuth = json_decode($responseAuth->getBody()->getContents(), true);

        return 'Bearer ' . $responseContentAuth['data']['token'];
    }

    private function generateRandomEmail(string $functionName = 'example'): string
    {
        $rand = rand();
        return "user{$rand}@{$functionName}.com";
    }
}
