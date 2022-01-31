<?php

namespace App\Helpers;

use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Facade;

/**
 * Class Reply
 * @package App\Classes
 */
class Files
{

    /**
     * @param $image
     * @param $dir
     * @param null $width
     * @param int $height
     * @param $crop
     * @return string
     * @throws \Exception
     */

    public static function upload($image, $dir, $width = null, $height = 800, $crop = false)
    {
        config(['filesystems.default' => 'local']);

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $image;
        $folder = $dir . '/';

        if (!$uploadedFile->isValid()) {
            throw new \Exception('File was not uploaded correctly');
        }

        $newName = self::generateNewFileName($uploadedFile->getClientOriginalName());

        $tempPath = public_path('user-uploads/temp/' . $newName);

        /** Check if folder exits or not. If not then create the folder */
        if (!\File::exists(public_path('user-uploads/' . $folder))) {
            \File::makeDirectory(public_path('user-uploads/' . $folder), 0775, true);
        }

        $newPath = $folder . '/' . $newName;

        /** @var UploadedFile $uploadedFile */
        $uploadedFile->storeAs('temp', $newName);

        if ($crop) {

            $image = Image::make($tempPath);
            $imageWidth = $image->width();
            $imageHeight = $image->height();

            if ($imageHeight > $imageWidth) {
                $image->resize($width, false, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                $updatedImageHeight = $image->height();
                $y = floor(($updatedImageHeight - 1000) / 2);

                $image->crop($width, $height, 0, $y);


            } else {
                $image->resize(false, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                $updatedImageWidth = $image->width();
                $y = floor(($updatedImageWidth - 1000) / 2);

                $image->crop($width, $height, $y, 0);

            }

            $image->save();

        }

        \Storage::put($newPath, \File::get($tempPath), ['public']);

        // Deleting temp file
        \File::delete($tempPath);

        return $newName;
    }

    public static function generateNewFileName($currentFileName)
    {
        $ext = strtolower(\File::extension($currentFileName));
        $newName = md5(microtime());

        if ($ext === '') {
            return $newName;
        }

        return $newName . '.' . $ext;
    }

    public static function uploadLocalOrS3($uploadedFile, $dir)
    {
        if (!$uploadedFile->isValid()) {
            throw new \Exception('File was not uploaded correctly');
        }

        $newName = self::generateNewFileName($uploadedFile->getClientOriginalName());

        if(config('filesystems.default') === 'local'){
            return self::upload($uploadedFile,$dir,1000,1000,true);
        }

        Storage::disk('s3')->putFileAs($dir, $uploadedFile, $newName, 'public');
        return $newName;
    }

}
