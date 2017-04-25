<?php

namespace Library\MyFooty\PushNotifications\MessageSender;

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
use \Davibennun\LaravelPushNotification\App as PushApp;
use \Sly\NotificationPusher\Model\Device;
use \Sly\NotificationPusher\Model\Message;
use ZendService\Apple\Apns\Message as ZendMessage;
use CustomLog as CLog;

class MessageSender
{
    /**
     * The send mode apns setting.
     *
     * @var string
     */
    const SEND_MODE_APNS = 'apns';

    /**
     * The send mode db setting.
     *
     * @var string
     */
    const SEND_MODE_DB = 'db';

    /**
     * The send mode variable
     *
     * @var string
     */
    protected $sendMode = self::SEND_MODE_APNS;

    /**
     * The name and signature of the console command.
     *
     * @var \Davibennun\LaravelPushNotification\App
     */
    protected $pushApp;

    /**
     * Create a new MessageSender instance.
     *
     * @param  \Davibennun\LaravelPushNotification\App $pushApp
     * @return void
     */
    public function __construct(PushApp $pushApp)
    {
        $this->pushApp = $pushApp;
    }

    /**
     * Create the Device object
     *
     * @param  \App\Models\User $user
     * @return \Sly\NotificationPusher\Model\Device
     */
    protected function createDeviceForUser(User $user)
    {
        return PushNotification::Device($user->apns_token, array('badge' => 1));
    }

    /**
     * Create NotificationLog for the Match
     *
     * @param  \Sly\NotificationPusher\Model\Device  $device
     * @param  \Sly\NotificationPusher\Model\Message $message
     * @param  \App\Models\User $user
     * @return \App\Models\NotificationLog
     */
    protected function createMatchNotificationLog(
        Device $device,
        Message $message,
        User $user
    ) {
        $adapter = $this->pushApp->adapter;
        $serviceMessage = $adapter->getServiceMessageFromOrigin($device, $message);

        $messageDeviceToken = $serviceMessage->getToken();
        $isProductionEnvironment = $adapter->isProductionEnvironment();
        $payload = $serviceMessage->getPayload();

        //var_dump($isProductionEnvironment);
        //var_dump($messageDeviceToken);

        if (defined('JSON_UNESCAPED_UNICODE')) {
            $payloadJSON = json_encode($payload, JSON_UNESCAPED_UNICODE);
        } else {
            $payloadJSON = JsonEncoder::encode($payload);
        }

        $token = $messageDeviceToken;
        $body = $payloadJSON;

        $production = $isProductionEnvironment ? 1 : 0;
        return $this->createNotificationLog($token, $body, $user->id, $production);
    }

    /**
     * Create NotificationLog object
     *
     * @param  string $token device token
     * @param  string $body
     * @param  string $userId
     * @param  int    $production
     * @return \App\Models\NotificationLog
     */
    protected function createNotificationLog($token, $body, $userId, $production)
    {
        $log = new NotificationLog;

        $log->apns_token = $token;
        $log->body = $body;
        $log->user_id = $userId;
        $log->production = $production;
        $log->save();
        return $log;
    }

    /**
     * Get the send mode .
     *
     * @return string
     */
    public function getSendMode()
    {
        return $this->sendMode;
    }

    /**
     * Set the visible attributes for the model.
     *
     * @param  string $sendMode
     * @return void
     */
    public function setSendMode($sendMode)
    {
        $this->sendMode = $sendMode;
    }
}
