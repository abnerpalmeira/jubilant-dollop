<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BookingController extends Controller
{
	public function book(Request $request) {
	    return response()->json('Hello World', 200);
	}
}
