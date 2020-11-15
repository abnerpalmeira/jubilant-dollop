<?php

namespace App;

use App\Booking;
use Storage;

class Airplane
{

    private $type;
    private $seats;
    private $rows;
    private $row;
    private $contiguous;
    private $map;
    private $matrix;

    function __construct() {

        $bookings = Booking::all();

        $json = Storage::disk('public')->get('config.json');
        $json = json_decode($json, true);

        $this->type = $json['aircraft_type'];
        $this->seats = $json['sits_count'];
        $this->rows = $json['rows'];
        $this->row = $this->seats/$this->rows;
        $this->contiguous = $this->row/2;
        $this->map = array_fill(0, $this->seats, 0);

        foreach ($bookings as $b) {
            $this->map[$b->position] = 1; 
        }

    }

    private function matrixNotationToNumber($i,$j,$k){
        return $i * $this->contiguous + $j * $this->row + $k;
    }

    private function seatsInRange($a,$b){

        $seats = [];

        for($i=$a; $i <= $b; $i++){
            $seats[] = $i;
        }

        return $seats;

    }

    private function findContiguousSeats($passengers){

        for($j=0;$j<$this->rows;$j++){

            for($i=0;$i<2;$i++){

                $vacant = 0;

                if(!$i){
                    for ($k=0; $k < $this->contiguous; $k++) { 
                        if($this->matrix[$i][$j][$k]){
                            $vacant = 0;
                        }else{
                            $vacant++;
                        }

                        if($vacant == $passengers){
                            $a = $this->matrixNotationToNumber($i,$j,$k);
                            $b = $this->matrixNotationToNumber($i,$j,$k-$vacant+1);
                            return $this->seatsInRange($b,$a);
                        }
                    
                    }
                }
                else{
                    for ($k=$this->contiguous-1; $k >= 0; $k--) {
                        if($this->matrix[$i][$j][$k]){
                            $vacant = 0;
                        }else{
                            $vacant++;
                        }

                        if($vacant == $passengers){
                            $a = $this->matrixNotationToNumber($i,$j,$k);
                            $b = $this->matrixNotationToNumber($i,$j,$k+$vacant-1);
                            return $this->seatsInRange($a,$b);
                        }
                    
                    }
                }

            }
        }

        return false;
    }

    private function findSeatsAcrossTheRows($passengers){

        for ($i=$this->contiguous; $i > 1; $i++) {
            if($passengers%$i == 1 || $passengers == $i) continue;

            $total_per_row = (int)($passengers/$i);
            $remaining = $passengers;
            $extra = $passengers%$i;

            for($i = 0; $i <= 2; $i++){
                $current = 0;
                for($j = 0; $j < $this->rows; $j++){

                    $vacant = 0;

                    if($i & 1){
                    
                        for ($k=0; $k < $this->contiguous; $k++) {

                            if($this->matrix[$i][$j][$k]){
                                $vacant = 0;
                            }else{
                                $vacant++;
                            }

                            if($remaining >= $total_per_row && $vacant == $total_per_row){
                                
                            }

                        }
                    }

                }
            } 
        }

        return false;
    }

    public function provisory($passengers){

        $this->matrix = array_fill(0, 2, array_fill(0,$this->rows, array_fill(0, $this->contiguous, 0)));

        foreach ($this->map as $key => $value) {
            
            if($value){

                $aisle_side = 0;
                if($key%$this->row >= $this->contiguous) $aisle_side = 1;
                
                $this->matrix[$aisle_side][(int)($key/$this->row)][$key%$this->contiguous] = 1;

            }

        }

        if($passengers <= $this->contiguous){

            $response = $this->findContiguousSeats($passengers);

            if($response) return $response;

        }else{
            $response = $this->findSeatsAcrossTheRows($passengers);
        }

    }


}
