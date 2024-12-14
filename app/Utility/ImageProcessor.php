<?php

namespace App\Utility;

use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ImageProcessor
{
    public static function processImage(Request $request, $destinationPath = 'images', $width = 300, $height = 200)
    {
        // Validate the uploaded file
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,svg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()];
        }

        // Get the uploaded image from the request
        $image = $request->file('image');
        $timestamp = time();
        $filename = $timestamp . '.png'; // Always save as .png

        // Move the original image to the 'uploads' directory
        $image->move(public_path('uploads'), $filename);

        // Initialize the ImageManager
        $manager = new ImageManager(new Driver());

        // Read the image file
        $imageInstance = $manager->read(public_path('uploads/' . $filename));

        // Resize the image
        $imageInstance->resize($width, $height);

        // Ensure the destination directory exists
        $destination = public_path($destinationPath);
        if (!file_exists($destination)) {
            mkdir($destination, 0777, true);
        }

        // Save the resized image to the destination path
        $imageInstance->save($destination . '/' . $filename);

        return [
            'success' => true,
            'message' => 'Image uploaded and formatted successfully!',
            'image_url' => asset($destinationPath . '/' . $filename),
        ];
    }
}
