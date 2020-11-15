<?php

namespace App\Http\Controllers;

use App\Booking;
use App\Client;
use App\Airplane;

use Illuminate\Http\Request;

class BookingController extends Controller
{
	public function book(Request $request) {

		$airplane = new Airplane();
		dd($airplane->provisory(3));
		
		// $booking = Booking::create([
		//     'position' => 1,
		//     'client_id' => 1
		// ]);

		// $booking->save();

	    return response()->json('Hello World', 200);
	}

}
