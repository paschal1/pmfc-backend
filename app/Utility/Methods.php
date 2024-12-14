<?php

namespace App\Utility;


use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Intervention\Image\Laravel\Facades\Image;


class Methods
{
    
    public static function uploadFile($file, $path = null)
    {
        //Get the Original File Name and path
        $thumbnail = $file->getClientOriginalName();
        //Get the filename only using native php 'pathinfo'
        $filename = pathinfo($thumbnail, PATHINFO_FILENAME);

        if($path == null) {
            $path = '/files'; //Save to files directory if no custom path is specified
        }

        // Check file type before saving
        $fileType = self::checkFileType($file);

        // If file is an image, save it as webp format
        if ($fileType['type'] === 'images') {
            //prepare the file to be stored
            $nameToStore = str_replace(' ', '_', $filename) . '_'. time() .'.png';
            // //upload the file
            $thumbnail->move('uploads', $nameToStore);
            // $image_resize = Image::make($file->getRealPath());
              // Initialize the ImageManager
            $manager = new ImageManager(new Driver());

            // Create the image instance from the content
            $image_resize = $manager->read("uploads/". $nameToStor);
        
            // To resize the image to a width of 600 and constrain aspect ratio (auto height)
            $image_resize->resize(900,  null, function ($constraint) {
                $constraint->aspectRatio();
                })->encode('webp', 100);

            // create the directory with permission
            $storePath = 'app/public/'.$fileType['type']. '/' .$path . '/';
            if (!file_exists(storage_path($storePath))) {
                mkdir(storage_path($storePath), 0777, true);
            }
            $stat_path = $fileType['type']. '/' .$path . '/';
            $static_path = env('APP_URL', 'https://market.shapcab.com'). '/storage/' . $stat_path;
            // $path = url().toString().$fileType['type'].$path;

            // Upload Image
            if($image_resize->save(storage_path($storePath . $nameToStore))){
                return [
                    'status' => true,
                    'name' => $nameToStore,
                    'path' => $static_path,
                    'type' => 'image',
                    'ext' => $fileType['ext'],
                ];
            } else {
                return [
                    'status' => false
                ];
            }
        }

        // If file is a document, retain its extension
        if ($fileType['type'] === 'documents') {
            // Save file
            $nameToStore = str_replace(' ', '_', $filename) . '_'. time() . '.' . $fileType['ext'];
            // $path = 'public/documents';

            // create the directory with permission
            // $path = 'public/'.$fileType['type'].$path;
            $storepath = 'public/'.$fileType['type']. '/' .$path. '/';

            if (!file_exists(storage_path($path))) {
                mkdir(storage_path($path), 0777, true);
            }
            $filepath = $file->storeAs(
                $storepath, $nameToStore
            );
            // $static_path = $fileType['type'].$path;
            $stat_path = $fileType['type']. '/' .$path. '/';
            $static_path = env('APP_URL', 'https://market.shapcab.com'). '/storage/' . $stat_path;
            return [
                'status' => true,
                'name' => $nameToStore,
                'path' => $static_path,
                'type' => 'document',
                'ext' => $fileType['ext'],
            ];
        }
    }

    public static function checkFileType($file)
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif' || $ext == 'webp' || $ext == 'tiff') {
            $type = 'images';
        }elseif($ext == 'pptx' || $ext == 'ppt' || $ext == 'docx' || $ext == 'doc' || $ext == 'pdf' || $ext == 'xlsx' || $ext == 'xls' || $ext == 'cdr' || $ext == 'psd'){
            $type = 'documents';
        }else{
            $type = 'file';
        }
        return [
            'type' => $type,
            'ext' => $ext
        ];
    }

    public static function EncryptData($balance){
        return Crypt::encryptString($balance);
    }

    // Will write this method in the future
    // Just testing out somethings
    public static function DecryptData($balance){
        try {
            return Crypt::decryptString($balance);
        } catch (DecryptException $e) {
            return $e;
        }
    }

    
}


