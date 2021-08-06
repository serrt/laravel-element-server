<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\{Request, Response};
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class WebController extends Controller
{
    public function upload(Request $request)
    {
        $path = $request->input('path', 'uploads') . '/' . date('Y-m-d');
        $result = [];

        // base64 图片
        $data = $request->except(['path']);
        foreach ($data as $key => $files) {
            $item = null;
            if (is_array($files)) {
                foreach ($files as $file) {
                    $item[] = Storage::url($this->saveFile($path, $file));
                }
            } else if ($files) {
                $item = Storage::url($this->saveFile($path, $files));
            }
            if ($item) {
                $result[$key] = $item;
            }
        }
        return $this->json($result);
    }

    protected function saveFile($path, $file = null)
    {
        if (gettype($file) == 'object') {
            $ext = $file->getClientOriginalExtension();
            $file = Storage::putFileAs($path, $file, uniqid() . '.' . $ext);
        } else if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $file, $result)) {
            $type = $result[2];
            if (in_array($type, array('jpeg', 'jpg', 'gif', 'bmp', 'png'))) {
                $savePath = $path . '/' . uniqid() . '.' . $type;
                Storage::put($savePath, base64_decode(str_replace($result[1], '', $file)));
                $file = $savePath;
            }
        }
        return $file;
    }

    /**
     * 验证是否唯一
     *
     * @param $table string 验证的数据表名, 必填
     * @param $ignore string 需要忽略的值, 选填
     * @param $unique string 验证的数据表列名, 默认: id
     * @param $ignore_column string 需要忽略的key, 默认: id
     * @param $deleted string 是否使用软删除
     *
     * @return mixed
     */
    public function unique(Request $request)
    {
        $request->validate([
            'table' => 'required',
        ], [
            'table.required' => 'table 参数必填',
        ]);

        $column = $request->input('unique', 'id');
        $table = $request->input('table');

        $unique_rule = Rule::unique($table, $column);
        if ($request->filled('ignore')) {
            $unique_rule->ignore($request->input('ignore'), $request->input('ignore_column', 'id'));
        }
        if ($request->filled('deleted')) {
            $unique_rule->whereNull('deleted_at');
        }
        $request->validate([
            $column => ['required', $unique_rule]
        ], [
            $column . '.unique' => ':input 已经存在'
        ]);

        return $this->success();
    }
}
