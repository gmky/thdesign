<?php

use App\Models\Image;
use App\Models\ImageSet;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
   $image_set_arr = ImageSet::whereNull('product_id')->get();
   Log::info('=== Start clear image set at '.date('Y-m-d H:i:s'));
   Log::info('Removing '.$image_set_arr->count().' image sets');
   foreach ($image_set_arr as $image_set) {
       $images = Image::where('image_set_id', $image_set->id)->get();
       Log::info('Removing '.$images->count().' images of image set ID: '.$image_set->id);
       foreach ($images as $image) {
           Storage::disk('public')->delete($image->path);
           $image->delete();
       }
       $image_set->delete();
   }
})->daily();
