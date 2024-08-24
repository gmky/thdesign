<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'thumbnail', 'featured_images', 'banner', 'category_id', 'published', 'external_link'];

    public function image_set() : HasMany
    {
        return $this->hasMany(ImageSet::class)->orderBy('id');
    }

    public function author() : BelongsTo
    {
        return $this->belongsTo(Author::class, 'author_id');
    }

    public function thumbnail() : Attribute
    {
        return Attribute::make(
            get: fn(string | null $value) => $value ? Storage::disk('public')->url($value) : null,
        );
    }

    public function banner() : Attribute
    {
        return Attribute::make(
            get: fn(string | null $value) => $value ? Storage::disk('public')->url($value) : null,
        );
    }
}
