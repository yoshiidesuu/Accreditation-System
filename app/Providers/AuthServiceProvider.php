<?php

namespace App\Providers;

use App\Models\SwotEntry;
use App\Policies\SwotPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        SwotEntry::class => SwotPolicy::class,
        \App\Models\College::class => \App\Policies\CollegePolicy::class,
        \App\Models\Parameter::class => \App\Policies\ParameterPolicy::class,
        \App\Models\ParameterContent::class => \App\Policies\ParameterContentPolicy::class,
        \App\Models\AccessRequest::class => \App\Policies\AccessRequestPolicy::class,
        \App\Models\Area::class => \App\Policies\AreaPolicy::class,
        \App\Models\Accreditation::class => \App\Policies\AccreditationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}