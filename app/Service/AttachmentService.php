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

namespace App\Service;

use App\Model\Attachment;
use App\Repository\AttachmentRepository;
use Hyperf\HttpMessage\Upload\UploadedFile;
use Mine\Upload\UploadInterface;
use Symfony\Component\Finder\SplFileInfo;
use Hyperf\Filesystem\FilesystemFactory;
use Hyperf\Stringable\Str;
use Ramsey\Uuid\Uuid;

/**
 * @extends IService<Attachment>
 */
final class AttachmentService extends IService
{
    public function __construct(
        protected readonly AttachmentRepository $repository,
        protected readonly UploadInterface $upload,
        protected readonly FilesystemFactory $filesystemFactory
    ) {}

    public function upload(SplFileInfo $fileInfo, UploadedFile $uploadedFile, int $userId): Attachment
    {
        $fileHash = md5_file($fileInfo->getRealPath());
        if ($attachment = $this->repository->findByHash($fileHash)) {
            return $attachment;
        }

        $mimeType = mime_content_type($fileInfo->getRealPath());
        $fileSize = $uploadedFile->getSize(); // Changed to use $uploadedFile->getSize()

        // If it's a video, handle it directly to avoid UploadListener issues
        if (Str::contains($mimeType, 'video')) {
            $filesystem = $this->filesystemFactory->get('local'); // Assuming 'local' disk
            $datePath = date('Y-m-d');
            $objectName = Uuid::uuid4()->toString() . '.' . $fileInfo->getExtension();
            $relativeStoragePath = $datePath . '/' . $objectName;

            // Stream the file to storage
            $stream = fopen($fileInfo->getRealPath(), 'r+');
            $filesystem->writeStream($relativeStoragePath, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }

            // Clean up the temporary merged file
            @unlink($fileInfo->getRealPath());

            // Get full URL from config
            $fullUrl = env('APP_URL') . '/uploads/' . $relativeStoragePath;

            return $this->repository->create([
                'created_by' => $userId,
                'origin_name' => $uploadedFile->getClientFilename(),
                'storage_mode' => 'local', // Assuming local storage
                'object_name' => $objectName,
                'mime_type' => $mimeType,
                'storage_path' => $relativeStoragePath,
                'hash' => $fileHash,
                'suffix' => $fileInfo->getExtension(),
                'size_byte' => $fileSize,
                'size_info' => $this->formatBytes($fileSize),
                'url' => $fullUrl,
            ]);
        }

        // For non-video files, use the existing upload factory
        $upload = $this->upload->upload(
            $fileInfo,
        );
        return $this->repository->create([
            'created_by' => $userId,
            'origin_name' => $uploadedFile->getClientFilename(),
            'storage_mode' => $upload->getStorageMode(),
            'object_name' => $upload->getObjectName(),
            'mime_type' => $upload->getMimeType(),
            'storage_path' => $upload->getStoragePath(),
            'hash' => $fileHash,
            'suffix' => $upload->getSuffix(),
            'size_byte' => $upload->getSizeByte(),
            'size_info' => $upload->getSizeInfo(),
            'url' => $upload->getUrl(),
        ]);
    }

    public function getRepository(): AttachmentRepository
    {
        return $this->repository;
    }

    // Add formatBytes method (copied from AttachmentController)
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