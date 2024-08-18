<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'thumbnail', 'featured_images', 'banner', 'category_id', 'published'];

    public function image_set() : HasMany
    {
        return $this->hasMany(ImageSet::class);
    }

    public function author() : BelongsTo
    {
        return $this->belongsTo(Author::class, 'author_id');
    }
}
