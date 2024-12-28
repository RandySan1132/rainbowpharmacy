@extends('admin.layouts.app')
@php
    $title ='settings';
@endphp

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">General Settings</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item"><a href="javascript:(0)">Settings</a></li>
		<li class="breadcrumb-item active">General Settings</li>
	</ul>
</div>
@endpush

@section('content')

<div class="row">				
	<div class="col-12">
		@include('app_settings::_settings')	
	</div>
</div>

<div class="row">
    <div class="col-12">
        <form action="{{ route('app_settings.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="telegram_bot_token">Telegram Bot Token</label>
                <input type="text" name="telegram_bot_token" id="telegram_bot_token" class="form-control" value="{{ setting('telegram_bot_token') }}">
            </div>
            <div class="form-group">
                <label for="telegram_chat_id">Telegram Chat ID</label>
                <input type="text" name="telegram_chat_id" id="telegram_chat_id" class="form-control" value="{{ setting('telegram_chat_id') }}">
            </div>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-12 text-right">
        <form action="{{ route('clear.log') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-danger">Clear Log</button>
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mt-4">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger mt-4">
        {{ $errors->first() }}
    </div>
@endif

@endsection

