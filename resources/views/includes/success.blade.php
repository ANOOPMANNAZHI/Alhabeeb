@if (Session::has('success'))
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">

    <div class="alert label-success alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <strong>Success!</strong> {{ Session::get('success') }}
    </div>

            </div>
        </div>
    </div>
@endif

