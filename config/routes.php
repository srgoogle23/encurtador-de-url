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
use App\Controller\AuthController;
use App\Controller\LinkController;
use App\Controller\UserController;
use Hyperf\HttpServer\Router\Router;
use Phper666\JWTAuth\Middleware\JWTAuthMiddleware;

Router::addGroup('/users', function () {
    Router::get('', [UserController::class, 'index']);
    Router::get('/{id}', [UserController::class, 'show']);
    Router::post('', [UserController::class, 'store']);
    Router::delete('/{id}', [UserController::class, 'delete']);
}, ['middleware' => [JWTAuthMiddleware::class]]);

Router::addGroup('/links', function () {
    Router::get('/byUser/{user}', [LinkController::class, 'index']);
    Router::get('/{shortenedLink}', [LinkController::class, 'show']);
    Router::post('', [LinkController::class, 'store']);
}, ['middleware' => [JWTAuthMiddleware::class]]);

Router::addGroup('/auth', function () {
    Router::post('', [AuthController::class, 'auth']);
});

Router::get('/favicon.ico', function () {
    return '';
});
