@extends('adminlte::page')
@section('title', 'sendPost')
@section('template_title')
    Eventos SENDPOST
@endsection

@section('content')
    @livewire('events')
    @include('footer')
@endsection
