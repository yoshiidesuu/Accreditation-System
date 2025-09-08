<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\College;
use App\Models\Area;
use App\Models\Parameter;
use App\Models\ParameterContent;
use App\Models\Accreditation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard
     */
    public function index()
    {
        // Get dashboard statistics
        $stats = [
            'total_users' => User::count(),
            'total_colleges' => College::count(),
            'total_areas' => Area::count(),
            'total_parameters' => Parameter::count(),
            'pending_contents' => ParameterContent::where('status', 'pending')->count(),
            'active_accreditations' => Accreditation::where('status', 'active')->count(),
        ];
        
        // Get recent activities
        $recentUsers = User::latest()->take(5)->get();
        $recentContents = ParameterContent::with(['parameter', 'user'])
            ->latest()
            ->take(5)
            ->get();
        
        // Get user role distribution
        $userRoles = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();
        
        // Get monthly user registrations
        $monthlyUsers = User::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as count')
        )
        ->whereYear('created_at', date('Y'))
        ->groupBy('month')
        ->orderBy('month')
        ->pluck('count', 'month')
        ->toArray();
        
        return view('admin.dashboard', compact(
            'stats',
            'recentUsers',
            'recentContents',
            'userRoles',
            'monthlyUsers'
        ));
    }
}