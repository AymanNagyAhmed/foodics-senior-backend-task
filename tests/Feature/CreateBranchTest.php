<?php

namespace Tests\Feature;

use App\Models\Branch;
use Tests\TestCase;

class CreateBranchTest extends TestCase
{
    public function test_create_branch(): void
    {
        $this->postJson('/api/branches', Branch::factory()->make()->toArray())->assertOk();
    }
}
