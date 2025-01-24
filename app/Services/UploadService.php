<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Image;

class UploadService
{
   public static function upload(object $request, string $save_to = "", string $image_name = "image")
   {
      if (!empty($request->file($image_name))) {
         $file_name = Str::random(30) . "." . $request->file($image_name)->getClientOriginalExtension();
         $request->{$image_name}->storeAs($save_to, $file_name, 'public');
         $data = array_merge($request->validated(), [$image_name => "/storage/$save_to/$file_name"]);
         return $data;
      }
      if (!empty($request->input("image_url"))) {
         $imageContents = file_get_contents($request->input("image_url"));
         $contentType = get_headers($request->input("image_url"), true)['Content-Type'];
         $extension = explode('/', $contentType)[1];
         $filename = uniqid() .".$extension";
         Storage::disk('public')->put("$save_to/$filename", $imageContents);
         $path = Storage::url("$save_to/$filename");
         return $path;
      }
      return $request->validated();
   }
}
