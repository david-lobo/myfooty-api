<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Team;
use App\Models\Competition;
use App\Models\Broadcaster;

class Match extends Model
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
}
