<?php

namespace Tests\Feature;

use App\Models\StockAttribute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StockModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_can_be_created_and_edited_with_the_shared_form(): void
    {
        $category = StockAttribute::where('type', 'category')->firstOrFail();
        $subcategory = StockAttribute::create(['type' => 'subcategory', 'name' => 'Test Subcategory', 'parent_id' => $category->id]);
        Storage::fake('local');
        $values = ['product_name' => 'Test Product', 'product_code' => 'TP-01', 'stock_quantity' => 12, 'price_a' => '120.75', 'category_id' => $category->id, 'subcategory_id' => $subcategory->id];
        $image = UploadedFile::fake()->createWithContent('product.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jR1sAAAAASUVORK5CYII='));

        $this->get(route('inventory.products.create'))->assertOk()->assertViewIs('product-form')->assertDontSee('name="stock_quantity"', false)->assertDontSee('name="price_a"', false);
        $this->post(route('inventory.products.store'), [])->assertSessionHasErrors(['product_name']);
        $stockProducts = route('inventory.stock.index', ['type' => 'products']);
        $this->get($stockProducts)->assertOk()->assertSee('Add Product');
        $this->post(route('inventory.products.store', ['from' => 'stock']), $values + ['image' => $image])->assertRedirect($stockProducts);
        $code = \Illuminate\Support\Facades\DB::table('products')->where('product_id', 1)->value('product_code');
        $this->assertStringStartsWith('MS-', $code);
        $this->get($stockProducts)->assertOk()->assertSee('Test Product')->assertSee($code);
        $this->get(route('purchase.invoices.create'))->assertOk()->assertSee('Test Product · '.$code);
        $this->get(route('sales.index'))->assertOk()->assertSee('data-code="'.$code.'"', false);
        $this->assertDatabaseHas('products', ['product_id' => 1, 'product_code' => $code, 'stock_quantity' => 0, 'price_a' => '0', 'brand' => '', 'model' => '', 'unit' => 'pcs']);
        $this->get(route('inventory.products.edit', 1))->assertOk()->assertViewIs('product-form')->assertSee('Test Product');
        $this->patch(route('inventory.products.update', 1), array_replace($values, ['product_name' => 'Updated Product', 'unit' => 'box']))
            ->assertRedirect(route('inventory.index'));
        $this->get(route('inventory.index'))->assertOk()->assertSee('Updated Product')->assertSee($category->name)->assertSee('120.75')->assertSee('TP-01')->assertSee('data-quantity="12"', false);
        $this->get(route('inventory.products.image', 1))->assertOk();
        $this->get(route('sales.index'))->assertOk()->assertSee('data-unit="box"', false)
            ->assertSee('data-category="'.$category->name.'"', false)->assertSee('data-subcategory="Test Subcategory"', false);
        $this->delete(route('inventory.products.destroy', ['product' => 1, 'from' => 'stock']))->assertRedirect($stockProducts);
        $this->assertSoftDeleted('products', ['product_id' => 1]);
        $this->get(route('inventory.index'))->assertOk()->assertDontSee('Updated Product');
        $this->get(route('inventory.products.edit', 1))->assertNotFound();
    }

    public function test_a_brand_can_be_created_renamed_and_deleted_from_product_suggestions(): void
    {
        $index = route('inventory.stock.index', ['type' => 'brand']);

        $this->post(route('inventory.stock.store'), ['type' => 'brand', 'name' => 'Example Brand'])
            ->assertRedirect($index);
        $brand = StockAttribute::where('name', 'Example Brand')->firstOrFail();

        $this->get($index)->assertOk()->assertSee('Example Brand');
        $this->get(route('inventory.stock.edit', $brand))->assertOk()->assertSee('Edit Brand');
        $this->patch(route('inventory.stock.update', $brand), ['name' => 'Updated Brand'])
            ->assertRedirect($index);
        $this->get(route('inventory.index'))->assertOk()->assertSee('value="Updated Brand"', false);

        $this->delete(route('inventory.stock.destroy', $brand))->assertRedirect($index);
        $this->assertModelMissing($brand);
        $this->get(route('inventory.index'))->assertOk()->assertDontSee('Updated Brand');
    }

    public function test_a_category_with_subcategories_cannot_be_deleted(): void
    {
        $category = StockAttribute::where('type', 'category')->firstOrFail();

        $this->post(route('inventory.stock.store'), ['type' => 'subcategory', 'name' => 'Example Subcategory', 'parent_id' => $category->id])
            ->assertRedirect(route('inventory.stock.index', ['type' => 'subcategory']));
        $this->get(route('inventory.stock.index', ['type' => 'subcategory']))
            ->assertOk()->assertSee('Parent Category')->assertSee('Example Subcategory')->assertSee($category->name);
        $this->get(route('inventory.stock.index', ['type' => 'category']))
            ->assertOk()->assertSee('Child Categories')->assertSee('Example Subcategory');

        $this->delete(route('inventory.stock.destroy', $category))->assertSessionHasErrors('delete');
        $this->assertModelExists($category);
    }
}
