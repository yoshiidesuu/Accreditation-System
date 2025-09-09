<?php

namespace Tests\Unit;

use App\Models\College;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollegeModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user for foreign key constraints
        User::factory()->create(['id' => 1]);
    }

    /** @test */
    public function it_can_create_a_college_with_required_fields()
    {
        $college = College::create([
            'name' => 'College of Engineering',
            'code' => 'COE',
            'address' => '123 University Ave',
            'contact' => '+1234567890',
        ]);

        $this->assertInstanceOf(College::class, $college);
        $this->assertEquals('College of Engineering', $college->name);
        $this->assertEquals('COE', $college->code);
        $this->assertEquals('123 University Ave', $college->address);
        $this->assertEquals('+1234567890', $college->contact);
    }

    /** @test */
    public function it_casts_meta_attribute_to_array()
    {
        $college = College::create([
            'name' => 'Test College',
            'code' => 'TC',
            'meta' => ['established' => 1990, 'accredited' => true],
        ]);

        $this->assertIsArray($college->meta);
        $this->assertEquals(1990, $college->meta['established']);
        $this->assertTrue($college->meta['accredited']);
    }

    /** @test */
    public function it_can_have_a_coordinator()
    {
        $coordinator = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Coordinator',
        ]);

        $college = College::create([
            'name' => 'Test College',
            'code' => 'TC',
            'coordinator_id' => $coordinator->id,
        ]);

        $this->assertInstanceOf(User::class, $college->coordinator);
        $this->assertEquals($coordinator->id, $college->coordinator->id);
        $this->assertEquals('John Coordinator', $college->coordinator->name);
    }

    /** @test */
    public function it_can_have_multiple_users()
    {
        $college = College::factory()->create();
        
        $user1 = User::factory()->create(['college_id' => $college->id]);
        $user2 = User::factory()->create(['college_id' => $college->id]);

        $this->assertCount(2, $college->users);
        $this->assertTrue($college->users->contains($user1));
        $this->assertTrue($college->users->contains($user2));
    }

    /** @test */
    public function it_can_exist_without_coordinator()
    {
        $college = College::create([
            'name' => 'Independent College',
            'code' => 'IC',
            'coordinator_id' => null,
        ]);

        $this->assertNull($college->coordinator);
    }

    /** @test */
    public function it_can_store_complex_meta_data()
    {
        $metaData = [
            'established' => 1985,
            'accreditation' => [
                'status' => 'active',
                'expires' => '2025-12-31',
                'level' => 'Level IV'
            ],
            'programs' => ['Engineering', 'Computer Science', 'Mathematics'],
            'statistics' => [
                'students' => 1500,
                'faculty' => 85,
                'staff' => 25
            ]
        ];

        $college = College::create([
            'name' => 'Advanced College',
            'code' => 'AC',
            'meta' => $metaData,
        ]);

        $this->assertEquals($metaData, $college->meta);
        $this->assertEquals('active', $college->meta['accreditation']['status']);
        $this->assertCount(3, $college->meta['programs']);
        $this->assertEquals(1500, $college->meta['statistics']['students']);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        College::create([
            'code' => 'TC',
            // Missing required name field
        ]);
    }

    /** @test */
    public function it_ensures_unique_code()
    {
        College::factory()->create(['code' => 'UNIQUE']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        College::factory()->create(['code' => 'UNIQUE']);
    }

    /** @test */
    public function it_can_update_college_information()
    {
        $college = College::factory()->create([
            'name' => 'Old Name',
            'address' => 'Old Address',
        ]);

        $college->update([
            'name' => 'New Name',
            'address' => 'New Address',
        ]);

        $this->assertEquals('New Name', $college->fresh()->name);
        $this->assertEquals('New Address', $college->fresh()->address);
    }

    /** @test */
    public function it_can_delete_college()
    {
        $college = College::factory()->create();
        $collegeId = $college->id;

        $college->delete();

        $this->assertNull(College::find($collegeId));
    }
}