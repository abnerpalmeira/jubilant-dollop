<?php
namespace App\Http\Controllers;

use App\Booking;
use App\Client;
use App\Airplane;
use DB;

use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function book(Request $request)
    {

        $airplane = new Airplane();
        $result = $airplane->getAvailableSeats($request['seats']);

        if (!$result)
        {
            return response()->json(array(
                'error' => 'Sorry, We don\'t have enough seats avaible.'
            ) , 424);
        }

        DB::beginTransaction();

        $client = Client::create(['name' => $request['name']]);

        $client->save();

        foreach ($result as $value)
        {
            $booking = Booking::create(['position' => $value, 'client_id' => $client->id]);

            $booking->save();
        }

        DB::commit();

        return response()
            ->json($airplane->numbersToAirplaneNotation($result) , 200);
    }

    public function flush()
    {
    	DB::statement("SET foreign_key_checks=0");
    	Booking::truncate();
    	Client::truncate();
    	DB::statement("SET foreign_key_checks=1");

    	return response()
            ->json('Success' , 200);
    }

}

