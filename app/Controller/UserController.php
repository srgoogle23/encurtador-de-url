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
use App\Request\AddUserRequest;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Ramsey\Uuid\Uuid;

class UserController
{
    public function index(ResponseInterface $response)
    {
        return $response->json(User::all());
    }

    public function show(RequestInterface $request, ResponseInterface $response)
    {
        return $response->json(User::find($request->input('id')));
    }

    public function store(AddUserRequest $request, ResponseInterface $response)
    {
        // Valida os dados da requisição
        $validated = $request->validated();

        // Cria um novo usuário
        $user = new User();
        $user->id = Uuid::uuid4()->toString();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = $validated['password'];
        $user->save();

        return $response->json($user, 201);
    }

    public function delete(RequestInterface $request, ResponseInterface $response)
    {
        $user = User::find($request->input('id'));
        $user->delete();
        return $response->json($user);
    }
}
