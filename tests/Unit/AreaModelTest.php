<?php

namespace Tests\Unit;

use App\Models\Area;
use App\Models\College;
use App\Models\AcademicYear;
use App\Models\Parameter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AreaModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create users and colleges for foreign key constraints
        User::factory()->create(['id' => 1]);
        College::factory()->create(['id' => 1]);
        AcademicYear::factory()->create(['id' => 1]);
    }

    /** @test */
    public function it_can_create_an_area_with_required_fields()
    {
        $college = College::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $area = Area::create([
            'code' => 'AREA001',
            'title' => 'Test Area',
            'description' => 'This is a test area',
            'college_id' => $college->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $this->assertInstanceOf(Area::class, $area);
        $this->assertEquals('AREA001', $area->code);
        $this->assertEquals('Test Area', $area->title);
        $this->assertEquals('This is a test area', $area->description);
        $this->assertEquals($college->id, $area->college_id);
        $this->assertEquals($academicYear->id, $area->academic_year_id);
    }

    /** @test */
    public function it_belongs_to_a_college()
    {
        $college = College::factory()->create(['name' => 'Engineering College']);
        $area = Area::factory()->create(['college_id' => $college->id]);

        $this->assertInstanceOf(College::class, $area->college);
        $this->assertEquals($college->id, $area->college->id);
        $this->assertEquals('Engineering College', $area->college->name);
    }

    /** @test */
    public function it_belongs_to_an_academic_year()
    {
        $academicYear = AcademicYear::factory()->create(['year' => '2024-2025']);
        $area = Area::factory()->create(['academic_year_id' => $academicYear->id]);

        $this->assertInstanceOf(AcademicYear::class, $area->academicYear);
        $this->assertEquals($academicYear->id, $area->academicYear->id);
        $this->assertEquals('2024-2025', $area->academicYear->year);
    }

    /** @test */
    public function it_can_have_a_parent_area()
    {
        $parentArea = Area::factory()->create(['title' => 'Parent Area']);
        $childArea = Area::factory()->create([
            'title' => 'Child Area',
            'parent_area_id' => $parentArea->id,
        ]);

        $this->assertInstanceOf(Area::class, $childArea->parentArea);
        $this->assertEquals($parentArea->id, $childArea->parentArea->id);
        $this->assertEquals('Parent Area', $childArea->parentArea->title);
    }

    /** @test */
    public function it_can_have_child_areas()
    {
        $parentArea = Area::factory()->create();
        $child1 = Area::factory()->create(['parent_area_id' => $parentArea->id]);
        $child2 = Area::factory()->create(['parent_area_id' => $parentArea->id]);

        $this->assertCount(2, $parentArea->childAreas);
        $this->assertTrue($parentArea->childAreas->contains($child1));
        $this->assertTrue($parentArea->childAreas->contains($child2));
    }

    /** @test */
    public function it_can_have_parameters()
    {
        $area = Area::factory()->create();
        $parameter1 = Parameter::factory()->create(['area_id' => $area->id]);
        $parameter2 = Parameter::factory()->create(['area_id' => $area->id]);

        $this->assertCount(2, $area->parameters);
        $this->assertTrue($area->parameters->contains($parameter1));
        $this->assertTrue($area->parameters->contains($parameter2));
    }

    /** @test */
    public function it_can_scope_root_areas()
    {
        $rootArea = Area::factory()->create(['parent_area_id' => null]);
        $childArea = Area::factory()->create(['parent_area_id' => $rootArea->id]);

        $rootAreas = Area::roots()->get();

        $this->assertCount(1, $rootAreas);
        $this->assertTrue($rootAreas->contains($rootArea));
        $this->assertFalse($rootAreas->contains($childArea));
    }

    /** @test */
    public function it_can_scope_areas_by_college()
    {
        $college1 = College::factory()->create();
        $college2 = College::factory()->create();
        
        $area1 = Area::factory()->create(['college_id' => $college1->id]);
        $area2 = Area::factory()->create(['college_id' => $college2->id]);

        $college1Areas = Area::byCollege($college1->id)->get();

        $this->assertCount(1, $college1Areas);
        $this->assertTrue($college1Areas->contains($area1));
        $this->assertFalse($college1Areas->contains($area2));
    }

    /** @test */
    public function it_can_scope_areas_by_academic_year()
    {
        $year1 = AcademicYear::factory()->create();
        $year2 = AcademicYear::factory()->create();
        
        $area1 = Area::factory()->create(['academic_year_id' => $year1->id]);
        $area2 = Area::factory()->create(['academic_year_id' => $year2->id]);

        $year1Areas = Area::byAcademicYear($year1->id)->get();

        $this->assertCount(1, $year1Areas);
        $this->assertTrue($year1Areas->contains($area1));
        $this->assertFalse($year1Areas->contains($area2));
    }

    /** @test */
    public function it_generates_full_hierarchical_path()
    {
        $grandparent = Area::factory()->create(['title' => 'Grandparent']);
        $parent = Area::factory()->create([
            'title' => 'Parent',
            'parent_area_id' => $grandparent->id,
        ]);
        $child = Area::factory()->create([
            'title' => 'Child',
            'parent_area_id' => $parent->id,
        ]);

        $this->assertEquals('Grandparent', $grandparent->full_path);
        $this->assertEquals('Grandparent > Parent', $parent->full_path);
        $this->assertEquals('Grandparent > Parent > Child', $child->full_path);
    }

    /** @test */
    public function it_calculates_depth_level_correctly()
    {
        $level0 = Area::factory()->create(['parent_area_id' => null]);
        $level1 = Area::factory()->create(['parent_area_id' => $level0->id]);
        $level2 = Area::factory()->create(['parent_area_id' => $level1->id]);

        $this->assertEquals(0, $level0->depth_level);
        $this->assertEquals(1, $level1->depth_level);
        $this->assertEquals(2, $level2->depth_level);
    }

    /** @test */
    public function it_checks_if_area_has_children()
    {
        $parentArea = Area::factory()->create();
        $childlessArea = Area::factory()->create();
        
        Area::factory()->create(['parent_area_id' => $parentArea->id]);

        $this->assertTrue($parentArea->hasChildren());
        $this->assertFalse($childlessArea->hasChildren());
    }

    /** @test */
    public function it_uses_soft_deletes()
    {
        $area = Area::factory()->create();
        $areaId = $area->id;

        $area->delete();

        // Should not be found in normal queries
        $this->assertNull(Area::find($areaId));
        
        // Should be found with trashed
        $this->assertNotNull(Area::withTrashed()->find($areaId));
        $this->assertTrue(Area::withTrashed()->find($areaId)->trashed());
    }

    /** @test */
    public function it_can_restore_soft_deleted_area()
    {
        $area = Area::factory()->create();
        $areaId = $area->id;

        $area->delete();
        $this->assertNull(Area::find($areaId));

        Area::withTrashed()->find($areaId)->restore();
        $this->assertNotNull(Area::find($areaId));
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Area::create([
            'title' => 'Test Area',
            // Missing required code field
        ]);
    }

    /** @test */
    public function it_can_update_area_information()
    {
        $area = Area::factory()->create([
            'title' => 'Old Title',
            'description' => 'Old Description',
        ]);

        $area->update([
            'title' => 'New Title',
            'description' => 'New Description',
        ]);

        $this->assertEquals('New Title', $area->fresh()->title);
        $this->assertEquals('New Description', $area->fresh()->description);
    }
}