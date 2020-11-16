<?php
/**
* Class and Function List:
* Function list:
* - __construct()
* - matrixNotationToNumber()
* - seatsInRange()
* - findContiguousSeats()
* - firstCase()
* - secondCase()
* - thirdCase()
* - anySeats()
* - processMatrix()
* - processCrossAisleSumArray()
* - processSumArray()
* - getAvailableSeats()
* - numbersToAirplaneNotation()
* Classes list:
* - Airplane
*/
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
    private $sum_array;
    private $cross_aisle_sum;

    function __construct()
    {

        $bookings = Booking::all();

        $json = Storage::disk('public')->get('config.json');
        $json = json_decode($json, true);

        $this->type = $json['aircraft_type'];
        $this->seats = $json['sits_count'];
        $this->rows = $json['rows'];
        $this->row = $this->seats / $this->rows;
        $this->contiguous = $this->row / 2;
        $this->map = array_fill(0, $this->seats, 0);

        foreach ($bookings as $b)
        {
            $this->map[$b->position] = 1;
        }

    }

    private function matrixNotationToNumber($i, $j, $k)
    {
        return $i * $this->contiguous + $j * $this->row + $k;
    }

    private function seatsInRange($a, $b)
    {

        $seats = [];

        for ($i = $a;$i <= $b;$i++)
        {
            $seats[] = $i;
        }

        return $seats;

    }

    private function findContiguousSeats($passengers, $row, $left = true)
    {

        $vacant = 0;

        if ($left)
        {
            for ($i = 0;$i < $this->contiguous;$i++)
            {

                if ($this->matrix[0][$row][$i])
                {
                    $vacant = 0;
                }
                else
                {
                    $vacant++;
                }

                if ($vacant == $passengers)
                {
                    $a = $this->matrixNotationToNumber(0, $row, $i);
                    $b = $this->matrixNotationToNumber(0, $row, $i - $vacant + 1);
                    return $this->seatsInRange($b, $a);
                }

            }
        }
        else
        {
            for ($i = $this->contiguous - 1;$i >= 0;$i--)
            {

                if ($this->matrix[1][$row][$i])
                {
                    $vacant = 0;
                }
                else
                {
                    $vacant++;
                }

                if ($vacant == $passengers)
                {
                    $a = $this->matrixNotationToNumber(1, $row, $i);
                    $b = $this->matrixNotationToNumber(1, $row, $i + $vacant - 1);
                    return $this->seatsInRange($a, $b);
                }

            }
        }

        return false;

    }

    private function firstCase($passengers)
    {

        for ($i = 0;$i < $this->rows;$i++)
        {

            $seats = $this->findContiguousSeats($passengers, $i);
            if ($seats) return $seats;

            $seats = $this->findContiguousSeats($passengers, $i, false);
            if ($seats) return $seats;

        }

        return false;
    }

    private function secondCase($passengers)
    {

        $this->processSumArray();
        for ($i = 2;$i <= $this->contiguous;$i++)
        {

            $i = $i;
            if ($passengers % $i == 1 || $passengers == $i) continue;

            $extra = $passengers % $i;
            $remaining = $passengers;
            $first = 0;
            $seats = [];

            for ($j = 0;$j < $this->rows;$j++)
            {

                if ($this->sum_array[0][$j] >= $i && $remaining >= $i)
                {
                    $remaining -= $i;
                    $seats = array_merge($seats, $this->findContiguousSeats($i, $j));
                }
                else if ($this->sum_array[0][$j] >= $extra && $extra)
                {
                    $remaining -= $extra;
                    $seats = array_merge($seats, $this->findContiguousSeats($extra, $j));
                    $extra = 0;
                }
                else
                {
                    $first = $j + 1;
                    $extra = $passengers % $i;
                    $remaining = $passengers;
                    $seats = [];
                }

                if (!$remaining)
                {
                    return $seats;
                }

            }

            $seats = [];

            for ($j = 0;$j < $this->rows;$j++)
            {

                if ($this->sum_array[1][$j] >= $i && $remaining >= $i)
                {
                    $remaining -= $i;
                    $seats = array_merge($seats, $this->findContiguousSeats($i, $j, false));
                }
                else if ($this->sum_array[1][$j] >= $extra && $extra)
                {
                    $remaining -= $extra;
                    $seats = array_merge($seats, $this->findContiguousSeats($extra, $j, false));
                    $extra = 0;
                }
                else
                {
                    $first = $j + 1;
                    $extra = $passengers % $i;
                    $remaining = $passengers;
                    $seats = [];
                }

                if (!$remaining)
                {
                    return $seats;
                }
            }

        }

        return false;
    }

    private function thirdCase($passengers)
    {

        $this->processCrossAisleSumArray();

        $first = 0;

        for ($i = 0;$i < $this->rows;$i++)
        {

            if ($this->cross_aisle_sum[$i] >= $passengers)
            {

                $seats = [];

                for ($j = $first;$j <= $i;$j++)
                {

                    $k = $this->contiguous - 1;
                    $l = 0;

                    while ($passengers && (($k >= 0 && !$this->matrix[0][$j][$k]) || ($l < $this->contiguous && !$this->matrix[1][$j][$l])))
                    {

                        if ($passengers && $k >= 0 && !$this->matrix[0][$j][$k])
                        {
                            $seats[] = $this->matrixNotationToNumber(0, $j, $k);
                            $passengers--;
                            $k--;
                        }

                        if ($passengers && $l < $this->contiguous && !$this->matrix[1][$j][$l])
                        {
                            $seats[] = $this->matrixNotationToNumber(1, $j, $l);
                            $passengers--;
                            $l++;
                        }

                    }
                }

                return $seats;

            }
            else if (!$this->cross_aisle_sum[$i])
            {
                $seats = [];
                $first = $i + 1;
            }
        }

        return false;
    }

    private function anySeats($passengers)
    {

        $seats = [];

        for ($i = 0;$i < $this->seats && $passengers;$i++)
        {
            if (!$this->map[$i])
            {
                $seats[] = $i;
                $passengers--;
            }
        }

        if (!$passengers) return $seats;

        return false;

    }

    private function processMatrix()
    {

        $this->matrix = array_fill(0, 2, array_fill(0, $this->rows, array_fill(0, $this->contiguous, 0)));

        foreach ($this->map as $key => $value)
        {

            if ($value)
            {

                $aisle_side = 0;
                if ($key % $this->row >= $this->contiguous) $aisle_side = 1;

                $this->matrix[$aisle_side][(int)($key / $this->row) ][$key % $this->contiguous] = 1;

            }

        }

    }

    private function processCrossAisleSumArray()
    {

        $cross_aisle = array_fill(0, $this->rows, 0);
        $this->cross_aisle_sum = array_fill(0, $this->rows, 0);

        for ($i = 0;$i < $this->rows;$i++)
        {
            $current = 0;
            $j = $this->contiguous - 1;
            $k = 0;

            while ($j >= 0 && !$this->matrix[0][$i][$j] && $k < $this->contiguous && !$this->matrix[1][$i][$k])
            {
                $j--;
                $k++;
                $current += 2;
            }
            while ($current && $j >= 0 && !$this->matrix[0][$i][$j--]) $current++;
            while ($current && $k < $this->contiguous && !$this->matrix[1][$i][$k++]) $current++;

            $cross_aisle[$i] = $current;
            $this->cross_aisle_sum[$i] = $current;

            if ($i && $current)
            {
                $this->cross_aisle_sum[$i] += $this->cross_aisle_sum[$i - 1];
            }

        }

    }

    private function processSumArray()
    {

        $this->sum_array = array_fill(0, 2, array_fill(0, $this->rows, 0));

        for ($i = 0;$i < $this->rows;$i++)
        {

            $current = 0;

            for ($j = 0;$j < $this->contiguous;$j++)
            {

                if ($this->matrix[0][$i][$j])
                {
                    $current = 0;
                }
                else
                {
                    $current++;
                }

                $this->sum_array[0][$i] = max($this->sum_array[0][$i], $current);
            }

            $current = 0;

            for ($j = $this->contiguous - 1;$j >= 0;$j--)
            {

                if ($this->matrix[1][$i][$j])
                {
                    $current = 0;
                }
                else
                {
                    $current++;
                }

                $this->sum_array[1][$i] = max($this->sum_array[1][$i], $current);
            }
        }

    }

    public function getAvailableSeats($passengers)
    {

        $this->processMatrix();

        if ($passengers <= $this->contiguous)
        {

            $response = $this->firstCase($passengers);

            if ($response) return $response;

        }

        $response = $this->secondCase($passengers);

        if ($response) return $response;

        $response = $this->thirdCase($passengers);

        if ($response) return $response;

        $response = $this->anySeats($passengers);

        if ($response) return $response;

        return false;

    }

    public function numbersToAirplaneNotation($numbers)
    {

        $seats = [];

        foreach ($numbers as $value)
        {

            $char = chr(65 + ($value % $this->row));
            $row = (int)($value / $this->row) + 1;

            $seats[] = "$char$row";

        }


        return $seats;

    }

}

