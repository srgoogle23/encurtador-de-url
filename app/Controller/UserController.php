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
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Ramsey\Uuid\Uuid;

class UserController
{
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        return $response->json(User::all());
    }

    public function show(RequestInterface $request, ResponseInterface $response)
    {
        return $response->json(User::find($request->input('id')));
    }

    public function store(RequestInterface $request, ResponseInterface $response)
    {
        $user = new User();
        $user->id = Uuid::uuid4()->toString();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = $request->input('password');
        $user->save();
        return $response->json($user);
    }

    public function delete(RequestInterface $request, ResponseInterface $response)
    {
        $user = User::find($request->input('id'));
        $user->delete();
        return $response->json($user);
    }
}
