<?php

namespace App\Http\Admin\Request;

use Hyperf\HttpServer\Request;

class MergeChunkRequest extends Request
{
    public function rules(): array
    {
        return [
            'file_md5' => 'required|string',
            'chunk_total' => 'required|integer',
            'filename' => 'required|string',
        ];
    }
} 