<?php

namespace Tests\Unit;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\ParameterContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class ActivityLogModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create users for foreign key constraints
        User::factory()->create(['id' => 1]);
    }

    /** @test */
    public function it_can_create_an_activity_log_with_required_fields()
    {
        $user = User::factory()->create();
        $file = ParameterContent::factory()->create();

        $activityLog = ActivityLog::create([
            'log_name' => 'file_access',
            'description' => 'File was accessed',
            'subject_type' => ParameterContent::class,
            'subject_id' => $file->id,
            'event' => 'accessed',
            'causer_type' => User::class,
            'causer_id' => $user->id,
            'properties' => ['ip_address' => '192.168.1.1'],
        ]);

        $this->assertInstanceOf(ActivityLog::class, $activityLog);
        $this->assertEquals('file_access', $activityLog->log_name);
        $this->assertEquals('File was accessed', $activityLog->description);
        $this->assertEquals(ParameterContent::class, $activityLog->subject_type);
        $this->assertEquals($file->id, $activityLog->subject_id);
        $this->assertEquals('accessed', $activityLog->event);
        $this->assertEquals(User::class, $activityLog->causer_type);
        $this->assertEquals($user->id, $activityLog->causer_id);
    }

    /** @test */
    public function it_casts_properties_as_array()
    {
        $activityLog = ActivityLog::factory()->create([
            'properties' => ['key1' => 'value1', 'key2' => 'value2'],
        ]);

        $this->assertIsArray($activityLog->properties);
        $this->assertEquals('value1', $activityLog->properties['key1']);
        $this->assertEquals('value2', $activityLog->properties['key2']);
    }

    /** @test */
    public function it_casts_timestamps_correctly()
    {
        $activityLog = ActivityLog::factory()->create();

        $this->assertInstanceOf(Carbon::class, $activityLog->created_at);
        $this->assertInstanceOf(Carbon::class, $activityLog->updated_at);
    }

    /** @test */
    public function it_hides_batch_uuid_in_serialization()
    {
        $activityLog = ActivityLog::factory()->create(['batch_uuid' => 'test-uuid-123']);
        $array = $activityLog->toArray();

        $this->assertArrayNotHasKey('batch_uuid', $array);
    }

    /** @test */
    public function it_has_polymorphic_causer_relationship()
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $activityLog = ActivityLog::factory()->create([
            'causer_type' => User::class,
            'causer_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $activityLog->causer);
        $this->assertEquals($user->id, $activityLog->causer->id);
        $this->assertEquals('John Doe', $activityLog->causer->name);
    }

    /** @test */
    public function it_has_polymorphic_subject_relationship()
    {
        $file = ParameterContent::factory()->create();
        $activityLog = ActivityLog::factory()->create([
            'subject_type' => ParameterContent::class,
            'subject_id' => $file->id,
        ]);

        $this->assertInstanceOf(ParameterContent::class, $activityLog->subject);
        $this->assertEquals($file->id, $activityLog->subject->id);
    }

    /** @test */
    public function it_has_user_relationship_for_user_causers()
    {
        $user = User::factory()->create(['name' => 'Jane Smith']);
        $activityLog = ActivityLog::factory()->create([
            'causer_type' => User::class,
            'causer_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $activityLog->user);
        $this->assertEquals($user->id, $activityLog->user->id);
        $this->assertEquals('Jane Smith', $activityLog->user->name);
    }

    /** @test */
    public function it_returns_correct_badge_colors_for_actions()
    {
        $testCases = [
            'created' => 'success',
            'updated' => 'info',
            'deleted' => 'danger',
            'uploaded' => 'primary',
            'downloaded' => 'secondary',
            'login_success' => 'success',
            'login_failed' => 'danger',
            'logout' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'unknown_action' => 'secondary', // default
        ];

        foreach ($testCases as $action => $expectedColor) {
            $activityLog = ActivityLog::factory()->create(['event' => $action]);
            $this->assertEquals($expectedColor, $activityLog->getActionBadgeColor());
        }
    }

    /** @test */
    public function it_can_scope_by_log_name()
    {
        $fileLog = ActivityLog::factory()->create(['log_name' => 'file_access']);
        $userLog = ActivityLog::factory()->create(['log_name' => 'user_activity']);

        $fileLogs = ActivityLog::byLogName('file_access')->get();

        $this->assertCount(1, $fileLogs);
        $this->assertTrue($fileLogs->contains($fileLog));
        $this->assertFalse($fileLogs->contains($userLog));
    }

    /** @test */
    public function it_can_scope_by_event()
    {
        $createdLog = ActivityLog::factory()->create(['event' => 'created']);
        $updatedLog = ActivityLog::factory()->create(['event' => 'updated']);

        $createdLogs = ActivityLog::byEvent('created')->get();

        $this->assertCount(1, $createdLogs);
        $this->assertTrue($createdLogs->contains($createdLog));
        $this->assertFalse($createdLogs->contains($updatedLog));
    }

    /** @test */
    public function it_can_scope_by_subject_type()
    {
        $userLog = ActivityLog::factory()->create(['subject_type' => User::class]);
        $fileLog = ActivityLog::factory()->create(['subject_type' => ParameterContent::class]);

        $userLogs = ActivityLog::bySubjectType(User::class)->get();

        $this->assertCount(1, $userLogs);
        $this->assertTrue($userLogs->contains($userLog));
        $this->assertFalse($userLogs->contains($fileLog));
    }

    /** @test */
    public function it_can_scope_by_causer()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $log1 = ActivityLog::factory()->create(['causer_id' => $user1->id]);
        $log2 = ActivityLog::factory()->create(['causer_id' => $user2->id]);

        $user1Logs = ActivityLog::byCauser($user1->id)->get();

        $this->assertCount(1, $user1Logs);
        $this->assertTrue($user1Logs->contains($log1));
        $this->assertFalse($user1Logs->contains($log2));
    }

    /** @test */
    public function it_can_scope_by_date_range()
    {
        $oldLog = ActivityLog::factory()->create(['created_at' => Carbon::now()->subDays(10)]);
        $recentLog = ActivityLog::factory()->create(['created_at' => Carbon::now()->subDays(2)]);
        $futureLog = ActivityLog::factory()->create(['created_at' => Carbon::now()->addDays(1)]);

        $rangeLogs = ActivityLog::byDateRange(
            Carbon::now()->subDays(5)->toDateString(),
            Carbon::now()->toDateString()
        )->get();

        $this->assertCount(1, $rangeLogs);
        $this->assertTrue($rangeLogs->contains($recentLog));
        $this->assertFalse($rangeLogs->contains($oldLog));
        $this->assertFalse($rangeLogs->contains($futureLog));
    }

    /** @test */
    public function it_can_scope_recent_activities()
    {
        $oldLog = ActivityLog::factory()->create(['created_at' => Carbon::now()->subDays(40)]);
        $recentLog = ActivityLog::factory()->create(['created_at' => Carbon::now()->subDays(10)]);

        $recentLogs = ActivityLog::recent(30)->get();

        $this->assertCount(1, $recentLogs);
        $this->assertTrue($recentLogs->contains($recentLog));
        $this->assertFalse($recentLogs->contains($oldLog));
    }

    /** @test */
    public function it_formats_description_with_subject_context()
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $activityLog = ActivityLog::factory()->create([
            'description' => 'User {subject} was updated',
            'subject_type' => User::class,
            'subject_id' => $user->id,
        ]);

        $formatted = $activityLog->formatted_description;

        $this->assertEquals('User John Doe was updated', $formatted);
    }

    /** @test */
    public function it_gets_ip_address_from_properties()
    {
        $activityLog = ActivityLog::factory()->create([
            'properties' => ['ip_address' => '192.168.1.100', 'other_data' => 'test'],
        ]);

        $this->assertEquals('192.168.1.100', $activityLog->ip_address);
    }

    /** @test */
    public function it_gets_user_agent_from_properties()
    {
        $activityLog = ActivityLog::factory()->create([
            'properties' => ['user_agent' => 'Mozilla/5.0 Chrome/91.0', 'other_data' => 'test'],
        ]);

        $this->assertEquals('Mozilla/5.0 Chrome/91.0', $activityLog->user_agent);
    }

    /** @test */
    public function it_gets_changes_from_properties()
    {
        $activityLog = ActivityLog::factory()->create([
            'properties' => [
                'attributes' => ['name' => 'New Name', 'email' => 'new@example.com'],
                'other_data' => 'test',
            ],
        ]);

        $changes = $activityLog->changes;

        $this->assertEquals(['name' => 'New Name', 'email' => 'new@example.com'], $changes);
    }

    /** @test */
    public function it_gets_old_values_from_properties()
    {
        $activityLog = ActivityLog::factory()->create([
            'properties' => [
                'old' => ['name' => 'Old Name', 'email' => 'old@example.com'],
                'other_data' => 'test',
            ],
        ]);

        $oldValues = $activityLog->old_values;

        $this->assertEquals(['name' => 'Old Name', 'email' => 'old@example.com'], $oldValues);
    }

    /** @test */
    public function it_checks_if_activity_has_property_changes()
    {
        $logWithChanges = ActivityLog::factory()->create([
            'properties' => ['attributes' => ['name' => 'New Name']],
        ]);

        $logWithOldValues = ActivityLog::factory()->create([
            'properties' => ['old' => ['name' => 'Old Name']],
        ]);

        $logWithoutChanges = ActivityLog::factory()->create([
            'properties' => ['other_data' => 'test'],
        ]);

        $this->assertTrue($logWithChanges->hasPropertyChanges());
        $this->assertTrue($logWithOldValues->hasPropertyChanges());
        $this->assertFalse($logWithoutChanges->hasPropertyChanges());
    }

    /** @test */
    public function it_returns_null_for_missing_property_attributes()
    {
        $activityLog = ActivityLog::factory()->create([
            'properties' => ['other_data' => 'test'],
        ]);

        $this->assertNull($activityLog->ip_address);
        $this->assertNull($activityLog->user_agent);
        $this->assertEquals([], $activityLog->changes);
        $this->assertEquals([], $activityLog->old_values);
    }

    /** @test */
    public function it_can_generate_activity_statistics()
    {
        // Create test data
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        ActivityLog::factory()->create([
            'event' => 'created',
            'causer_id' => $user1->id,
            'created_at' => Carbon::now()->subDays(1),
        ]);
        
        ActivityLog::factory()->create([
            'event' => 'updated',
            'causer_id' => $user1->id,
            'created_at' => Carbon::now()->subDays(2),
        ]);
        
        ActivityLog::factory()->create([
            'event' => 'created',
            'causer_id' => $user2->id,
            'created_at' => Carbon::now()->subDays(3),
        ]);

        $stats = ActivityLog::getStats(30);

        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('by_event', $stats);
        $this->assertArrayHasKey('by_user', $stats);
        $this->assertArrayHasKey('daily', $stats);
        
        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(2, $stats['by_event']['created']);
        $this->assertEquals(1, $stats['by_event']['updated']);
    }
}