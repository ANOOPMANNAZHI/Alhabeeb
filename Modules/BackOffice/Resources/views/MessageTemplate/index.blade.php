@extends('layouts.plms-app')
@section('content')
@foreach($templates as $t) <div>{{ $t->name }}</div> @endforeach
@endsection
