@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif



@if (Session::has('error'))
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">

    <div class="alert label-danger alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <strong>Error!</strong> {{ Session::get('error') }}
    </div>

            </div>
        </div>
    </div>
@endif
