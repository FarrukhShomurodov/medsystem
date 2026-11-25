<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\DocumentResponse;
use App\Models\HouseholdMaterial;
use App\Models\MainWarehouseHouseholdMaterial;
use App\Models\MainWarehouseMedicalSupply;
use App\Models\MainWarehouseMedication;
use App\Models\MedicalSupply;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\PatientMedication;
use App\Models\PharmacyMedication;
use App\Models\Reception;
use App\Models\SupplyRequest;
use App\Models\Transfer;
use App\Models\WarehouseAccounting;
use App\Models\WarehouseHouseholdMaterial;
use App\Models\WarehouseMedicalSupply;
use App\Policies\AdminPolicy;
use App\Policies\DocumentResponsePolicy;
use App\Policies\ReceptionPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Telescope is a dev dependency: never boot it outside local, and stay
        // bootable when it was not installed (composer install --no-dev).
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Admin::class, AdminPolicy::class);
        Gate::policy(DocumentResponse::class, DocumentResponsePolicy::class);
        Gate::policy(Reception::class, ReceptionPolicy::class);

        // The dashboard is Tabler (Bootstrap 5): ->links() renders resources/views/vendor/pagination/bootstrap-5.
        Paginator::useBootstrapFive();

        // The *_type columns of polymorphic relations store these aliases, not class names, so a
        // renamed class never orphans rows. A model missing here cannot be stored in such a column.
        Relation::enforceMorphMap([
            'admin' => Admin::class,
            'patient' => Patient::class,
            'medication' => Medication::class,
            'medical_supply' => MedicalSupply::class,
            'household_material' => HouseholdMaterial::class,
            'main_warehouse_medication' => MainWarehouseMedication::class,
            'main_warehouse_medical_supply' => MainWarehouseMedicalSupply::class,
            'main_warehouse_household_material' => MainWarehouseHouseholdMaterial::class,
            'warehouse_accounting' => WarehouseAccounting::class,
            'warehouse_medical_supply' => WarehouseMedicalSupply::class,
            'warehouse_household_material' => WarehouseHouseholdMaterial::class,
            'pharmacy_medication' => PharmacyMedication::class,
            'supply_request' => SupplyRequest::class,
            'transfer' => Transfer::class,
            'patient_medication' => PatientMedication::class,
        ]);

        // Dashboard sign-in: 5 attempts a minute for one login from one address. The limiter runs
        // before LoginRequest validates, so an e-mail that is not a string (email[]=...) is read as
        // "" and counted under the address alone, instead of failing with a 500.
        RateLimiter::for('login', function (Request $request): Limit {
            $key = Str::transliterate(Str::lower($request->string('email', '', strict: true)->toString())).'|'.$request->ip();

            return Limit::perMinute(5)->by($key)->response(
                fn (Request $request, array $headers): RedirectResponse => back()
                    ->withInput($request->only('email', 'remember'))
                    ->withErrors(['login' => __('auth.throttle', ['seconds' => $headers['Retry-After'] ?? 60])])
                    ->withHeaders($headers),
            );
        });
    }
}
