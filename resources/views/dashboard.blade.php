@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Sistema de Gestion de Entregas en Ventanilla y Carteros</h1>
@stop

@section('content')
    @livewire('dashboard')
    @include('footer')
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}

@stop

@section('js')

@stop
