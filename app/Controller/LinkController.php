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

class LinkController
{
    /**
     * @Inject
     * @var ValidatorFactoryInterface
     */
    protected $validationFactory;

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
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'url' => 'required|url',
            ]
        );

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            return $response->json(['status' => 'error', 'message' => $errorMessage])->withStatus(422);
        }

        $link = Link::create([
            'url' => $request->input('url'),
            'shortened_url' => bin2hex(random_bytes(5)),
        ]);

        return $response->json($link)->withStatus(201);
    }
}
