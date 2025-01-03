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
use Ramsey\Uuid\Uuid;

class UserController
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

    public function index(ResponseInterface $response)
    {
        return $response->json(User::all())->withStatus(200);
    }

    public function show(string $id, ResponseInterface $response)
    {
        if (! Uuid::isValid($id)) {
            return $response->json(['status' => 'error', 'message' => 'Invalid user ID.'])->withStatus(422);
        }

        $user = User::find($id);
        if (! $user) {
            return $response->json(['status' => 'error', 'message' => 'User not found.'])->withStatus(404);
        }
        return $response->json($user)->withStatus(200);
    }

    public function store(RequestInterface $request, ResponseInterface $response)
    {
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'name' => 'required|regex:/^[A-Za-z\s]+$/|min:2|max:255',
                'email' => 'required|email|unique:users,email|max:255',
                'password' => 'required|regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[@$!%*#?&]).{8,}$/',
            ]
        );

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            return $response->json(['status' => 'error', 'message' => $errorMessage])->withStatus(422);
        }

        $validated = $validator->validated();

        $user = new User();
        $user->id = Uuid::uuid4()->toString();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = password_hash($validated['password'], PASSWORD_DEFAULT);
        $user->save();

        return $response->json($user)->withStatus(201);
    }

    public function delete(string $id, ResponseInterface $response)
    {
        if (! Uuid::isValid($id)) {
            return $response->json(['status' => 'error', 'message' => 'Invalid user ID.'])->withStatus(422);
        }

        $user = User::find($id);
        if (! $user) {
            return $response->json(['status' => 'error', 'message' => 'User not found.'])->withStatus(404);
        }
        $user->delete();
        return $response->json(['status' => 'success', 'message' => 'User deleted.'])->withStatus(200);
    }
}
