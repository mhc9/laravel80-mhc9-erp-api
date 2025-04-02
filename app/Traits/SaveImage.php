<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait SaveImage
{
    public function saveImage($image, $destPath = 'uploads/')
    {
        if ($image) {
            $fileName = date('mdYHis') . uniqid(). '.' .$image->getClientOriginalExtension();

            return $image->storeAs($destPath, $fileName, 'public');
        }

        return '';
    }
}
