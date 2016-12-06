<?php

namespace App\Models;

use App\Models\BaseModel;

class MatchBroadcaster extends BaseModel
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'match_broadcaster';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['match_id', 'broadcaster_id'];

    protected static $rules = [
        'match_id' => 'required|exists:match,id',
        'broadcaster_id' => 'required|exists:broadcaster,id'
    ];
}
