<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ucfirst(AppSettings::get('app_name', 'App'))}} - {{ucfirst($title ?? '')}}</title>
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{!empty(AppSettings::get('favicon')) ? asset('storage/'.AppSettings::get('favicon')) : asset('assets/img/favicon.png')}}">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}">

    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="{{asset('assets/plugins/fontawesome/css/fontawesome.min.css')}}">

    <!-- Main CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    <!-- Page CSS -->
    @stack('page-css')
    <!--[if lt IE 9]>
        <script src="assets/js/html5shiv.min.js"></script>
        <script src="assets/js/respond.min.js')}}"></script>
    <![endif]-->
</head>
<body>

    <!-- Main Wrapper -->
    
    <div class="main-wrapper login-body">
        <div class="login-wrapper" style="display: flex; height: 100vh;">
            <!-- Left Section with Background Image -->
            <div style="flex: 1; background: url('{{ asset('assets/img/admin-login-img.svg') }}') no-repeat center center; background-size: cover;">
            </div>
            <!-- Right Section with Form -->
            <div style="flex: 1; display: flex; justify-content: center; align-items: center; background-color: #fff; {{ request()->is('admin/auth/login') ? 'padding-right: 0; padding-left: 0; margin-right: 0; margin-left: 0;' : '' }}">
                <div style="width: 100%; max-width: 500px; padding: 2rem; background: none; text-align: center; height: 100vh; display: flex; align-items: center;">
                    <div class="loginbox">

                        <div class="login-right">
                            <div class="login-right-wrap">
                                @if ($errors->any())
                                    @foreach ($errors->all() as $error)
                                        <x-alerts.danger :error="$error" />
                                    @endforeach
                                @endif
                                @yield('content')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- Visit codeastro.com for more projects -->
    <!-- /Main Wrapper -->
    
</body>
<!-- jQuery -->
<script src="{{asset('assets/js/jquery-3.2.1.min.js')}}"></script>

<!-- Bootstrap Core JS -->
<script src="{{asset('assets/js/popper.min.js')}}"></script>
<script src="{{asset('assets/js/bootstrap.min.js')}}"></script>

<!-- Custom JS -->
<script src="{{asset('assets/js/script.js')}}"></script>
<!-- Page JS -->
@stack('page-js')
</html>