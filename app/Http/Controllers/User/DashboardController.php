<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\Area;
use App\Models\Parameter;
use App\Models\ParameterContent;
use App\Models\Accreditation;
use App\Models\SwotEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the user dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $userRole = $user->role ?? 'user';
        
        // Base statistics available to all users
        $stats = [
            'my_contents' => ParameterContent::where('user_id', $user->id)->count(),
            'pending_contents' => ParameterContent::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count(),
            'approved_contents' => ParameterContent::where('user_id', $user->id)
                ->where('status', 'approved')
                ->count(),
        ];
        
        // Role-specific statistics
        switch ($userRole) {
            case 'coordinator':
                $stats['my_colleges'] = College::where('coordinator_id', $user->id)->count();
                $stats['my_areas'] = Area::whereHas('college', function($query) use ($user) {
                    $query->where('coordinator_id', $user->id);
                })->count();
                break;
                
            case 'faculty':
                $stats['my_colleges'] = College::whereHas('users', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->count();
                break;
                
            case 'accreditor_lead':
            case 'accreditor_member':
                $stats['my_accreditations'] = Accreditation::whereHas('team', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->count();
                $stats['pending_evaluations'] = Accreditation::whereHas('team', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->where('status', 'in_progress')->count();
                break;
        }
        
        // Get user's recent activities
        $recentContents = ParameterContent::with(['parameter'])
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();
        
        // Get user's colleges (if applicable)
        $userColleges = collect();
        if (in_array($userRole, ['coordinator', 'faculty'])) {
            if ($userRole === 'coordinator') {
                $userColleges = College::where('coordinator_id', $user->id)->get();
            } else {
                $userColleges = College::whereHas('users', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->get();
            }
        }
        
        // Get user's accreditations (if applicable)
        $userAccreditations = collect();
        if (in_array($userRole, ['accreditor_lead', 'accreditor_member'])) {
            $userAccreditations = Accreditation::whereHas('team', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with('college')->get();
        }
        
        // Get monthly content submissions
        $monthlyContents = ParameterContent::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as count')
        )
        ->where('user_id', $user->id)
        ->whereYear('created_at', date('Y'))
        ->groupBy('month')
        ->orderBy('month')
        ->pluck('count', 'month')
        ->toArray();
        
        return view('user.dashboard', compact(
            'stats',
            'recentContents',
            'userColleges',
            'userAccreditations',
            'monthlyContents',
            'userRole'
        ));
    }
}