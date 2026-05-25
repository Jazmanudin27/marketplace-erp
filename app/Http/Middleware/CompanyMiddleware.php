<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CompanyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (Auth::check()) {

            /** @var \App\Models\User $user */
            $user = Auth::user();

            if ($user) {

                $company = $user->companies()->first();
            }
            // Set company from session or user's company
            if (!session()->has('company_id') && $user->company_id) {
                session(['company_id' => $user->company_id]);
            }

            // Share company with views
            if (session()->has('company_id')) {
                $companyId = session('company_id');
                $company = $user->company_id === $companyId
                    ? $user->company
                    : $user->companies()
                        ->where('companies.id', $companyId)
                        ->first();

                if (!$company) {
                    // Reset to user's primary company if invalid
                    session(['company_id' => $user->company_id]);
                    $company = $user->company;
                }

                view()->share('currentCompany', $company);
            }
        }

        return $next($request);
    }
}
