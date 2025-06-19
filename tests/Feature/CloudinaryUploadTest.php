<?php

namespace Tests\Feature;

use Tests\TestCase;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Cloudinary\Cloudinary as CloudinarySDK;

class CloudinaryUploadTest extends TestCase
{
    /** @test */
   public function it_uploads_an_image_to_cloudinary_successfully()
{
    $cloudinaryConfig = config('cloudinary.cloud');
    $this->assertNotNull($cloudinaryConfig, 'Cloudinary config missing');

    // Initialize SDK client
    $client = new \Cloudinary\Cloudinary([
        'cloud' => $cloudinaryConfig,
        'url' => ['secure' => true],
    ]);

    $imagePath = public_path('images/test.jpeg');
    $this->assertFileExists($imagePath, 'Test image file does not exist at ' . $imagePath);

    try {
        // Using SDK directly:
        $uploadResult = $client->uploadApi()->upload($imagePath);
    } catch (\Exception $e) {
        $this->fail('Cloudinary SDK upload threw exception: ' . $e->getMessage());
    }

    $secureUrl = $uploadResult['secure_url'] ?? null;
    $this->assertNotEmpty($secureUrl, 'Failed to get uploaded image URL from Cloudinary.');

    fwrite(STDERR, "Uploaded image URL: $secureUrl" . PHP_EOL);
}

}
