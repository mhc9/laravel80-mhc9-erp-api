<?php

namespace App\Traits;

trait SaveImage
{
    public function saveImage($image, $destPath = 'uploads/')
    {
        if ($image) {
            $fileName = date('mdYHis') . uniqid(). '.' .$image->getClientOriginalExtension();

            if ($image->move($destPath, $fileName)) {
                return $fileName;
            }
        }

        return '';
    }
}
