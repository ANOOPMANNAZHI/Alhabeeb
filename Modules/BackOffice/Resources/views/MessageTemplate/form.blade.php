@extends('layouts.plms-app')
@section('content')
<div>{{ $template ? $template->name : 'New' }}</div>
@endsection
