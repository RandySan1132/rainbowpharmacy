@extends('admin.layouts.plain')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; height: 100vh; background-color: none; padding: 2rem; overflow: hidden;">
    <!-- Right Section with Form -->
    <div style="flex: 1; max-width: 500px; background: none; text-align: center; margin: auto;">
        <img src="{{ asset('assets/img/pharmafrnt.jpg') }}" alt="Pharmacy Logo" style="max-width: 150px; margin-bottom: 1.5rem;">

        <h1 style="font-size: 1.8rem; font-weight: bold; color: #004085;">Welcome Pharmacist<h1>

        @if (session('login_error'))
            <x-alerts.danger :error="session('login_error')" />
        @endif

        <!-- Form -->
        <form action="{{route('login')}}" method="post" style="margin-top: 1.5rem; text-align: left;">
            @csrf
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="email" style="font-size: 0.9rem; color: #6c757d;">Email Address</label>
                <input 
                    id="email"
                    class="form-control" 
                    name="email" 
                    type="text" 
                    placeholder="admin@mail.com" 
                    style="padding: 0.75rem; border: 1px solid #ced4da; border-radius: 50px;">
            </div>
            <div class="form-group" style="margin-bottom: 2rem;">
                <label for="password" style="font-size: 0.9rem; color: #6c757d;">Password</label>
                <input 
                    id="password"
                    class="form-control" 
                    name="password" 
                    type="password" 
                    placeholder="********" 
                    style="padding: 0.75rem; border: 1px solid #ced4da; border-radius: 50px;">
            </div>
            <button 
                class="btn btn-primary btn-block" 
                type="submit" 
                style="background-color: #004085; border-color: #004085; padding: 0.75rem; font-size: 1rem; font-weight: bold; border-radius: 50px;">
                Login
            </button>
        </form>
        <!-- /Form -->

        <div class="text-center forgotpass" style="margin-top: 1rem;">
            <a href="{{route('password.request')}}" style="color: #004085; text-decoration: none; font-size: 0.9rem;">Forgot Password?</a>
        </div>
        <div class="text-center dont-have" style="font-size: 0.9rem; margin-top: 1rem;">
            Don’t have an account? 
            <a href="{{route('register')}}" style="color: #004085; text-decoration: none; font-weight: bold;">Register</a>
        </div>
    </div>
</div>
@endsection
