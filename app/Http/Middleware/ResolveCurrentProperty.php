<?php

namespace App\Http\Middleware;

use App\Models\Property;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveCurrentProperty
{
    public function handle(Request $request, Closure $next): Response
    {
        $property = $this->resolve($request);

        if ($property) {
            app()->instance('current_property', $property);
            session(['current_property_id' => $property->id]);
        }

        return $next($request);
    }

    protected function resolve(Request $request): ?Property
    {
        $user = $request->user();

        if ($id = $request->header('X-Property-Id')) {
            $prop = Property::find((int) $id);
            if ($prop && $user && ($user->property_id === $prop->id || $user->property_id === null)) {
                return $prop;
            }
        }

        if ($id = session('current_property_id')) {
            $prop = Property::find($id);
            if ($prop) return $prop;
        }

        if ($user && $user->property_id) {
            return Property::find($user->property_id);
        }

        return Property::orderBy('id')->first();
    }
}
