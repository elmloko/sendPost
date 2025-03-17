<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CarteroController extends Controller
{
    public function getDistribuicion()
    {
        return view('cartero.distribuicion');
    }
    public function getEntregas()
    {
        return view('cartero.entregas');
    }
    public function getDespacho()
    {
        return view('cartero.despacho');
    }
    public function getInventario()
    {
        return view('cartero.inventario');
    }
    public function getPaquetes()
    {
        return view('cartero.paquetes');
    }
}
