<?php

namespace App\Models;
use App\Models\Match;

use Illuminate\Database\Eloquent\Model;

class Broadcaster extends Model
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

    public function matches() {
        return $this->belongsToMany(Match::class, 'match_broadcaster', 'broadcaster_id', 'match_id')->withTimestamps();;
    }
}
