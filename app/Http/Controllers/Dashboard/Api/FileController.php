<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Models\File;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class FileController
{
    public function deleteFile(
        string $folderName,
        string $fileName
    ): \Illuminate\Foundation\Application|Response|Application|ResponseFactory {
        $url = $folderName.'/'.$fileName;
        File::query()->where('url', $url)->delete();
        Storage::disk('public')->delete($url);

        return response([], 201);
    }
}
