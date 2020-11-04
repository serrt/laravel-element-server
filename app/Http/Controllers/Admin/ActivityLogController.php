<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\Role;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with(['causer', 'subject']);

        if ($request->filled('search')) {
            $condition = '%'. $request->input('search').'%';
            $query->where(function ($q) use ($condition) {
                $q->where('description', 'like', $condition)->orWhere('subject_type', 'like', $condition);
            });
        }

        $list = $query->orderByDesc('created_at')->paginate($request->input('limit'));

        return ActivityLogResource::collection($list)->additional(['code' => Response::HTTP_OK, 'message' => '']);
    }

    public function clear()
    {
        Activity::truncate();
        return $this->success();
    }
}
