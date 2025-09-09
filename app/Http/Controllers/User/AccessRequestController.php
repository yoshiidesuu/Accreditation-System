<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AccessRequest;
use App\Models\ParameterContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AccessRequestCreated;
use App\Notifications\AccessRequestApproved;
use App\Notifications\AccessRequestRejected;

class AccessRequestController extends Controller
{
    /**
     * Display a listing of access requests.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = AccessRequest::with(['requester', 'approver', 'file.user'])
            ->latest();

        // Filter based on user role
        if ($user->hasRole('admin')) {
            // Admin can see all requests
        } elseif ($user->hasAnyRole(['dean', 'overall_coordinator'])) {
            // Deans and coordinators can see requests for their college files
            $query->whereHas('file', function ($q) use ($user) {
                $q->where('college_id', $user->college_id);
            });
        } else {
            // Others can only see their own requests or requests for files they own
            $query->where(function ($q) use ($user) {
                $q->where('requester_id', $user->id)
                  ->orWhereHas('file', function ($subQ) use ($user) {
                      $subQ->where('user_id', $user->id);
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhereHas('requester', function ($subQ) use ($search) {
                      $subQ->where('name', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $accessRequests = $query->paginate(15);

        return view('user.access-requests.index', compact('accessRequests'));
    }

    /**
     * Store a newly created access request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file_id' => 'required|exists:parameter_contents,id',
            'reason' => 'required|string|max:1000',
        ]);

        $parameterContent = ParameterContent::findOrFail($request->file_id);
        
        // Check if user can request access
        if (!Auth::user()->can('parameter_contents.request_access')) {
            abort(403, 'You do not have permission to request access.');
        }

        // Check if user already has a pending request for this file
        $existingRequest = AccessRequest::where('file_id', $request->file_id)
            ->where('requester_id', Auth::id())
            ->where('status', AccessRequest::STATUS_PENDING)
            ->first();

        if ($existingRequest) {
            return back()->with('error', 'You already have a pending access request for this file.');
        }

        // Create the access request
        $accessRequest = AccessRequest::create([
            'file_id' => $request->file_id,
            'requester_id' => Auth::id(),
            'reason' => $request->reason,
            'status' => AccessRequest::STATUS_PENDING,
        ]);

        // Notify file owner and admins
        $this->notifyRelevantUsers($accessRequest);

        return back()->with('success', 'Access request submitted successfully. You will be notified when it is reviewed.');
    }

    /**
     * Approve an access request.
     */
    public function approve(AccessRequest $accessRequest, Request $request)
    {
        // Check if user can approve this request
        if (!$accessRequest->canBeApprovedBy(Auth::id())) {
            abort(403, 'You do not have permission to approve this request.');
        }

        if (!$accessRequest->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        $shareLinkDuration = $request->get('share_link_duration', 7);
        $generateShareLink = $request->boolean('generate_share_link', true);
        $accessRequest->approve(Auth::id(), $generateShareLink, $shareLinkDuration);

        // Notify requester
        $accessRequest->requester->notify(new AccessRequestApproved($accessRequest));

        return back()->with('success', 'Access request approved successfully.');
    }

    /**
     * Reject an access request.
     */
    public function reject(AccessRequest $accessRequest, Request $request)
    {
        $request->validate([
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        // Check if user can approve this request (same permission for reject)
        if (!$accessRequest->canBeApprovedBy(Auth::id())) {
            abort(403, 'You do not have permission to reject this request.');
        }

        if (!$accessRequest->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        $accessRequest->reject(Auth::id(), $request->rejection_reason);

        // Notify requester
        $accessRequest->requester->notify(new AccessRequestRejected($accessRequest));

        return back()->with('success', 'Access request rejected.');
    }

    /**
     * Show the specified access request.
     */
    public function show(AccessRequest $accessRequest)
    {
        // Check if user can view this request
        $user = Auth::user();
        if (!$user->hasRole('admin') && 
            $accessRequest->requester_id !== $user->id && 
            !$accessRequest->canBeApprovedBy($user->id)) {
            abort(403, 'You do not have permission to view this request.');
        }

        return view('user.access-requests.show', compact('accessRequest'));
    }

    /**
     * Access file via share link.
     */
    public function accessViaShareLink($shareLink)
    {
        $accessRequest = AccessRequest::where('share_link', $shareLink)
            ->where('status', AccessRequest::STATUS_APPROVED)
            ->where('share_link_expires_at', '>', now())
            ->firstOrFail();

        $accessRequest->load(['file.parameter', 'file.user', 'file.college', 'file.academicYear']);

        // Log the access
        activity()
            ->performedOn($accessRequest->file)
            ->causedBy(Auth::id())
            ->withProperties([
                'access_request_id' => $accessRequest->id,
                'share_link' => $shareLink,
                'accessed_at' => now(),
            ])
            ->log('File accessed via share link');

        return view('user.parameter-contents.show', [
            'parameterContent' => $accessRequest->file,
            'viaShareLink' => true,
            'accessRequest' => $accessRequest
        ]);
    }

    /**
     * Notify relevant users about the access request.
     */
    private function notifyRelevantUsers(AccessRequest $accessRequest)
    {
        // Notify file owner
        if ($accessRequest->file && $accessRequest->file->user) {
            $accessRequest->file->user->notify(new AccessRequestCreated($accessRequest));
        }

        // Notify admins
        $admins = \App\Models\User::role('admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new AccessRequestCreated($accessRequest));
        }

        // Notify college coordinators if applicable
        if ($accessRequest->file && $accessRequest->file->college_id) {
            $coordinators = \App\Models\User::role('overall_coordinator')
                ->where('college_id', $accessRequest->file->college_id)
                ->get();
            
            foreach ($coordinators as $coordinator) {
                $coordinator->notify(new AccessRequestCreated($accessRequest));
            }
        }
    }
}