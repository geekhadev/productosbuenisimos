<?php

namespace App\Http\Controllers\Shared;

use App\Actions\Shared\Countries\CreateCountryAction;
use App\Actions\Shared\Countries\DeleteCountryAction;
use App\Actions\Shared\Countries\ListCountriesAction;
use App\Actions\Shared\Countries\UpdateCountryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shared\CountriesRequest;
use App\Http\Requests\Shared\CountryListRequest;
use App\Models\Shared\Country;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CountriesController extends Controller
{
    public function index(CountryListRequest $request, ListCountriesAction $action): Response
    {
        $this->authorize('viewAny', Country::class);

        $countries = $action->execute($request->filtersForAction());

        return Inertia::render('shared/countries/index', [
            'data' => $countries,
            'filters' => $request->filtersForFrontend(),
        ]);
    }

    public function create(): RedirectResponse
    {
        $this->authorize('create', Country::class);

        return to_route('shared.countries.index');
    }

    public function store(CountriesRequest $request, CreateCountryAction $action): RedirectResponse
    {
        $this->authorize('create', Country::class);

        $action->execute($request->countryPayload());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Country created.')]);

        return to_route('shared.countries.index');
    }

    public function edit(Country $country): RedirectResponse
    {
        $this->authorize('update', $country);

        return to_route('shared.countries.index');
    }

    public function update(CountriesRequest $request, Country $country, UpdateCountryAction $action): RedirectResponse
    {
        $this->authorize('update', $country);

        $action->execute($country, $request->countryPayload());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Country updated.')]);

        return to_route('shared.countries.index');
    }

    public function destroy(Country $country, DeleteCountryAction $action): RedirectResponse
    {
        $this->authorize('delete', $country);

        $action->execute($country);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Country deleted.')]);

        return to_route('shared.countries.index');
    }
}
