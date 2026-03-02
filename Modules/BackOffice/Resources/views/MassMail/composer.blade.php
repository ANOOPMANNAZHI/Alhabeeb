@component('mail::message')

Dear {{$user_info->username }},

{!! $user_info->content !!}

Thanks,

{{ config('app.name') }}

@endcomponent