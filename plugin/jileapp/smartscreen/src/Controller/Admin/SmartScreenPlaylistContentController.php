<?php

declare(strict_types=1);

namespace Plugin\Jileapp\Smartscreen\Controller\Admin;

use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Result;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Plugin\Jileapp\Smartscreen\Service\SmartScreenPlaylistContentService;
use Hyperf\HttpServer\Contract\RequestInterface;

#[Controller(prefix: 'admin/plugin/smart-screen/playlist-content')]
class SmartScreenPlaylistContentController extends AbstractController
{
    #[Inject]
    protected SmartScreenPlaylistContentService $service;
    #[Inject]
    protected RequestInterface $request;

    #[GetMapping('list')]
    public function list(): Result
    {
        $params = $this->request->all();
        return $this->success($this->service->page($params, (int)($params['page'] ?? 1), (int)($params['page_size'] ?? 15)));
    }

    #[PostMapping('')]
    public function create(): Result
    {
        $data = $this->request->all();
        return $this->success($this->service->create($data));
    }

    #[PutMapping('{id}')]
    public function update(int $id): Result
    {
        $data = $this->request->all();
        return $this->success($this->service->updateById($id, $data));
    }

    #[DeleteMapping('')]
    public function delete(): Result
    {
        $ids = $this->request->input('ids', []);
        $count = 0;
        foreach ((array)$ids as $id) {
            $count += $this->service->deleteById($id);
        }
        return $this->success(['deleted' => $count]);
    }

    #[PostMapping('update-sort')]
    public function updateSortOrder(): Result
    {
        $items = $this->request->input('items', []);
        return $this->success($this->service->updateSortOrder($items));
    }
} 