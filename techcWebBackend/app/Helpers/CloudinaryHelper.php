<?php

namespace App\Helpers;

use Cloudinary\Cloudinary;

class CloudinaryHelper
{
    public static function upload($file, $folder = 'techc')
    {
        $cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
            'url' => [
                'secure' => true,
            ],
        ]);

        $upload = $cloudinary->uploadApi()->upload(
            $file->getRealPath(),
            [
                'folder' => $folder
            ]
        );

        return $upload['secure_url'];
    }
}