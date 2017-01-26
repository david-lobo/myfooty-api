<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamConfig extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'team_config';

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
}
