<?php

namespace Tests\Unit;

use App\Models\Parameter;
use App\Models\User;
use App\Models\ParameterContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParameterModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user for foreign key constraints
        User::factory()->create(['id' => 1]);
    }

    /** @test */
    public function it_can_create_a_parameter_with_required_fields()
    {
        $parameter = Parameter::create([
            'code' => 'A1',
            'title' => 'Test Parameter',
            'description' => 'This is a test parameter',
            'category' => 'A',
            'subcategory' => '1',
            'weight' => 3,
            'status' => 'active',
            'required_documents' => ['document1', 'document2'],
            'evaluation_criteria' => 'Test criteria',
            'created_by' => 1,
        ]);

        $this->assertInstanceOf(Parameter::class, $parameter);
        $this->assertEquals('A1', $parameter->code);
        $this->assertEquals('Test Parameter', $parameter->title);
        $this->assertEquals('This is a test parameter', $parameter->description);
        $this->assertEquals('A', $parameter->category);
        $this->assertEquals('1', $parameter->subcategory);
        $this->assertEquals(3, $parameter->weight);
        $this->assertEquals('active', $parameter->status);
        $this->assertEquals(1, $parameter->created_by);
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $parameter = Parameter::factory()->create([
            'required_documents' => ['doc1', 'doc2'],
            'weight' => 5,
        ]);

        $this->assertIsArray($parameter->required_documents);
        $this->assertIsInt($parameter->weight);
        $this->assertEquals(['doc1', 'doc2'], $parameter->required_documents);
        $this->assertEquals(5, $parameter->weight);
    }

    /** @test */
    public function it_belongs_to_a_creator()
    {
        $user = User::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
        $parameter = Parameter::factory()->create(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $parameter->creator);
        $this->assertEquals($user->id, $parameter->creator->id);
        $this->assertEquals('John Doe', $parameter->creator->name);
    }

    /** @test */
    public function it_can_have_parameter_contents()
    {
        $parameter = Parameter::factory()->create();
        $content1 = ParameterContent::factory()->create(['parameter_id' => $parameter->id]);
        $content2 = ParameterContent::factory()->create(['parameter_id' => $parameter->id]);

        $this->assertCount(2, $parameter->parameterContents);
        $this->assertTrue($parameter->parameterContents->contains($content1));
        $this->assertTrue($parameter->parameterContents->contains($content2));
    }

    /** @test */
    public function it_can_scope_active_parameters()
    {
        $activeParam = Parameter::factory()->create(['status' => 'active']);
        $inactiveParam = Parameter::factory()->create(['status' => 'inactive']);

        $activeParams = Parameter::active()->get();

        $this->assertCount(1, $activeParams);
        $this->assertTrue($activeParams->contains($activeParam));
        $this->assertFalse($activeParams->contains($inactiveParam));
    }

    /** @test */
    public function it_can_scope_parameters_by_category()
    {
        $categoryAParam = Parameter::factory()->create(['category' => 'A']);
        $categoryBParam = Parameter::factory()->create(['category' => 'B']);

        $categoryAParams = Parameter::byCategory('A')->get();

        $this->assertCount(1, $categoryAParams);
        $this->assertTrue($categoryAParams->contains($categoryAParam));
        $this->assertFalse($categoryAParams->contains($categoryBParam));
    }

    /** @test */
    public function it_can_scope_ordered_parameters()
    {
        $param3 = Parameter::factory()->create(['category' => 'C', 'subcategory' => '1', 'title' => 'C']);
        $param1 = Parameter::factory()->create(['category' => 'A', 'subcategory' => '1', 'title' => 'A']);
        $param2 = Parameter::factory()->create(['category' => 'B', 'subcategory' => '1', 'title' => 'B']);

        $orderedParams = Parameter::ordered()->get();

        $this->assertEquals($param1->id, $orderedParams->first()->id);
        $this->assertEquals($param3->id, $orderedParams->last()->id);
    }

    /** @test */
    public function it_has_evaluation_criteria()
    {
        $parameter = Parameter::factory()->create([
            'evaluation_criteria' => 'Test evaluation criteria for this parameter'
        ]);

        $this->assertEquals('Test evaluation criteria for this parameter', $parameter->evaluation_criteria);
    }

    /** @test */
    public function it_has_required_documents()
    {
        $parameter = Parameter::factory()->create([
            'required_documents' => ['document1.pdf', 'document2.docx']
        ]);

        $this->assertIsArray($parameter->required_documents);
        $this->assertEquals(['document1.pdf', 'document2.docx'], $parameter->required_documents);
    }

    /** @test */
    public function it_checks_if_parameter_has_content()
    {
        $paramWithContent = Parameter::factory()->create();
        $paramWithoutContent = Parameter::factory()->create();
        
        ParameterContent::factory()->create(['parameter_id' => $paramWithContent->id]);

        $this->assertTrue($paramWithContent->hasContent());
        $this->assertFalse($paramWithoutContent->hasContent());
    }

    /** @test */
    public function it_can_get_content_for_specific_user()
    {
        $parameter = Parameter::factory()->create();
        $userId = 1;
        $otherUserId = 2;
        
        $userContent = ParameterContent::factory()->create([
            'parameter_id' => $parameter->id,
            'uploaded_by' => $userId,
            'content' => 'User content'
        ]);
        
        ParameterContent::factory()->create([
            'parameter_id' => $parameter->id,
            'uploaded_by' => $otherUserId,
            'content' => 'Other user content'
        ]);

        $content = $parameter->getContentForUser($userId);

        $this->assertInstanceOf(ParameterContent::class, $content);
        $this->assertEquals('User content', $content->content);
        $this->assertEquals($userId, $content->uploaded_by);
    }





    /** @test */
    public function it_uses_soft_deletes()
    {
        $parameter = Parameter::factory()->create();
        $parameterId = $parameter->id;

        $parameter->delete();

        $this->assertNull(Parameter::find($parameterId));
        $this->assertNotNull(Parameter::withTrashed()->find($parameterId));
        $this->assertTrue(Parameter::withTrashed()->find($parameterId)->trashed());
    }

    /** @test */
    public function it_can_restore_soft_deleted_parameter()
    {
        $parameter = Parameter::factory()->create();
        $parameterId = $parameter->id;

        $parameter->delete();
        $this->assertNull(Parameter::find($parameterId));

        Parameter::withTrashed()->find($parameterId)->restore();
        $this->assertNotNull(Parameter::find($parameterId));
    }
}