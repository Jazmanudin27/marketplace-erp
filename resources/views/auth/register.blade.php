@extends('layouts.app')

@section('title', 'Daftar - Marketplace ERP')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-6">
            <div class="card page-card">
                <div class="card-body p-4 p-lg-5">
                    <div class="text-center mb-4">
                        <h1 class="h3 fw-bold mb-2">Marketplace ERP</h1>
                        <p class="text-muted mb-0">Daftar sekarang dan mulai mengelola toko Anda.</p>
                    </div>

                    <form action="{{ route('register') }}" method="POST" class="d-grid gap-3">
                        @csrf

                        <div>
                            <label class="form-label">Nama Perusahaan</label>
                            <input type="text" name="company_name" value="{{ old('company_name') }}" class="form-control" required>
                        </div>

                        <div>
                            <label class="form-label">Email Perusahaan</label>
                            <input type="email" name="company_email" value="{{ old('company_email') }}" class="form-control" required>
                        </div>

                        <div>
                            <label class="form-label">Nomor Telepon (Opsional)</label>
                            <input type="tel" name="company_phone" value="{{ old('company_phone') }}" class="form-control">
                        </div>

                        <hr class="my-2">

                        <div>
                            <label class="form-label">Nama Anda</label>
                            <input type="text" name="user_name" value="{{ old('user_name') }}" class="form-control" required>
                        </div>

                        <div>
                            <label class="form-label">Email Anda</label>
                            <input type="email" name="user_email" value="{{ old('user_email') }}" class="form-control" required>
                        </div>

                        <div>
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div>
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg">Daftar</button>
                    </form>

                    <div class="mt-4 text-center">
                        <span class="text-muted">Sudah punya akun?</span>
                        <a href="{{ route('login') }}" class="fw-semibold text-decoration-none">Login di sini</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
