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

namespace App\Controller;

use App\Model\User;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Phper666\JWTAuth\JWT;

class AuthController
{
    /**
     * @Inject
     * @var ValidatorFactoryInterface
     */
    protected $validationFactory;

    public function __construct(ValidatorFactoryInterface $validationFactory)
    {
        $this->validationFactory = $validationFactory;
    }

    public function auth(RequestInterface $request, JWT $jwt, ResponseInterface $response)
    {
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'email' => 'required|email|exists:users,email',
                'password' => 'required|regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[@$!%*#?&]).{8,}$/',
            ]
        );

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            return $response->json(['status' => 'error', 'message' => $errorMessage])->withStatus(422);
        }

        $validated = $validator->validated();

        $user = User::where('email', $validated['email'])->first();

        if (! password_verify($validated['password'], $user->password)) {
            return $response->json(['status' => 'error', 'message' => 'Invalid credentials'])->withStatus(401);
        }

        $token = $jwt->getToken('default', ['id' => $user->id]);

        return $response->json(['status' => 'success', 'data' => ['token' => $token->toString(), 'expires' => $jwt->getTTL($token->toString())]]);
    }
}
