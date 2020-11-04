<?php

namespace App\Http\Resources;

use App\Models\AdminUser;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class AdminUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->name,
            'avatar' => $this->avatar,
            'status' => $this->status == AdminUser::STATUS_NORMAL,
            'created_at' => $this->created_at->timestamp,

            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions'))
        ];
    }

    public function with($request)
    {
        return ['code' => Response::HTTP_OK, 'message' => ''];
    }
}
