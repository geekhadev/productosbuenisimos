<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class LandingController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('landing/index', [
            'canRegister' => Features::enabled(Features::registration()),
        ]);
    }
}
