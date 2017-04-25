<?php

namespace Library\MyFooty\PushNotifications\NotificationSender;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;
use \Illuminate\Support\Collection as Collection;
use App\User;
use App\Models\Team;
use App\Models\Match;
use App\Models\Broadcaster;
use App\Models\Competition;
use App\Models\MatchBroadcaster;
use App\Models\NotificationLog;
use Carbon\Carbon;
use Davibennun\LaravelPushNotification\Facades\PushNotification;
use \Sly\NotificationPusher\Model\Device;
use \Sly\NotificationPusher\Model\Message;
use ZendService\Apple\Apns\Message as ZendMessage;
use Library\MyFooty\PushNotifications\MessageSender\MessageSender;
use Library\MyFooty\PushNotifications\MessageSender\BatchMessageSender;
use Library\MyFooty\PushNotifications\Model\MatchUserList;
use CustomLog as CLog;

abstract class MatchNotificationSender
{
    /**
     * The name and signature of the console command.
     *
     * @var \Davibennun\LaravelPushNotification\App
     */
    protected $pushApp;

    /**
     * Collection of matches
     *
     * @var \Illuminate\Support\Collection
     */
    protected $matchUsersLists;

    /**
     * Carbon date
     *
     * @var \Carbon\Carbon
     */
    protected $dateNow;

    /**
     * The send mode variable
     *
     * @var string
     */
    protected $sendMode = MessageSender::SEND_MODE_DB;

    /**
     * Create a new DailyNotificationSender instance.
     *
     * @param string $sendMode mode to send in
     * @param string $pushApp instance of the push app
     * @return void
     */
    public function __construct($sendMode, $pushApp)
    {
        $this->dateNow = Carbon::now();
        $this->sendMode = $sendMode;
        $this->pushApp = $pushApp;
    }

    /**
     * Set the dateNow property
     *
     * @param  \Carbon\Carbon $dateNow
     * @return void
     */
    public function setDateNow(Carbon $dateNow)
    {
        $this->dateNow = $dateNow;
    }

    /**
     * Iterate matches and sends to list of users
     *
     * @return void
     */
    public function send()
    {
        CLog::info("{$this->getClassName()}");

        $this->matchUsersLists = $this->getMatchUserListsForDate($this->dateNow);

        $dateNowString = $this->getQueryDateAsString();

        if ($this->matchUsersLists->count() == 0) {
            CLog::info("No matches found for today {$dateNowString}");
            return;
        }

        $matchCount = $this->matchUsersLists->count();

        CLog::info("Found {$matchCount} matches for today {$dateNowString}");

        $this->sendMatchMessages();
    }

    /**
     * Iterate matches and sends to list of users
     *
     * @param  \Illuminate\Support\Collection $matchUserLists
     * @return void
     */
    protected function sendMatchMessages()
    {
        $collection = $this->matchUsersLists->each(function ($matchUserList, $key) {

            $match = $matchUserList->getMatch();
            $users = $matchUserList->getUsers();

            $versus = "{$match->homeTeam->title} v {$match->awayTeam->title}";

            if ($users->isEmpty()) {
                CLog::info("0 users for {$versus}");
                return;
            }

            CLog::info("{$users->count()} users for {$versus}");

            $message = $this->createPushMessageForMatch($match);
            $sender = new BatchMessageSender($this->pushApp);

            // send to apns/db or just db for debugging
            $sender->setSendMode($this->sendMode);

            // send message to a batch of users
            $sender->send($message, $users);
        });
    }

    /**
     * Create a collection of Matches with Users
     *
     * @param  \Carbon\Carbon $date
     * @return \Illuminate\Support\Collection
     */
    protected function getMatchUserListsForDate(Carbon $date)
    {
        $matchUsers = collect([]);
        $matches = $this->findMatchesForDate($date);

        //$queries = DB::getQueryLog();
        //var_dump($queries);

        foreach ($matches as $match) {
            //Log::info("===================");
            $users = $this->findUsersForMatch($match);
            /*$matchMessage = [
                'match' => $match,
                'users' => $users
            ];*/

            $matchUserList = new MatchUserList($match, $users);
            $matchUsers->push($matchUserList);
        }

        return $matchUsers;
    }

