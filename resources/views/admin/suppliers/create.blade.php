@extends('admin.layouts.app')

@push('page-css')
	<!-- Datetimepicker CSS -->
	<link rel="stylesheet" href="{{asset('assets/css/bootstrap-datetimepicker.min.css')}}">
	<link rel="stylesheet" href="{{asset('assets/plugins/chart.js/Chart.min.css')}}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Hanuman&display=swap">
    <style>
        .khmer-text {
            font-family: 'Hanuman', serif;
        }
    </style>
@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title khmer-text">បញ្ចូលអ្នកផ្គត់ផ្គង់</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item khmer-text"><a href="{{route('dashboard')}}">ទំព័រដើម</a></li>
		<li class="breadcrumb-item active khmer-text">បញ្ចូលអ្នកផ្គត់ផ្គង់</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body custom-edit-service">
				
		
			<!-- Add Supplier -->
			<form method="post" enctype="multipart/form-data" action="{{route('suppliers.store')}}">
				@csrf
				
				<div class="service-fields mb-3">
					<div class="row">
						<div class="col-lg-6">
							<div class="form-group">
								<label class="khmer-text">ឈ្មោះ<span class="text-danger">*</span></label>
								<input class="form-control" type="text" name="name">
							</div>
						</div>
						<div class="col-lg-6">
							<label class="khmer-text">លេខទូរស័ព្ទ</label>
							<input class="form-control" type="text" name="phone">
						</div>
					</div>
				</div>

				<div class="service-fields mb-3">
					<div class="row">
						<div class="col-lg-6">
							<div class="form-group">
								<label class="khmer-text">Email</label>
								<input class="form-control" type="text" name="email" id="email">
							</div>
						</div>
						<div class="col-lg-6">
							<label class="khmer-text">អាស័យដ្ឋាន</label>
							<input class="form-control" type="text" name="address">
						</div>
					</div>
				</div>

				<!-- Comment Section Removed -->
				
				<div class="submit-section">
					<button class="btn btn-success submit-btn" type="submit" name="form_submit" value="submit">Submit</button>
				</div>
			</form>
			<!-- /Add Supplier -->

			</div>
		</div>
	</div>			
</div>
@endsection	

@push('page-js')
	<!-- Datetimepicker JS -->
	<script src="{{asset('assets/js/moment.min.js')}}"></script>
	<script src="{{asset('assets/js/bootstrap-datetimepicker.min.js')}}"></script>	
@endpush
