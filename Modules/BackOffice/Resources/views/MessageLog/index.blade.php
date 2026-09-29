@extends('layouts.plms-app')
@section('content')
@foreach($logs as $l) <div>{{ $l->recipient }}</div> @endforeach
@endsection
