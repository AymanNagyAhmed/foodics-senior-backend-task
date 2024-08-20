<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;


class OrderTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    /**
     *
     * @var string $route
     */
    private $route;

    /**
     * Represents the private property $branch.
     */
    private $branches;

    /**
     * @var Collection $products
     *
     * Description: This variable holds the products for the order.
     */
    private $products;

    /**
     * Set up the test
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->route = '/api/orders';
        $this->branches = Branch::factory()->count(3)->create();
        $this->products = Product::factory()->count(3)->create();
    }

    /**
     * Tear down the test environment.
     *
     * This method is called after each test method is executed.
     * It deletes all records from the OrderItem, Order, Branch, and Product tables.
     * It then calls the parent's tearDown method.
     */
    public function tearDown(): void
    {
        OrderItem::query()->delete();
        Order::query()->delete();
        Branch::query()->delete();
        Product::query()->delete();
        parent::tearDown();
    }

    /**
     * Test the rate limit of creating a new order for a single branch.
     *
     * This test function checks the rate limit functionality for creating a new order for a single branch. It performs the following steps:
     * 1. Retrieves the ID of the first branch from the database.
     * 2. Defines an array of test items with their corresponding product IDs and quantities.
     * 3. Retrieves the rate limit configuration for the branch from the application configuration file.
     * 4. Constructs an order data array with the branch ID and test items.
     * 5. Executes a loop to make multiple POST requests to create orders until the rate limit is reached.
     * 6. Asserts that the response status code for each request is 201 (Created).
     * 7. Executes an additional POST request to create an order after the rate limit is reached.
     * 8. Asserts that the response status code is 429 (Too Many Requests).
     * 9. Asserts the JSON structure and checks if the 'success' key in the JSON response is set to false.
     *
     * @return void
     */
    public function test_rate_limit_of_create_a_new_order_single_branch(): void
    {
        $testBranchId = $this->branches->first()->id;
        $testItems = [
            ['product_id' => $this->products->first()->id, 'quantity' => 2],
            ['product_id' => $this->products->last()->id, 'quantity' => 5]
        ];
        $branchConfig = config("rate_limits.branches.{$testBranchId}", config('rate_limits.default'));
        $orderData = [
            'name' => 'test',
            'branch_id' => $testBranchId,
            'items' => $testItems
        ];
        for ($i = 0; $i < $branchConfig['attempts']; $i++) {
            $response = $this->postJson($this->route, $orderData);
            $response->assertStatus(201);
        }

        $response = $this->postJson($this->route, $orderData);

        $response->assertStatus(429);
        $response->assertJsonStructure();
        $response->assertJsonFragment(['success' => false]);
    }


    /**
     * Test the rate limit of creating a new order for multiple branches.
     *
     * This test method checks the rate limit functionality when creating a new order for multiple branches. It performs the following steps:
     * 1. Retrieves the IDs of the first and last branches from the `$branches` collection.
     * 2. Defines an array of test items, each containing a product ID and quantity.
     * 3. Retrieves the rate limit configuration for the branch with ID `$testBranchId` from the `rate_limits.branches` configuration, falling back to the `rate_limits.default` configuration if not found.
     * 4. Defines an order data array with the name, branch ID, and test items for the branch with ID `$testBranchId`.
     * 5. Defines an order data array with the name, branch ID, and test items for the branch with ID `$testBranchId2`.
     * 6. Executes a loop for the number of attempts specified in the branch configuration.
     *    - Sends a POST request to the specified route with the order data for the branch with ID `$testBranchId`.
     *    - Asserts that the response status is 201 (Created).
     *    - Sends a POST request to the specified route with the order data for the branch with ID `$testBranchId2`.
     *    - Asserts that the response status is 201 (Created).
     * 7. Sends a POST request to the specified route with the order data for the branch with ID `$testBranchId`.
     *    - Asserts that the response status is 429 (Too Many Requests).
     *    - Asserts the JSON structure of the response.
     *    - Asserts that the response JSON contains the fragment ['success' => false].
     */
    public function test_rate_limit_of_create_a_new_order_multiple_branch(): void
    {
        $testBranchId = $this->branches->first()->id;
        $testBranchId2 = $this->branches->last()->id;
        $testItems = [
            ['product_id' => $this->products->first()->id, 'quantity' => 2],
            ['product_id' => $this->products->last()->id, 'quantity' => 5]
        ];
        $branchConfig = config("rate_limits.branches.{$testBranchId}", config('rate_limits.default'));
        $orderData = [
            'name' => 'test',
            'branch_id' => $testBranchId,
            'items' => $testItems
        ];
        $orderData2 = [
            'name' => 'test',
            'branch_id' => $testBranchId2,
            'items' => $testItems
        ];
        for ($i = 0; $i < $branchConfig['attempts']; $i++) {
            $response = $this->postJson($this->route, $orderData);

            $response->assertStatus(201);
            $response2 = $this->postJson($this->route, $orderData2);
            $response2->assertStatus(201);
        }

        $response = $this->postJson($this->route, $orderData);

        $response->assertStatus(429);
        $response->assertJsonStructure();
        $response->assertJsonFragment(['success' => false]);
    }
    public function test_create_a_new_order(): void
    {
        $testBranchId = $this->branches->first()->id;
        $testItems = [
            ['product_id' => $this->products->first()->id, 'quantity' => 2],
            ['product_id' => $this->products->last()->id, 'quantity' => 5]
        ];
        $orderData = [
            'name' => 'test',
            'branch_id' => $testBranchId,
            'items' => $testItems
        ];

        $response = $this->postJson($this->route, $orderData);

        $response->assertStatus(201);
        $response->assertJsonStructure();
        $response->assertJsonFragment(['success' => true]);
        $this->assertDatabaseHas('orders', ['id' => $response['data']['order']['id']]);
    }

    /**
     * Test case for creating a new order with a required name.
     *
     * @return void
     */
    public function test_create_a_new_order_name_is_required(): void
    {
        $testBranchId = $this->branches->first()->id;
        $testItems = [
            ['product_id' => $this->products->first()->id, 'quantity' => 2],
            ['product_id' => $this->products->last()->id, 'quantity' => 5]
        ];
        $orderData = [
            'branch_id' => $testBranchId,
            'items' => $testItems
        ];

        $response = $this->postJson($this->route, $orderData);

        $response->assertStatus(422);
        $response->assertJsonStructure();
        $response->assertJsonFragment(['success' => false]);
        $response->assertJson([
            'errors' => [
                'name' => [
                    'The name field is required.'
                ]
            ]
        ]);
    }
}
