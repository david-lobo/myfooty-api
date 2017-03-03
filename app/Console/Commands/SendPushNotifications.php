<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
use Library\MyFooty\Notifications\MessageSender;
use Library\MyFooty\Notifications\BatchMessageSender;
use Library\MyFooty\Notifications\Model\MatchUserList;

class SendPushNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fixtures:send-apns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send APN notifications';

    /**
     * The name and signature of the console command.
     *
     * @var \Davibennun\LaravelPushNotification\App
     */
    protected $pushApp;

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
    protected $sendMode = MessageSender::SEND_MODE_APNS;

    /**
     * Create a new SendPushNotifications command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::useFiles('php://stderr');
        Log::info('SendPushNotifications running');
        //DB::connection()->enableQueryLog();

        // send to apns server or just db for debug
        $this->sendMode = MessageSender::SEND_MODE_APNS;

        // set the config here - can be dev or prod
        $this->pushApp = PushNotification::app('appNameIOS');

        // date used for queries
        $this->dateNow = Carbon::now();

        //Remove from prod - testing time
        $this->dateNow->day = 4;
        $this->dateNow->month = 3;

        $this->dateNow->hour = 10;
        $this->dateNow->minute = 0;
        $this->dateNow->second = 0;

        $matchUsersLists = $this->getMatchUserListsForDate($this->dateNow);

        if ($matchUsersLists->count() == 0) {
            Log::info("No matches found for today {$this->dateNow->toDateString()}");
            return;
        }

        $matchCount = $matchUsersLists->count();
        $dateNowString = $this->dateNow->toDateString();
        Log::info("Found {$matchCount} matches for today {$dateNowString}");

        $this->sendMatchMessages($matchUsersLists);

        //$queries = DB::getQueryLog();
        //var_dump($queries);
    }

    /**
     * Iterate matches and sends to list of users
     *
     * @param  \Illuminate\Support\Collection $matchUserLists
     * @return void
     */
    protected function sendMatchMessages(Collection $matchUsersLists)
    {
        $collection = $matchUsersLists->each(function ($matchUserList, $key) {

            $match = $matchUserList->getMatch();
            $users = $matchUserList->getUsers();

            if ($users->isEmpty()) {
                $versus = "{$match->homeTeam->title} v {$match->awayTeam->title}";
                Log::info("No users for {$versus}");
                return;
            }

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

        foreach ($matches as $match) {
            $this->info("===================");
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
        $matches = Match::select()
            ->with('competition', 'homeTeam')
            ->whereDate('kickoff', '=', $date->toDateString())
            ->take(10)
            ->get();

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
        $message = "Kick-off {$kickOffTime}";
        if (!empty($broadcasters)) {
            $message .= ", Live on {$broadcasters}";
        }

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
}
