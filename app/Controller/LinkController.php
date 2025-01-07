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

use App\Model\Link;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Ramsey\Uuid\Uuid;

class LinkController
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

    public function index(string $user, ResponseInterface $response)
    {
        if (! Uuid::isValid($user)) {
            return $response->json(['status' => 'error', 'message' => 'Invalid user ID.'])->withStatus(422);
        }

        $validator = $this->validationFactory->make(
            ['user' => $user],
            ['user' => 'required|exists:links,user_id']
        );

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            return $response->json(['status' => 'error', 'message' => $errorMessage])->withStatus(422);
        }

        $links = Link::where('user_id', $user)->get();

        return $response->json($links)->withStatus(200);
    }

    /**
     * @Cacheable(key="link", ttl=9000, listener="link-update")
     */
    public function show(string $shortenedLink, ResponseInterface $response)
    {
        $validator = $this->validationFactory->make(
            ['shortened_url' => $shortenedLink],
            ['shortened_url' => 'required|exists:links,shortened_url']
        );

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            return $response->json(['status' => 'error', 'message' => $errorMessage])->withStatus(422);
        }

        $link = Link::where('shortened_url', $shortenedLink)->first();

        return $response->json($link)->withStatus(200);
    }

    public function store(RequestInterface $request, ResponseInterface $response)
    {
        if (! Uuid::isValid($request->input('user_id'))) {
            return $response->json(['status' => 'error', 'message' => 'Invalid user ID.'])->withStatus(422);
        }

        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'user_id' => 'required|exists:users,id',
                'url' => 'required|url',
            ]
        );

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            return $response->json(['status' => 'error', 'message' => $errorMessage])->withStatus(422);
        }

        $validated = $validator->validated();

        $link = Link::create([
            'user_id' => $validated['user_id'],
            'url' => $validated['url'],
            'shortened_url' => bin2hex(random_bytes(5)),
        ]);

        return $response->json($link)->withStatus(201);
    }
}
