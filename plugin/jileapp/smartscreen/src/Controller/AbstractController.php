<?php

declare(strict_types=1);

namespace Plugin\Jileapp\Smartscreen\Controller;

use App\Http\Common\Controller\AbstractController as BaseController;
use Hyperf\HttpServer\Contract\RequestInterface;

abstract class AbstractController extends BaseController
{
    public function __construct(
        protected RequestInterface $request
    ) {}
} 