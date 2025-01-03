@extends('admin.layouts.app')

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">Backups</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">App Backups</li>
	</ul>
</div>
<div class="col-sm-5 col">
    <form action="{{ route('backup.manualExport') }}" method="get" class="float-right mr-2">
        @csrf
        <button class="btn btn-primary mt-2" type="submit">Export DB SQL to Telegram</button>
    </form>
    <form action="{{ route('backup.storage') }}" method="get" class="float-right mr-2">
        @csrf
        <button class="btn btn-primary mt-2" type="submit">Backup Storage Folder</button>
    </form>
    <form action="{{ route('backup.import') }}" method="POST" enctype="multipart/form-data" class="float-right mr-2">
        @csrf
        <input type="file" name="sql_file" accept=".sql" required>
        <button class="btn btn-primary mt-2" type="submit">Import DB SQL</button>
    </form>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        {{ $errors->first() }}
    </div>
@endif

@endpush

@section('content')

<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<table id="category-table" class="datatable table table-striped table-bordered table-hover table-center mb-0">
						<thead>
							<tr style="boder:1px solid black;">
                                <th>ID</th>
                                <th>Disk</th>
                                <th>Backup Date</th>
                                <th>File Size</th>
								<th class="text-center action-btn">Actions</th>
							</tr>
						</thead>
						<tbody>
                            @foreach ($backups as $k => $b)
                            <tr>
                                <td>{{ $k+1 }}</td>
                                <td>{{ $b['disk'] }}</td>
                                <td>{{ \Carbon\Carbon::createFromTimeStamp($b['last_modified'])->formatLocalized('%d %B %Y, %H:%M') }}</td>
                                <td>{{ round((int)$b['file_size']/1048576, 2).' MB' }}</td>
                                <td class="text-center">
                                    <div class="actions">
                                        @if ($b['download'])
                                        <a class="float-left" href="{{ route('backup.download') }}?disk={{ $b['disk'] }}&path={{ urlencode($b['file_path']) }}&file_name={{ urlencode($b['file_name']) }}">
                                            <button title="download backup" class="btn btn-success" >
                                                <i class="fe fe-download"></i>
                                            </button>
                                        </a>
                                        @endif
                                        <form action="{{route('backup.destroy',$b['file_name'])}}?disk={{ $b['disk'] }}" method="post">
                                            @csrf
                                            @method("DELETE")
                                            <button title="delete backup" class="btn btn-danger" type="submit">
                                                <i class="fe fe-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>			
</div>

@endsection

@push('page-js')
	
@endpush