<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\SelectedCompanySession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanySelected
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        SelectedCompanySession::synchronize($request);

        $user = $request->user();

        if (! $user instanceof User) {
            return $next($request);
        }

        $list = SelectedCompanySession::selectableCompaniesOrResolve($request);

        if ($list->isEmpty()) {
            return $next($request);
        }

        if ($list->count() === 1) {
            return $next($request);
        }

        if ($request->routeIs('company-selection.*')) {
            return $next($request);
        }

        $currentId = data_get($request->session()->get('company_selected'), 'id');

        if (is_string($currentId) && $currentId !== '' && $list->contains('id', $currentId)) {
            return $next($request);
        }

        $request->session()->forget('company_selected');

        return redirect()->route('company-selection.index');
    }
}
