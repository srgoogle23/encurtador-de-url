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
        $this->assertArrayHasKey('id', $responseContent);
        $this->assertArrayHasKey('password', $responseContent);
        unset($responseContent['id'], $responseContent['password'], $user['password']);

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

        $this->assertSame(409, $response->getStatusCode());
        $responseContent = $response->toArray();
        $this->assertSame('User already exists.', $responseContent['message']);
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
}