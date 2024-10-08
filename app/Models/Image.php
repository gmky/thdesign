<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    use HasFactory;

    protected $fillable = ['display_order'];

    public function image_set() : BelongsTo
    {
        return $this->belongsTo(ImageSet::class);
    }

    public function path() : Attribute
    {
        return Attribute::make(
            get: fn(string | null $value) => $value ? Storage::disk('public')->url($value) : null,
        );
    }
}
