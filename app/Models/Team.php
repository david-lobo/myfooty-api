<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Match;

class Team extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'team';

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title'];


    /**
     * Get the comments for the blog post.
     */
    public function homeMatches()
    {
        return $this->hasMany(Match::class, 'home_id', 'id');
    }

    /**
     * Get the comments for the blog post.
     */
    public function awayMatches()
    {
        return $this->hasMany(Match::class, 'away_id', 'id');
    }
}
