<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BookingController extends Controller
{
    
    public function index(){
        // Logic to retrieve and display bookings
        return view('bookings.index');
    }
}
