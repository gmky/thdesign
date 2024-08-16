<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImageSet extends Model
{
    use HasFactory;

    protected $table = 'image_set';

    public function images() : HasMany
    {
        return $this->hasMany(Image::class, 'image_set_id');
    }

    public function product() : BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function scopeAbandoned($query) {
        return $query->whereNull('product_id');
    }
}
