<?php

namespace Tests\Feature;

use App\Models\Branch;
use Tests\TestCase;

class UpdateBranchTest extends TestCase
{
    public function test_update_branch(): void
    {
        $branch = Branch::factory()->create();

        $this->putJson('/api/branches/' . $branch->id, Branch::factory()->make()->toArray())->assertOk();
    }
}