    /**
     * Find matches occuring on a date
     *
     * @param  \Carbon\Carbon $date
     * @return \Illuminate\Support\Collection
     */
    protected function findMatchesForDate(Carbon $date)
    {
        $matchQuery = $this->getMatchQuery();
        $matches = $matchQuery->get();

        if ($matches->count() == 0) {
            return collect([]);
        }


        return $matches;
    }

    /**
     * Find the users who support either team
     *
     * @param  \App\Models\Match $match
     * @return \Illuminate\Support\Collection
     */
    protected function findUsersForMatch(Match $match)
    {
        $users = User::select()
        ->where('is_notifications_enabled', 1)
        ->whereNotNull('apns_token')
        ->where(function ($query) use ($match) {
            $query->orWhere('team_alias', $match->homeTeam->title_normalised);
            $query->orWhere('team_alias', $match->awayTeam->title_normalised);
        })
        ->get();

        return $users;
    }

    /**
     * Create the PushMessage object
     *
     * @param  \App\Models\Match $match
     * @return \Sly\NotificationPusher\Model\Message
     */
    protected function createPushMessageForMatch(Match $match)
    {
        $homeTeamId = $match->homeTeam->id;
        $homeTeamTitle = $match->homeTeam->title;
        $homeTeamTitleNormalised = $match->homeTeam->title_normalised;

        $awayTeamId = $match->awayTeam->id;
        $awayTeamTitle = $match->awayTeam->title;
        $awayTeamTitleNormalised = $match->awayTeam->title_normalised;

        $kickOff = new Carbon($match->kickoff);
        $kickOffTimeFormat = "g:iA";
        if ($kickOff->minute == 0) {
            $kickOffTimeFormat = "gA";
        }
        $kickOffTime = $kickOff->format($kickOffTimeFormat);
        $kickoffDateTime = $kickOff->format('Y-m-d H:i:s');

        $broadcasters = $match->broadcastersFlat;

        $title = "{$homeTeamTitle} v {$awayTeamTitle}";
        /*$message = "Kickoff {$kickOffTime}";
        if (!empty($broadcasters)) {
            $message .= ", Live on {$broadcasters}";
        }*/

        $message = $this->getMessageForAlert($kickOffTime, $broadcasters);

        $message = PushNotification::Message($message, array(
            'badge' => 1,
            'sound' => 'default',
            'title' => $title,
            'category' => 'NEWS_CATEGORY',
            'custom' => array(
                'match' => array(
                    'kickoff' => $kickoffDateTime
                )
            )

            /* Additional optional params

            'actionLocKey' => 'Reminder',
            'locKey' => 'localized key',
            'locArgs' => array(
            'localized args',
            'localized args',
            ),
            'launchImage' => 'image.jpg',

            */
        ));

        return $message;
    }

    /**
     * Create the PushMessage object
     *
     * @param  string $kickOffTime
     * @param  string $broadcasters
     * @return string
     */
    abstract protected function getMessageForAlert($kickOffTime, $broadcasters);

    /**
     * The query date to use for match list
     *
     * @return string
     */
    protected function getQueryDateAsString()
    {
        return $this->dateNow->toDateString();
    }

    /**
     * The query to use for match list
     *
     * @param  string $date
     * @return string
     */
    protected function getMatchQuery()
    {
        $date = $this->dateNow->toDateString();
        $matches = Match::select()
            ->with('competition', 'homeTeam')
            ->whereDate('kickoff', '=', $date)
            ->take(10);

        return $matches;
    }

    public function getClassName()
    {
        $path = explode('\\', static::class);
        return array_pop($path);
    }
}
