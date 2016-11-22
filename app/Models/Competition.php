<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Match;

class Competition extends Model
{
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
    protected $table = 'competition';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'title_normalised', 'priority'];
    
    /**
     * Get the comments for the blog post.
     */
    public function matches()
    {
        return $this->hasMany(Match::class, 'competition_id', 'id');
    }
}
