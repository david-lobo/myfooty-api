<?php

namespace Library\MyFooty\PushNotifications\NotificationSender;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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
use Library\MyFooty\PushNotifications\MessageSender\MessageSender;
use Library\MyFooty\PushNotifications\MessageSender\BatchMessageSender;
use Library\MyFooty\PushNotifications\Model\MatchUserList;
use Library\MyFooty\PushNotifications\NotificationSender\MatchNotificationSender;

class DailyReminderSender extends MatchNotificationSender
{
    /**
     * Create a new DailyNotificationSender instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * The query to use for match list
     *
     * @param  string $date
     * @return string
     */
    protected function getMatchQuery()
    {
        return parent::getMatchQuery();
    }

    /**
     * The query date to use for match list
     *
     * @return string
     */
    protected function getQueryDateAsString()
    {
        // date formatted without time portion
        return parent::getQueryDateAsString();
    }

    /**
     * Create the PushMessage object
     *
     * @param  string $kickOffTime
     * @param  string $broadcasters
     * @return string
     */
    protected function getMessageForAlert($kickOffTime, $broadcasters)
    {
        $message = "Kickoff {$kickOffTime}";
        if (!empty($broadcasters)) {
            $message .= ", Live on {$broadcasters}";
        }
        return $message;
    }
}
