<?php

namespace App\Models;

use App\Models\Team;
use App\Models\Competition;
use App\Models\Broadcaster;
use App\Models\BaseModel;

class Match extends BaseModel
{
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
    protected $table = 'match';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['home_id', 'away_id', 'kickoff', 'competition_id'];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['broadcasters_flat'];

    protected static $rules = [
        'home_id' => 'required|exists:team,id',
        'away_id' => 'required|exists:team,id',
        'competition_id' => 'required|exists:competition,id',
        'kickoff' => 'required|date_format:Y-m-d H:i:s'
    ];

    /**
     * Get the post that owns the comment.
     */
    public function homeTeam()
    {
        return $this->belongsTo(Team::class, 'home_id');
    }

    /**
     * Get the post that owns the comment.
     */
    public function awayTeam()
    {
        return $this->belongsTo(Team::class, 'away_id');
    }

    /**
     * Get the post that owns the comment.
     */
    public function competition()
    {
        return $this->belongsTo(Competition::class, 'competition_id');
    }

    public function broadcasters() {
        return $this->belongsToMany(Broadcaster::class, 'match_broadcaster', 'match_id', 'broadcaster_id')->withTimestamps();;
    }

        /**
     * Get the administrator flag for the user.
     *
     * @return bool
     */
    public function getBroadcastersFlatAttribute()
    {
        return $this->broadcasters->implode('title', ', ');
        //return $this->attributes['admin'] == 'yes';
    }
}
