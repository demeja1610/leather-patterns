@extends('layouts.app')

@section('content')
    <div class="page page--reset-password">
        <x-form.reset-password
            :token="$token"
            :email="$email"
        />
    </div>
@endsection
