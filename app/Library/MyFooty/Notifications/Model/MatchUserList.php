<?php

namespace Library\MyFooty\Notifications\Model;

use \Illuminate\Support\Collection as Collection;
use App\Models\Match;
use App\User;

class MatchUserList
{
    /**
     * Instance of match
     *
     * @var \App\Models\Match
     */
    protected $match;

    /**
     * Collection of Users
     *
     * @var \Illuminate\Support\Collection
     */
    protected $users;

    /**
     * Create a new MessageSender instance.
     *
     * @param  \App\Models\Match                $match
     * @param  \Illuminate\Support\Collection   $users
     * @return void
     */
    public function __construct(Match $match, Collection $users)
    {
        $this->match = $match;
        $this->users = $users;
    }

    /**
     * Get the match
     *
     * @return \App\Models\Match
     */
    public function getMatch()
    {
        return $this->match;
    }

    /**
     * Get the users
     *
     * @return \Illuminate\Support\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }
}

?>
