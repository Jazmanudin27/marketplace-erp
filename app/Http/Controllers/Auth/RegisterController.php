<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255|unique:companies,name',
            'company_email' => 'required|email|unique:companies,email',
            'company_phone' => 'nullable|string|max:20',
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            DB::beginTransaction();

            // Create company
            $company = Company::create([
                'name' => $validated['company_name'],
                'slug' => Str::slug($validated['company_name']),
                'email' => $validated['company_email'],
                'phone' => $validated['company_phone'] ?? null,
                'status' => 'active',
            ]);

            // Create user
            $user = User::create([
                'name' => $validated['user_name'],
                'email' => $validated['user_email'],
                'password' => $validated['password'],
                'company_id' => $company->id,
                'status' => 'active',
            ]);

            // Get admin role or create if not exists
            $adminRole = Role::where('name', 'admin')->first();
            if (!$adminRole) {
                $adminRole = Role::create([
                    'name' => 'admin',
                    'display_name' => 'Administrator',
                    'description' => 'Full access to all features',
                ]);
            }

            // Attach user to company with admin role
            $user->companies()->attach($company->id, [
                'role_id' => $adminRole->id,
                'status' => 'active',
            ]);

            DB::commit();

            auth()->login($user);
            session(['company_id' => $company->id]);

            return redirect()->route('dashboard')->with('success', 'Pendaftaran berhasil! Selamat datang ke ' . $company->name);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
}
