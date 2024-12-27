<!-- Header -->
<div class="header">
			
	<!-- Logo -->
	<div class="header-left">
		<a href="{{route('dashboard')}}" class="logo">
			<img src="@if(!empty(AppSettings::get('logo'))) {{asset('storage/'.AppSettings::get('logo'))}} @else{{asset('assets/img/pharrrlg.png')}} @endif" alt="Logo">
		</a>
		<a href="{{route('dashboard')}}" class="logo logo-small">
			<img src="{{asset('assets/img/logo-small.png')}}" alt="Logo" width="30" height="30">
		</a>
	</div>
	<!-- /Logo -->
	
	<a href="javascript:void(0);" id="toggle_btn">
		<i class="fe fe-text-align-left"></i>
	</a>
	
	<!-- Visit codeastro.com for more projects -->
	<!-- Mobile Menu Toggle -->
	<a class="mobile_btn" id="mobile_btn">
		<i class="fa fa-bars"></i>
	</a>
	<!-- /Mobile Menu Toggle -->
	
	<!-- Header Right Menu -->
	<ul class="nav user-menu">
		<li class="nav-item dropdown">
			<a href="{{ route('sales.create') }}" title="make a sale" class="nav-link">
				<i class="fas fa-cash-register"></i>
			</a>
		</li>
		<!-- Notifications -->
		<li class="nav-item dropdown noti-dropdown">
			
			<a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
				<i class="fe fe-bell"></i> <span class="badge badge-pill">{{auth()->user()->unReadNotifications->count()}}</span>
			</a>
			<div class="dropdown-menu notifications">
				<div class="topnav-dropdown-header">
					<span class="notification-title">Notifications</span>
					<a href="{{route('mark-as-read')}}" class="clear-noti" onclick="return confirm('Are you sure you want to mark all notifications as read?')">Mark All As Read </a>
					<a href="{{ route('reload-notifications') }}" class="clear-noti" title="Reload Notifications" style="margin-right: 105px;">
    <i class="fas fa-sync-alt" style="color: blue; font-size: 1.2em;"></i>
</a>
				</div>
				<div class="noti-content">
					<ul class="notification-list">
						@foreach (auth()->user()->unReadNotifications as $notification)
							<li class="notification-message">
								<a href="{{route('read', $notification->id)}}" onclick="return confirm('Are you sure you want to mark this notification as read?')">
									<div class="media">
										<div class="media-body">
											@if(isset($notification->data['type']))
												@if($notification->data['type'] == 'low_stock')
													<h6 class="text-danger">Stock Alert</h6>
													<p class="noti-details">
														<span class="noti-title">
															{{ $notification->data['product_name'] }} is only {{ $notification->data['quantity'] }} left.
														</span>
														<span>Please update the quantity </span>
													</p>
												@elseif($notification->data['type'] == 'expired')
													<h6 class="text-danger">Expired Alert</h6>
													<p class="noti-details">
														<span class="noti-title">{{$notification->data['product_name']}} has expired.</span>
													</p>
												@elseif($notification->data['type'] == 'nearly_expired')
													<h6 class="text-warning">Nearly Expired Alert</h6>
													<p class="noti-details">
														<span class="noti-title">{{$notification->data['product_name']}} is nearly expired.</span>
													</p>
												@endif
											@endif
											<p class="noti-time"><span class="notification-time">{{$notification->created_at->diffForHumans()}}</span></p>
										</div>
									</div>
								</a>
							</li>
						@endforeach						
					</ul>
				</div>

			</div>
		</li>
		<!-- /Notifications -->
		
		<!-- User Menu -->
		<li class="nav-item dropdown has-arrow">
			<a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
				<span class="user-img"><img class="rounded-circle" src="{{!empty(auth()->user()->avatar) ? asset('storage/users/'.auth()->user()->avatar): asset('assets/img/avatar_1nn.png')}}" width="31" alt="avatar"></span>
			</a>
			<div class="dropdown-menu">
				<div class="user-header">
					<div class="avatar avatar-sm">
						<img src="{{!empty(auth()->user()->avatar) ? asset('storage/users/'.auth()->user()->avatar): asset('assets/img/avatar_1nn.png')}}" alt="User Image" class="avatar-img rounded-circle">
					</div>
					<div class="user-text">
						<h6>{{auth()->user()->name}}</h6>
					</div>
				</div>
				
				<a class="dropdown-item" href="{{route('profile')}}">My Profile</a>
				@can('view-settings')<a class="dropdown-item" href="{{route('settings')}}">Settings</a>@endcan
				
				<a href="javascript:void(0)" class="dropdown-item">
					<form action="{{route('logout')}}" method="post">
					@csrf
					<button type="submit" class="btn">Logout</button>
				</form>
				</a>
			</div>
		</li>
		<!-- /User Menu -->
		
	</ul>
	<!-- /Header Right Menu -->
	
</div>
<!-- /Header -->