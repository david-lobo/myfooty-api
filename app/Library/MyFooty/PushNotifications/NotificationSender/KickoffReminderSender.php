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
use Library\MyFooty\PushNotifications\NotificationSender\NotificationSender;

class KickoffReminderSender extends MatchNotificationSender
{
    /**
     * Minutes Before kickoff to send reminder
     *
     * @var int
     */
    protected $minutesBefore;

    /**
     * Date with offset applied
     *
     * @var \Carbon\Carbon
     */
    protected $dateNowWithOffset;

    /**
     * Create a new DailyNotificationSender instance.
     *
     * @return void
     */
    public function __construct($sendMode)
    {
        parent::__construct($sendMode);

        $this->minutesBefore = 30;
    }

    /**
     * Get minutes before
     *
     * @return int
     */
    public function getMinutesBefore()
    {
        return $this->minutesBefore;
    }

    /**
     * Set minutes before
     *
     * @param  int  $minutesBefore
     * @return void
     */
    public function setMinutesBefore($minutesBefore)
    {
        $this->minutesBefore = $minutesBefore;
    }

    /**
     * The query to use for match list
     *
     * @param  string $date
     * @return string
     */
    protected function getMatchQuery()
    {
        $query = parent::getMatchQuery();

        $this->dateNowWithOffset = $this->dateNow->copy();

        $this->dateNowWithOffset->addMinutes($this->minutesBefore);
        $this->dateNowWithOffset->second = 0;

        // date formatted with time portion
        $dateTimeNow = $this->dateNowWithOffset->toTimeString();

        $rawSql = "TIME(kickoff)";
        $query->where(DB::raw($rawSql), $dateTimeNow);
        return $query;
    }

    /**
     * The query date to use for match list
     *
     * @return string
     */
    protected function getQueryDateAsString()
    {
        parent::getQueryDateAsString();

        return $this->dateNowWithOffset->toDateTimeString();
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
        $message = "Kickoff in {$this->minutesBefore} mins";
        if (!empty($broadcasters)) {
            $message .= ", Live on {$broadcasters}";
        }
        return $message;
    }
}
