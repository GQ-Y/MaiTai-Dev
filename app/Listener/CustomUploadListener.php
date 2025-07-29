<?php

declare(strict_types=1);

namespace App\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Filesystem\FilesystemFactory;
use Hyperf\Stringable\Str;
use League\Flysystem\Filesystem;
use Mine\Upload\Event\UploadEvent;
use Mine\Upload\Upload;
use Ramsey\Uuid\Uuid;

class CustomUploadListener implements ListenerInterface
{
    public const ADAPTER_NAME = 'local';

    private Filesystem $filesystem;

    public function __construct(
        FilesystemFactory $filesystemFactory
    ) {
        $this->filesystem = $filesystemFactory->get(static::ADAPTER_NAME);
    }

    public function listen(): array
    {
        return [
            UploadEvent::class,
        ];
    }

    public function process(object $event): void
    {
        if ($event instanceof UploadEvent) {
            $fileInfo = $event->getUploadFile();

            // Correctly get MIME type using mime_content_type
            $mimeType = mime_content_type($fileInfo->getRealPath());

            // If it's a video, skip processing in this listener
            if (Str::contains($mimeType, 'video')) {
                return;
            }

            $path = $this->generatorPath();
            $filename = $this->generatorId() . '.' . Str::lower($fileInfo->getExtension());
            $this->filesystem->write($path . '/' . $filename, file_get_contents($fileInfo->getRealPath()));
            $event->setUpload(new Upload(
                static::ADAPTER_NAME,
                $filename,
                $mimeType, // Use the correctly obtained MIME type
                $path . '/' . $filename, // Corrected storage_path
                md5_file($fileInfo->getRealPath()),
                Str::lower($fileInfo->getExtension()),
                $fileInfo->getSize(),
                $fileInfo->getSize(),
                $this->filesystem->publicUrl($path . '/' . $filename)
            ));
            @unlink($fileInfo->getRealPath());
        }
    }

    protected function generatorPath(): string
    {
        return date('Y-m-d');
    }

    protected function generatorId(): string
    {
        return Uuid::uuid4()->toString();
    }
}
