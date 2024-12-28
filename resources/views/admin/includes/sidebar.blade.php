<!-- Sidebar -->
<div class="sidebar" id="sidebar">
	<style>
		@font-face {
			font-family: 'Hanuman';
			src: url('{{ asset('fonts/Hanuman-Regular.ttf') }}') format('truetype');
			font-weight: normal;
			font-style: normal;
		}
		.khmer-text {
			font-family: 'Hanuman', Arial, sans-serif;
		}
	</style>
	<div class="sidebar-inner slimscroll">
		<div id="sidebar-menu" class="sidebar-menu">
			
			<ul>
				<li class="menu-title"> 
					<span>Main</span>
				</li>
				<li class="{{ route_is('dashboard') ? 'active' : '' }}"> 
					<a href="{{route('dashboard')}}"><i class="fe fe-home"></i> <span class="khmer-text">ទំព័រដើម</span></a>
				</li>
				
				@can('view-category')
				<li class="{{ route_is('categories.*') ? 'active' : '' }}"> 
					<a href="{{route('categories.index')}}"><i class="fe fe-layout"></i> <span class="khmer-text">ប្រភេទ</span></a>
				</li>
				@endcan

				@can('view-products')
				<li class="submenu">
    <a href="#">
	<img src="{{ asset('assets/icons/pills-solid.svg') }}" alt="Products Icon" style="width: 20px; height: 23px; filter: brightness(0) invert(1);">
	<span class="khmer-text"> មុខទំនិញ</span>
        <span class="fas fa-chevron-down"></span>
    </a>
    <ul style="display: none;">
        <li><a class="{{ route_is(('products.*')) ? 'active' : '' }}" href="{{route('products.index')}}"><span class="khmer-text">ទំនិញទាំងអស់</span></a></li>
        @can('create-product')
        <li><a class="{{ route_is('products.create') ? 'active' : '' }}" href="{{route('products.create')}}"><span class="khmer-text">បញ្ចូលទំនិញ</span></a></li>
        @endcan
        @can('view-outstock-products')
        <li><a class="{{ route_is('outstock') ? 'active' : '' }}" href="{{route('outstock')}}"><span class="khmer-text">អស់ស្តុក</span></a></li>
        @endcan
        @can('view-expired-products')
        <li><a class="{{ route_is('expired') ? 'active' : '' }}" href="{{route('expired')}}"><span class="khmer-text">ផុតកំណត់</span></a></li>
        @endcan
    </ul>
</li>

				@endcan

				@can('view-supplier')
				<li class="submenu">
					<a href="#"><i class="fe fe-user"></i> <span class="khmer-text"> អ្នកផ្គត់ផ្គង់</span> <span class="fas fa-chevron-down"></span></a>
					<ul style="display: none;">
						@can('create-supplier')<li><a class="{{ route_is('suppliers.create') ? 'active' : '' }}" href="{{route('suppliers.create')}}"><span class="khmer-text">បញ្ចូលអ្នកផ្គត់ផ្គង់</span></a></li>@endcan
						<li><a class="{{ route_is('suppliers.*') ? 'active' : '' }}" href="{{route('suppliers.index')}}"><span class="khmer-text">អ្នកផ្គត់ផ្គង់</span></a></li>
					</ul>
				</li>
				@endcan

				@can('view-purchase')
				<li class="submenu">
    <a href="#"><i class="fe fe-star-o"></i> <span class="khmer-text"> ទំនិញទិញចូល</span> <span class="fas fa-chevron-down"></span></a>
    <ul style="display: none;">
        <li>
            <a class="{{ route_is('purchases.index') ? 'active' : '' }}" href="{{route('purchases.index')}}"><span class="khmer-text">បញ្ជីទំនិញ</span></a>
        </li>
        @can('create-purchase')
        <li>
            <a class="{{ route_is('purchases.create') ? 'active' : '' }}" href="{{route('purchases.create')}}"><span class="khmer-text">បញ្ចូលស្តុក</span></a>
        </li>
        @endcan
    </ul>
</li>


				@endcan
				
				
				
				
				@can('view-sales')
				<li class="submenu">
					<a href="#"><i class="fe fe-activity"></i> <span class="khmer-text"> ការលក់</span> <span class="fas fa-chevron-down"></span></a>
					<ul style="display: none;">
						<li><a class="{{ route_is('sales.*') ? 'active' : '' }}" href="{{route('sales.index')}}"><span class="khmer-text">បញ្ជីការលក់</span></a></li>
						@can('create-sale')
						<li><a class="{{ route_is('sales.create') ? 'active' : '' }}" href="{{route('sales.create')}}"><span class="khmer-text">ការលក់តាម POS</span></a></li>
						@endcan
					</ul>
				</li>
				@endcan
				


				@can('view-reports')
				<li class="submenu">
					<a href="#"><i class="fe fe-document"></i> <span class="khmer-text"> របាយការណ៍</span> <span class="fas fa-chevron-down"></span></a>
					<ul style="display: none;">
						<li><a class="{{ route_is('sales.report') ? 'active' : '' }}" href="{{route('sales.report')}}"><span class="khmer-text">របាយការណ៍ ការលក់</span></a></li>
						<li><a class="{{ route_is('purchases.report') ? 'active' : '' }}" href="{{route('purchases.report')}}"><span class="khmer-text">របាយការណ៍ ទំនិញទិញចូល</span></a></li>
					</ul>
				</li>
				@endcan

				@can('view-access-control')
				<li class="submenu">
					<a href="#"><i class="fe fe-lock"></i> <span> Access Control</span> <span class="fas fa-chevron-down"></span></a>
					<ul style="display: none;">
						@can('view-permission')
						<li><a class="{{ route_is('permissions.index') ? 'active' : '' }}" href="{{route('permissions.index')}}">Permissions</a></li>
						@endcan
						@can('view-role')
						<li><a class="{{ route_is('roles.*') ? 'active' : '' }}" href="{{route('roles.index')}}">Roles</a></li>
						@endcan
					</ul>
				</li>					
				@endcan

				@can('view-users')
				<li class="{{ route_is('users.*') ? 'active' : '' }}"> 
					<a href="{{route('users.index')}}"><i class="fe fe-users"></i> <span>Users</span></a>
				</li>
				@endcan
				
				<li class="{{ route_is('profile') ? 'active' : '' }}"> 
					<a href="{{route('profile')}}"><i class="fe fe-user-plus"></i> <span class="khmer-text">ព័ត៌មានផ្ទាល់ខ្លួន</span></a>
				</li>
				<li class="{{ route_is('backup.index') ? 'active' : '' }}"> 
					<a href="{{route('backup.index')}}"><i class="material-icons">backup</i> <span>Backups</span></a>
				</li>
				@can('view-settings')
				<li class="{{ route_is('settings') ? 'active' : '' }}"> 
					<a href="{{route('settings')}}">
						<i class="material-icons">settings</i>
						 <span> Settings</span>
					</a>
				</li>
				@endcan
			</ul>
		</div>
	</div>
</div><!-- Visit codeastro.com for more projects -->
<!-- /Sidebar -->
