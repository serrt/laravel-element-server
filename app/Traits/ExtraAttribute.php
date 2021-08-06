<?php

namespace App\Traits;

trait ExtraAttribute
{
    public function setExtraAttribute($value)
    {
        $this->attributes['extra'] = json_encode($this->extra ? array_merge($this->extra, $value) : $value);
    }
}
