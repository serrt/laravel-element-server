<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Plugin\ListWith;

class MediaController extends Controller
{
    protected $hidden = [
        '',
    ];

    public function upload(Request $request)
    {
        $path = $request->input('path', '/');
        $result = [];

        $files = $request->file();
        $disk = $this->disk();

        foreach ($files as $key => $fileData) {
            $item = null;
            if (is_array($fileData)) {
                foreach ($fileData as $file) {
                    $item[] = $disk->url($disk->putFile($path, $file));
                }
            } else {
                $item = $disk->url($disk->putFile($path, $file));
            }
            $result[$key] = $item;
        }
        return $this->json($result);
    }

    public function files(Request $request)
    {
        $dir = $request->input('folder', '/');
        $type = $request->input('type');
        if ($type) {
            $type = is_array($type) ? $type : explode(',', $type);
        }

        $storage = $this->disk();

        $storageItems = Storage::addPlugin(new ListWith())->listWith(['mimetype'], $dir);

        $data = [];
        foreach($storageItems as $item) {
            if ($this->checkHidden($item['filename'])) {
                continue;
            }
            if ($item['type'] !== 'dir') {
                $mimeType = $item['mimetype'];
                if (preg_match('/^image\/\w+$/', $mimeType)) {
                    $item['type'] = 'image';
                } else if (preg_match('/^video\/\w+$/', $mimeType)) {
                    $item['type'] = 'video';
                }
                $item['filename'] = $item['filename'] . '.' . $item['extension'];
                $item['url'] = $storage->url($item['path']);

                if ($type && count($type) > 0 && !in_array($item['type'], $type)) {
                    continue;
                }
            }
            array_push($data, $item);
        }

        return $this->json($data);
    }

    public function deleteFiles(Request $request)
    {
        $request->validate([
            'path' => 'required'
        ]);

        $path = $request->input('path');
        $path = is_array($path) ? $path : explode(',', $path);

        $disk = $this->disk();

        $disk->delete($path);

        return $this->success();
    }

    public function addFolder(Request $result)
    {
        $result->validate([
            'path' => 'required'
        ]);
        $path = $result->input('path');

        $disk = $this->disk();

        $disk->makeDirectory($path);

        return $this->success();
    }

    public function deleteFolder(Request $result)
    {
        $result->validate([
            'path' => 'required'
        ]);
        $path = $result->input('path');

        $disk = $this->disk();

        $disk->deleteDirectory($path);

        return $this->success();
    }

    protected function disk()
    {
        return Storage::disk();
    }

    protected function checkHidden($filename)
    {
        return in_array($filename, $this->hidden);
    }
}
