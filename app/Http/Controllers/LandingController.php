<?php

namespace App\Http\Controllers;

use App\Actions\Landing\GetPublicProductsAction;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class LandingController extends Controller
{
    public function __invoke(GetPublicProductsAction $action): Response
    {
        return Inertia::render('landing/index', array_merge(
            ['canRegister' => Features::enabled(Features::registration())],
            $action->execute(),
        ));
    }
}
