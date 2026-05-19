<?php

namespace App\Http\Controllers;

use App\Actions\Landing\ShowPublicProductAction;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class LandingPublicProductController extends Controller
{
    public function show(string $product, ShowPublicProductAction $action): Response
    {
        $payload = $action->execute($product);

        if ($payload === null) {
            abort(404);
        }

        return Inertia::render('landing/product-show', [
            'canRegister' => Features::enabled(Features::registration()),
            'product' => $payload,
        ]);
    }
}
