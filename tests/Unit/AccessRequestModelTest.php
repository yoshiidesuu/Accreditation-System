<?php

namespace Tests\Unit;

use App\Models\AccessRequest;
use App\Models\ParameterContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AccessRequestModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create users for foreign key constraints
        User::factory()->create(['id' => 1]);
    }

    /** @test */
    public function it_can_create_an_access_request_with_required_fields()
    {
        $file = ParameterContent::factory()->create();
        $requester = User::factory()->create();

        $accessRequest = AccessRequest::create([
            'file_id' => $file->id,
            'requester_id' => $requester->id,
            'reason' => 'Need access for research',
            'status' => AccessRequest::STATUS_PENDING,
        ]);

        $this->assertInstanceOf(AccessRequest::class, $accessRequest);
        $this->assertEquals($file->id, $accessRequest->file_id);
        $this->assertEquals($requester->id, $accessRequest->requester_id);
        $this->assertEquals('Need access for research', $accessRequest->reason);
        $this->assertEquals(AccessRequest::STATUS_PENDING, $accessRequest->status);
    }

    /** @test */
    public function it_casts_datetime_attributes_correctly()
    {
        $accessRequest = AccessRequest::factory()->create([
            'expires_at' => '2024-12-31 23:59:59',
            'approved_at' => '2024-01-15 10:30:00',
            'rejected_at' => null,
            'share_link_expires_at' => '2024-02-01 12:00:00',
        ]);

        $this->assertInstanceOf(Carbon::class, $accessRequest->expires_at);
        $this->assertInstanceOf(Carbon::class, $accessRequest->approved_at);
        $this->assertNull($accessRequest->rejected_at);
        $this->assertInstanceOf(Carbon::class, $accessRequest->share_link_expires_at);
    }

    /** @test */
    public function it_has_correct_status_constants()
    {
        $this->assertEquals('pending', AccessRequest::STATUS_PENDING);
        $this->assertEquals('approved', AccessRequest::STATUS_APPROVED);
        $this->assertEquals('rejected', AccessRequest::STATUS_REJECTED);
        $this->assertEquals('expired', AccessRequest::STATUS_EXPIRED);
    }

    /** @test */
    public function it_returns_correct_status_labels()
    {
        $statuses = AccessRequest::getStatuses();

        $this->assertEquals('Pending', $statuses[AccessRequest::STATUS_PENDING]);
        $this->assertEquals('Approved', $statuses[AccessRequest::STATUS_APPROVED]);
        $this->assertEquals('Rejected', $statuses[AccessRequest::STATUS_REJECTED]);
        $this->assertEquals('Expired', $statuses[AccessRequest::STATUS_EXPIRED]);
    }

    /** @test */
    public function it_belongs_to_a_file()
    {
        $file = ParameterContent::factory()->create();
        $accessRequest = AccessRequest::factory()->create(['file_id' => $file->id]);

        $this->assertInstanceOf(ParameterContent::class, $accessRequest->file);
        $this->assertEquals($file->id, $accessRequest->file->id);
    }

    /** @test */
    public function it_belongs_to_a_requester()
    {
        $requester = User::factory()->create(['name' => 'John Doe']);
        $accessRequest = AccessRequest::factory()->create(['requester_id' => $requester->id]);

        $this->assertInstanceOf(User::class, $accessRequest->requester);
        $this->assertEquals($requester->id, $accessRequest->requester->id);
        $this->assertEquals('John Doe', $accessRequest->requester->name);
    }

    /** @test */
    public function it_belongs_to_an_approver()
    {
        $approver = User::factory()->create(['name' => 'Jane Smith']);
        $accessRequest = AccessRequest::factory()->create(['approver_id' => $approver->id]);

        $this->assertInstanceOf(User::class, $accessRequest->approver);
        $this->assertEquals($approver->id, $accessRequest->approver->id);
        $this->assertEquals('Jane Smith', $accessRequest->approver->name);
    }

    /** @test */
    public function it_checks_if_request_is_pending()
    {
        $pendingRequest = AccessRequest::factory()->create([
            'status' => AccessRequest::STATUS_PENDING,
            'expires_at' => now()->addDays(1),
        ]);

        $expiredRequest = AccessRequest::factory()->create([
            'status' => AccessRequest::STATUS_PENDING,
            'expires_at' => now()->subDays(1),
        ]);

        $approvedRequest = AccessRequest::factory()->create([
            'status' => AccessRequest::STATUS_APPROVED,
        ]);

        $this->assertTrue($pendingRequest->isPending());
        $this->assertFalse($expiredRequest->isPending());
        $this->assertFalse($approvedRequest->isPending());
    }

    /** @test */
    public function it_checks_if_request_is_approved()
    {
        $approvedRequest = AccessRequest::factory()->create(['status' => AccessRequest::STATUS_APPROVED]);
        $pendingRequest = AccessRequest::factory()->create(['status' => AccessRequest::STATUS_PENDING]);

        $this->assertTrue($approvedRequest->isApproved());
        $this->assertFalse($pendingRequest->isApproved());
    }

    /** @test */
    public function it_checks_if_request_is_rejected()
    {
        $rejectedRequest = AccessRequest::factory()->create(['status' => AccessRequest::STATUS_REJECTED]);
        $pendingRequest = AccessRequest::factory()->create(['status' => AccessRequest::STATUS_PENDING]);

        $this->assertTrue($rejectedRequest->isRejected());
        $this->assertFalse($pendingRequest->isRejected());
    }

    /** @test */
    public function it_checks_if_request_is_expired()
    {
        $expiredRequest = AccessRequest::factory()->create(['expires_at' => now()->subDays(1)]);
        $validRequest = AccessRequest::factory()->create(['expires_at' => now()->addDays(1)]);
        $noExpiryRequest = AccessRequest::factory()->create(['expires_at' => null]);

        $this->assertTrue($expiredRequest->isExpired());
        $this->assertFalse($validRequest->isExpired());
        $this->assertFalse($noExpiryRequest->isExpired());
    }

    /** @test */
    public function it_checks_if_share_link_is_valid()
    {
        $validShareLink = AccessRequest::factory()->create([
            'status' => AccessRequest::STATUS_APPROVED,
            'share_link' => 'valid-link-123',
            'share_link_expires_at' => now()->addHours(1),
        ]);

        $expiredShareLink = AccessRequest::factory()->create([
            'status' => AccessRequest::STATUS_APPROVED,
            'share_link' => 'expired-link-456',
            'share_link_expires_at' => now()->subHours(1),
        ]);

        $noShareLink = AccessRequest::factory()->create([
            'status' => AccessRequest::STATUS_APPROVED,
            'share_link' => null,
        ]);

        $this->assertTrue($validShareLink->isShareLinkValid());
        $this->assertFalse($expiredShareLink->isShareLinkValid());
        $this->assertFalse($noShareLink->isShareLinkValid());
    }

    /** @test */
    public function it_can_approve_a_request()
    {
        $approver = User::factory()->create();
        $accessRequest = AccessRequest::factory()->create(['status' => AccessRequest::STATUS_PENDING]);

        $accessRequest->approve($approver->id);

        $this->assertEquals(AccessRequest::STATUS_APPROVED, $accessRequest->status);
        $this->assertEquals($approver->id, $accessRequest->approver_id);
        $this->assertNotNull($accessRequest->approved_at);
    }

    /** @test */
    public function it_can_approve_with_share_link_generation()
    {
        $approver = User::factory()->create();
        $accessRequest = AccessRequest::factory()->create(['status' => AccessRequest::STATUS_PENDING]);

        $accessRequest->approve($approver->id, true, 48);

        $this->assertEquals(AccessRequest::STATUS_APPROVED, $accessRequest->status);
        $this->assertNotNull($accessRequest->share_link);
        $this->assertNotNull($accessRequest->share_link_expires_at);
    }

    /** @test */
    public function it_can_reject_a_request()
    {
        $approver = User::factory()->create();
        $accessRequest = AccessRequest::factory()->create(['status' => AccessRequest::STATUS_PENDING]);

        $accessRequest->reject($approver->id, 'Insufficient justification');

        $this->assertEquals(AccessRequest::STATUS_REJECTED, $accessRequest->status);
        $this->assertEquals($approver->id, $accessRequest->approver_id);
        $this->assertEquals('Insufficient justification', $accessRequest->rejection_reason);
        $this->assertNotNull($accessRequest->rejected_at);
    }

    /** @test */
    public function it_can_generate_share_link()
    {
        $accessRequest = AccessRequest::factory()->create();

        $accessRequest->generateShareLink(12);

        $this->assertNotNull($accessRequest->share_link);
        $this->assertEquals(64, strlen($accessRequest->share_link));
        $this->assertNotNull($accessRequest->share_link_expires_at);
    }

    /** @test */
    public function it_can_revoke_share_link()
    {
        $accessRequest = AccessRequest::factory()->create([
            'share_link' => 'existing-link',
            'share_link_expires_at' => now()->addHours(1),
        ]);

        $accessRequest->revokeShareLink();

        $this->assertNull($accessRequest->share_link);
        $this->assertNull($accessRequest->share_link_expires_at);
    }

    /** @test */
    public function it_can_scope_by_status()
    {
        $pendingRequest = AccessRequest::factory()->create(['status' => AccessRequest::STATUS_PENDING]);
        $approvedRequest = AccessRequest::factory()->create(['status' => AccessRequest::STATUS_APPROVED]);

        $pendingRequests = AccessRequest::byStatus(AccessRequest::STATUS_PENDING)->get();

        $this->assertCount(1, $pendingRequests);
        $this->assertTrue($pendingRequests->contains($pendingRequest));
        $this->assertFalse($pendingRequests->contains($approvedRequest));
    }

    /** @test */
    public function it_can_scope_by_requester()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $request1 = AccessRequest::factory()->create(['requester_id' => $user1->id]);
        $request2 = AccessRequest::factory()->create(['requester_id' => $user2->id]);

        $user1Requests = AccessRequest::byRequester($user1->id)->get();

        $this->assertCount(1, $user1Requests);
        $this->assertTrue($user1Requests->contains($request1));
        $this->assertFalse($user1Requests->contains($request2));
    }

    /** @test */
    public function it_can_scope_by_file()
    {
        $file1 = ParameterContent::factory()->create();
        $file2 = ParameterContent::factory()->create();
        
        $request1 = AccessRequest::factory()->create(['file_id' => $file1->id]);
        $request2 = AccessRequest::factory()->create(['file_id' => $file2->id]);

        $file1Requests = AccessRequest::byFile($file1->id)->get();

        $this->assertCount(1, $file1Requests);
        $this->assertTrue($file1Requests->contains($request1));
        $this->assertFalse($file1Requests->contains($request2));
    }

    /** @test */
    public function it_can_scope_pending_requests()
    {
        $pendingValid = AccessRequest::factory()->create([
            'status' => AccessRequest::STATUS_PENDING,
            'expires_at' => now()->addDays(1),
        ]);

        $pendingExpired = AccessRequest::factory()->create([
            'status' => AccessRequest::STATUS_PENDING,
            'expires_at' => now()->subDays(1),
        ]);

        $approved = AccessRequest::factory()->create(['status' => AccessRequest::STATUS_APPROVED]);

        $pendingRequests = AccessRequest::pending()->get();

        $this->assertCount(1, $pendingRequests);
        $this->assertTrue($pendingRequests->contains($pendingValid));
        $this->assertFalse($pendingRequests->contains($pendingExpired));
        $this->assertFalse($pendingRequests->contains($approved));
    }

    /** @test */
    public function it_can_scope_expired_requests()
    {
        $expiredRequest = AccessRequest::factory()->create([
            'status' => AccessRequest::STATUS_PENDING,
            'expires_at' => now()->subDays(1),
        ]);

        $validRequest = AccessRequest::factory()->create([
            'status' => AccessRequest::STATUS_PENDING,
            'expires_at' => now()->addDays(1),
        ]);

        $expiredRequests = AccessRequest::expired()->get();

        $this->assertCount(1, $expiredRequests);
        $this->assertTrue($expiredRequests->contains($expiredRequest));
        $this->assertFalse($expiredRequests->contains($validRequest));
    }

    /** @test */
    public function it_sets_default_expiry_on_creation()
    {
        $accessRequest = AccessRequest::factory()->create(['expires_at' => null]);

        $this->assertNotNull($accessRequest->expires_at);
        $this->assertTrue($accessRequest->expires_at->isFuture());
    }

    /** @test */
    public function it_gets_file_owner_attribute()
    {
        $owner = User::factory()->create(['name' => 'File Owner']);
        $file = ParameterContent::factory()->create(['user_id' => $owner->id]);
        $accessRequest = AccessRequest::factory()->create(['file_id' => $file->id]);

        $fileOwner = $accessRequest->file_owner;

        $this->assertInstanceOf(User::class, $fileOwner);
        $this->assertEquals($owner->id, $fileOwner->id);
        $this->assertEquals('File Owner', $fileOwner->name);
    }

    /** @test */
    public function it_checks_if_user_can_approve_request_as_file_owner()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $file = ParameterContent::factory()->create(['user_id' => $owner->id]);
        $accessRequest = AccessRequest::factory()->create(['file_id' => $file->id]);

        $this->assertTrue($accessRequest->canBeApprovedBy($owner->id));
        $this->assertFalse($accessRequest->canBeApprovedBy($otherUser->id));
    }
}