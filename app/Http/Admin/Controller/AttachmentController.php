<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace App\Http\Admin\Controller;

use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\UploadRequest;
use App\Http\Admin\Request\MergeChunkRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\CurrentUser;
use App\Schema\AttachmentSchema;
use App\Service\AttachmentService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Delete;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;
use Symfony\Component\Finder\SplFileInfo;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: OperationMiddleware::class, priority: 98)]
final class AttachmentController extends AbstractController
{
    public function __construct(
        protected readonly AttachmentService $service,
        protected readonly CurrentUser $currentUser
    ) {}

    #[Get(
        path: '/admin/attachment/list',
        operationId: 'AttachmentList',
        summary: '附件列表',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['数据中心'],
    )]
    #[Permission(code: 'dataCenter:attachment:list')]
    #[PageResponse(instance: AttachmentSchema::class)]
    public function list(): Result
    {
        $params = $this->getRequest()->all();
        $params['current_user_id'] = $this->currentUser->id();
        if (isset($params['suffix'])) {
            $params['suffix'] = explode(',', $params['suffix']);
        }
        return $this->success(
            $this->service->page($params, $this->getCurrentPage(), $this->getPageSize())
        );
    }

    #[Post(
        path: '/admin/attachment/upload',
        operationId: 'UploadAttachment',
        summary: '上传附件',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['数据中心'],
    )]
    #[Permission(code: 'dataCenter:attachment:upload')]
    #[ResultResponse(instance: new Result())]
    public function upload(UploadRequest $request): Result
    {
        $uploadFile = $request->file('file');
        $newTmpPath = sys_get_temp_dir() . '/' . uniqid() . '.' . $uploadFile->getExtension();
        $uploadFile->moveTo($newTmpPath);
        $splFileInfo = new SplFileInfo($newTmpPath, '', '');
        return $this->success(
            $this->service->upload($splFileInfo, $uploadFile, $this->currentUser->id())
        );
    }

    /**
     * 分片上传：接收单个分片
     * POST /admin/attachment/upload-chunk
     * 参数：file(分片文件)、file_md5、chunk_index、chunk_total、filename
     */
    #[Post(
        path: '/admin/attachment/upload-chunk',
        operationId: 'UploadChunk',
        summary: '分片上传-接收分片',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['数据中心'],
    )]
    #[Permission(code: 'dataCenter:attachment:upload')]
    #[ResultResponse(instance: new Result())]
    public function uploadChunk(UploadRequest $request): Result
    {
        $file = $request->file('file');
        $fileMd5 = $request->input('file_md5');
        $chunkIndex = $request->input('chunk_index');
        $chunkTotal = $request->input('chunk_total');
        $filename = $request->input('filename');
        if (!$file || !$fileMd5 || $chunkIndex === null || !$chunkTotal) {
            return $this->error('参数不完整');
        }
        $tmpDir = sys_get_temp_dir() . '/upload_chunks/' . $fileMd5;
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }
        $chunkPath = $tmpDir . '/' . $chunkIndex;
        $file->moveTo($chunkPath);
        return $this->success(['msg' => '分片上传成功']);
    }

    /**
     * 分片上传：合并分片
     * POST /admin/attachment/merge-chunk
     * 参数：file_md5、chunk_total、filename
     */
    #[Post(
        path: '/admin/attachment/merge-chunk',
        operationId: 'MergeChunk',
        summary: '分片上传-合并分片',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['数据中心'],
    )]
    #[Permission(code: 'dataCenter:attachment:upload')]
    #[ResultResponse(instance: new Result())]
    public function mergeChunk(MergeChunkRequest $request): Result
    {
        $fileMd5 = $request->input('file_md5');
        $chunkTotal = (int)$request->input('chunk_total');
        $filename = $request->input('filename');
        if (!$fileMd5 || !$chunkTotal || !$filename) {
            return $this->error('参数不完整');
        }
        $tmpDir = sys_get_temp_dir() . '/upload_chunks/' . $fileMd5;
        $mergePath = $tmpDir . '/merge_' . $filename;
        $out = fopen($mergePath, 'w');
        for ($i = 0; $i < $chunkTotal; $i++) {
            $chunkFile = $tmpDir . '/' . $i;
            if (!file_exists($chunkFile)) {
                fclose($out);
                return $this->error('缺少分片：' . $i);
            }
            $in = fopen($chunkFile, 'r');
            while ($buff = fread($in, 1048576)) {
                fwrite($out, $buff);
            }
            fclose($in);
        }
        fclose($out);

        $splFileInfo = new SplFileInfo($mergePath, '', '');
        $uploadedFile = new \Hyperf\HttpMessage\Upload\UploadedFile(
            $mergePath,
            $splFileInfo->getSize(),
            UPLOAD_ERR_OK,
            $filename,
            mime_content_type($mergePath)
        );

        $attachment = $this->service->upload($splFileInfo, $uploadedFile, $this->currentUser->id());

        // Cleanup temp files
        for ($i = 0; $i < $chunkTotal; $i++) {
            @unlink($tmpDir . '/' . $i);
        }
        @unlink($mergePath);
        @rmdir($tmpDir);

        return $this->success($attachment);
    }

    #[Delete(
        path: '/admin/attachment/{id}',
        operationId: 'DeleteAttachment',
    )]
    #[Permission(code: 'dataCenter:attachment:delete')]
    #[ResultResponse(instance: new Result())]
    public function delete(int $id): Result
    {
        if (! $this->service->getRepository()->existsById($id)) {
            return $this->error(trans('attachment.attachment_not_exist'));
        }
        $this->service->deleteById($id);
        return $this->success();
    }

    // 新增格式化文件大小方法
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
