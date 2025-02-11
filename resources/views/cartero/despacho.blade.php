@extends('adminlte::page')

@section('title', 'Despacho Cartero')

@section('template_title')
    Sistema de Gestion de Entregas en Ventanilla y Carteros
@endsection

@section('content')
    @livewire('despacho')
    @include('footer')
@stop
