<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Models\Match;

class Team extends BaseModel
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
    protected $fillable = [
        'title',
        'title_normalised',
        'premier_league',
        'background_color',
        'text_color',
        'image'
    ];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at'];

    protected static $rules = [
        'title' => 'required',
        'title_normalised' => 'required|unique:team,title_normalised',
        'premier_league' => 'required'
    ];

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
