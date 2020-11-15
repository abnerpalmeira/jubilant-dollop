<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    public $table = 'booking';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public $fillable = [
        'position',
        'client_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */

    protected $casts = [
        'id' => 'integer',
        'client_id' => 'integer',
        'position' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */

    public static $rules = [
        'position' => 'required|integer',
        'client_id' => 'required|integer'
    ];
}
