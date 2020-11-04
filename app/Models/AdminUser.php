<?php

namespace App\Models;

use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class AdminUser extends Authenticatable
{
    use Notifiable, HasRoles;

    const STATUS_NORMAL = 1;
    const STATUS_DISABLE = 2;

    protected $fillable = ['password', 'username', 'name', 'avatar', 'status'];

    protected $hidden = ['password', 'api_token'];

    protected $attributes = [
        'status' => self::STATUS_NORMAL,
    ];

    public static $statusMap = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_DISABLE => '禁用'
    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function setAvatarAttribute($file = null)
    {
        $path = 'avatar/' . date('Y-m-d');
        if ($file instanceof UploadedFile) {
            $file = Storage::putFile($path, $file);
        } else if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $file, $result)) {
            $type = data_get($result, 2);
            $savePath = $path . '/' . uniqid() . '.' . $type;
            Storage::put($savePath, base64_decode(str_replace($result[1], '', $file)));
            $file = $savePath;
        }
        $this->attributes['avatar'] = $file;
    }

    public function getAvatarAttribute()
    {
        $value = $this->attributes['avatar'];
        return $value ? (preg_match('/^https?:\/\//i', $value) === 0 ? Storage::url($value) : $value) : asset('images/user.jpg');
    }

    public function getStatusNameAttribute()
    {
        return data_get(self::$statusMap, $this->status);
    }

    public function isNormal()
    {
        return $this->status === self::STATUS_NORMAL;
    }
}
