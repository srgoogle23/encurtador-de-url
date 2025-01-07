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

use App\Model\Link;
use Hyperf\Testing\TestCase;

/**
 * @internal
 * @coversNothing
 */
class LinkTest extends TestCase
{
    private string $token = '';

    private array $linksIds = [];

    private array $usersIds = [];

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testIndexWithValidUser(): void
    {
        $user = $this->getUserData(__FUNCTION__);
        $response = $this->createUser($user);
        $responseContent = $this->getResponseContent($response);
        $userId = $responseContent['id'];

        $link = $this->createLink(['user_id' => $userId, 'url' => 'https://example.com']);
        $response = $this->get("/links/byUser/{$userId}", [], ['Authorization' => $this->getToken()]);

        $response->assertStatus(200);
        $responseData = $this->getResponseContent($response);
        $this->assertNotEmpty($responseData);
        $this->assertSame($link['url'], $responseData[0]['url']);
    }

    public function testIndexWithInvalidUser(): void
    {
        $response = $this->get('/links/byUser/invalid-user-id', [], ['Authorization' => $this->getToken()]);
        $response->assertStatus(422);
        $this->assertErrorMessage($response, 'Invalid user ID.');
    }

    public function testShowWithValidShortenedLink(): void
    {
        $user = $this->getUserData(__FUNCTION__);
        $response = $this->createUser($user);
        $responseContent = $this->getResponseContent($response);

        $link = $this->createLink(['user_id' => $responseContent['id'], 'url' => 'https://example.com']);
        $response = $this->get("/links/{$link['shortened_url']}", [], ['Authorization' => $this->getToken()]);

        $response->assertStatus(200);
        $responseData = $this->getResponseContent($response);
        $this->assertSame($link['url'], $responseData['url']);
    }

    public function testShowWithInvalidShortenedLink(): void
    {
        $response = $this->get('/links/invalid123', [], ['Authorization' => $this->getToken()]);
        $response->assertStatus(422);
        $this->assertErrorMessage($response, 'The selected shortened url is invalid.');
    }

    public function testStoreWithValidData(): void
    {
        $user = $this->getUserData(__FUNCTION__);
        $response = $this->createUser($user);
        $responseContent = $this->getResponseContent($response);

        $payload = ['user_id' => $responseContent['id'], 'url' => 'https://example.com'];
        $response = $this->post('/links', $payload, ['Authorization' => $this->getToken()]);

        $response->assertStatus(201);
        $responseData = $this->getResponseContent($response);
        $this->assertArrayHasKey('shortened_url', $responseData);
        $this->assertSame($payload['url'], $responseData['url']);

        // Adicionar link criado para exclusão posterior
        $this->linksIds[] = $responseData['shortened_url'];
    }

    public function testStoreWithInvalidUser(): void
    {
        $payload = ['user_id' => 'invalid-user-id', 'url' => 'https://example.com'];
        $response = $this->post('/links', $payload, ['Authorization' => $this->getToken()]);
        $response->assertStatus(422);
        $this->assertErrorMessage($response, 'Invalid user ID.');
    }

    public function testStoreWithInvalidUrl(): void
    {
        $user = $this->getUserData(__FUNCTION__);
        $response = $this->createUser($user);
        $responseContent = $this->getResponseContent($response);

        $payload = ['user_id' => $responseContent['id'], 'url' => 'invalid-url'];
        $response = $this->post('/links', $payload, ['Authorization' => $this->getToken()]);
        $response->assertStatus(422);
        $this->assertErrorMessage($response, 'The url format is invalid.');
    }

    private function getToken(): string
    {
        if ($this->token !== '') {
            return $this->token;
        }

        $user = $this->getUserData(__FUNCTION__);
        $this->createUser($user);

        $authResponse = $this->post('/auth', ['email' => $user['email'], 'password' => $user['password']]);
        $this->assertSame(200, $authResponse->getStatusCode());

        $this->token = 'Bearer ' . $this->getResponseContent($authResponse)['data']['token'];

        return $this->token;
    }

    private function createUser(array $user, int $expectedStatusCode = 201)
    {
        $response = $this->post('/users', $user);
        $this->assertSame($expectedStatusCode, $response->getStatusCode());
        $responseContent = $this->getResponseContent($response);
        $this->usersIds[] = $responseContent['id'];

        return $response;
    }

    private function getUserData(string $functionName, string $name = 'John Doe', ?string $email = null, string $password = 'Password1@'): array
    {
        return [
            'name' => $name,
            'email' => $email ?? $this->generateRandomEmail($functionName),
            'password' => $password,
        ];
    }

    private function generateRandomEmail(string $functionName = 'example'): string
    {
        $rand = rand();
        return "user{$rand}@{$functionName}.com";
    }

    private function createLink(array $attributes): array
    {
        $default = ['shortened_url' => bin2hex(random_bytes(5))];
        $link = Link::create(array_merge($default, $attributes));
        $this->linksIds[] = $link->shortened_url;

        return $link->toArray();
    }

    private function getResponseContent($response): array
    {
        return json_decode($response->getBody()->getContents(), true);
    }

    private function assertErrorMessage($response, string $message): void
    {
        $responseData = $this->getResponseContent($response);
        $this->assertSame($message, $responseData['message']);
    }
}
