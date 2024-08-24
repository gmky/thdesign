<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Author extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'avatar', 'email', 'job_title', 'tags'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function avatar() : Attribute
    {
        return Attribute::make(
            get: fn(string | null $value) => $value ? Storage::disk('public')->url($value) : null,
        );
    }

    public function tags() : Attribute
    {
        return Attribute::make(
            get: fn(string | null $value) => json_decode($value),
            set: fn(array $value) => json_encode($value)
        );
    }
}
