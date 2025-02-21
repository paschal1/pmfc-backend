<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    use RefreshDatabase;

    public function it_can_create_a_product()
    {
        $productData = [
            'name' => 'Chair',
            'price' => 99.99,
            'description' => 'A comfortable chair',
            'stock' => 5,
        ];
    
        $response = $this->post('/api/products', $productData);
    
        $response->assertStatus(201);
        $this->assertDatabaseHas('products', ['name' => 'Chair']);
    }
    
    public function it_can_delete_a_product()
    {
        $product = Product::factory()->create();
    
        $response = $this->delete('/api/products/' . $product->id);
    
        $response->assertStatus(200);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
    
}
