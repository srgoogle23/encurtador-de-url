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

/**
 * @internal
 * @coversNothing
 */
class UserTest extends TestCase
{
    private string $userId;

    public function testAddValidUser()
    {
        $user = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password1@',
        ];
        $response = $this->post('/users', $user);

        $this->assertSame(201, $response->getStatusCode());
        $responseContent = $response->toArray();
        $this->userId = $responseContent['id'];

        $this->assertArrayHasKey('id', $responseContent);
        unset($responseContent['id'], $user['password']);

        $this->assertSame($user, $responseContent);
    }

    public function testAddDuplicateUser()
    {
        $user = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password1@',
        ];
        $response = $this->post('/users', $user);

        $this->assertSame(422, $response->getStatusCode());
        $responseContent = $response->toArray();
        $this->assertSame('The email has already been taken.', $responseContent['message']);
    }

    public function testAddUserInvalidEmail()
    {
        $user = [
            'name' => 'John Doe',
            'email' => 'johnexample.com',
            'password' => 'Password1@',
        ];
        $response = $this->post('/users', $user);

        $this->assertSame(400, $response->getStatusCode());
        $responseContent = $response->toArray();
        $this->assertSame('Invalid email address.', $responseContent['message']);
    }

    public function testAddUserInvalidPassword()
    {
        $user = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
        ];
        $response = $this->post('/users', $user);

        $this->assertSame(400, $response->getStatusCode());
        $responseContent = $response->toArray();
        $this->assertSame('Password must contain at least one uppercase letter, one lowercase letter, one number and one special character.', $responseContent['message']);
    }

    public function testAddUserInvalidName()
    {
        $user = [
            'name' => 'J1',
            'email' => 'john@example.com',
            'password' => 'Password1@',
        ];
        $response = $this->post('/users', $user);

        $this->assertSame(400, $response->getStatusCode());
        $responseContent = $response->toArray();
        $this->assertSame('Name must have at least two characters and cannot contain numbers.', $responseContent['message']);
    }

    public function testGetAllUsers()
    {
        $response = $this->get('/users');

        $this->assertSame(200, $response->getStatusCode());
        $responseContent = $response->toArray();
        $this->assertIsArray($responseContent);
        $this->assertNotEmpty($responseContent);
    }

    public function testGetOneValidUser()
    {
        $response = $this->get('/users/' . $this->userId);

        $this->assertSame(200, $response->getStatusCode());
        $responseContent = $response->toArray();
        $this->assertIsArray($responseContent);
        $this->assertNotEmpty($responseContent);
    }

    public function testGetOneInvalidUser()
    {
        $response = $this->get('/users/invalid-id');

        $this->assertSame(404, $response->getStatusCode());
        $responseContent = $response->toArray();
        $this->assertSame('User not found.', $responseContent['message']);
    }

    public function testDeleteValidUser()
    {
        $response = $this->delete('/users/' . $this->userId);

        $this->assertSame(200, $response->getStatusCode());
        $responseContent = $response->toArray();
        $this->assertIsArray($responseContent);
        $this->assertNotEmpty($responseContent);
    }

    public function testDeleteInvalidUser()
    {
        $response = $this->delete('/users/invalid-id');

        $this->assertSame(404, $response->getStatusCode());
        $responseContent = $response->toArray();
        $this->assertSame('User not found.', $responseContent['message']);
    }
}
