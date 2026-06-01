<?php

namespace App\Providers;

use App\Http\Responses\LoginResponse;
use App\Http\Responses\RegisterResponse;
use App\Models\Administration\Module;
use App\Models\Administration\Permission;
use App\Models\Administration\System;
use App\Models\Company;
use App\Models\Configuration\AiProviderCredential;
use App\Models\Configuration\FulfillmentProviderCredential;
use App\Models\Configuration\Role;
use App\Models\Public\ChatbotConversation;
use App\Models\Sales\Customer;
use App\Models\Sales\Lead;
use App\Models\Sales\Order;
use App\Models\Sales\SalesAgentConfig;
use App\Models\Shared\Country;
use App\Models\Shared\State;
use App\Models\Stock\Product;
use App\Policies\Administration\ModulesPolicy;
use App\Policies\Administration\PermissionsPolicy;
use App\Policies\Administration\SystemPolicy;
use App\Policies\Configuration\AiProvidersPolicy;
use App\Policies\Configuration\CompaniesPolicy;
use App\Policies\Configuration\FulfillmentProvidersPolicy;
use App\Policies\Configuration\RolesPolicy;
use App\Policies\Sales\AgentConfigPolicy;
use App\Policies\Sales\ConversationsPolicy;
use App\Policies\Sales\CustomerPolicy;
use App\Policies\Sales\LeadPolicy;
use App\Policies\Sales\OrderPolicy;
use App\Policies\Shared\CountriesPolicy;
use App\Policies\Shared\StatesPolicy;
use App\Policies\Stock\ProductPolicy;
use App\Support\AiConfigurationBridge;
use App\Support\FulfillmentConfigurationBridge;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
        $this->app->singleton(RegisterResponseContract::class, RegisterResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        Event::listen(Logout::class, function (Logout $event): void {
            if ($event->user !== null) {
                request()->session()->forget('company_selected');
            }
        });

        $this->app->booted(function (): void {
            AiConfigurationBridge::apply();
            FulfillmentConfigurationBridge::apply();
        });

        Gate::policy(AiProviderCredential::class, AiProvidersPolicy::class);
        Gate::policy(FulfillmentProviderCredential::class, FulfillmentProvidersPolicy::class);
        Gate::policy(Company::class, CompaniesPolicy::class);
        Gate::policy(Module::class, ModulesPolicy::class);
        Gate::policy(Permission::class, PermissionsPolicy::class);
        Gate::policy(Role::class, RolesPolicy::class);
        Gate::policy(System::class, SystemPolicy::class);
        Gate::policy(Country::class, CountriesPolicy::class);
        Gate::policy(State::class, StatesPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(ChatbotConversation::class, ConversationsPolicy::class);
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(Lead::class, LeadPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(SalesAgentConfig::class, AgentConfigPolicy::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
