<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CompanyAdditionalNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_companies_table_has_additional_number_column(): void
    {
        $this->assertTrue(Schema::hasColumn('companies', 'additional_number'));
    }
}
