<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Models\Match;

class Competition extends BaseModel
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
    protected $fillable = ['title', 'title_normalised'];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at'];

    protected static $rules = [
        'title' => 'required',
        'title_normalised' => 'required|unique:competition,title_normalised'
    ];

    /**
     * Get the comments for the blog post.
     */
    public function matches()
    {
        return $this->hasMany(Match::class, 'competition_id', 'id');
    }
}
