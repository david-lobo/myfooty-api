<?php

namespace App\Models;
use App\Models\Match;
use App\Models\BaseModel;

class Broadcaster extends BaseModel
{
   	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
    protected $table = 'broadcaster';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'title_normalised',];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at'];

    protected static $rules = [
        'title' => 'required',
        'title_normalised' => 'required|unique:broadcaster,title_normalised'
    ];

    public function matches() {
        return $this->belongsToMany(Match::class, 'match_broadcaster', 'broadcaster_id', 'match_id')->withTimestamps();;
    }
}
