<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Model\Product;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
  public function it_can_list_products(){
    //seeder some products
    Product::factory()->count(3)->create();
    // Act: Make a GET request to the product listing endpoint
    $response = $this->get('api/products');
     // Assert: Verify the response contains the products
     $response->assertStatus(200);
     $response->assertJsonCount(3);
  }

 
}
