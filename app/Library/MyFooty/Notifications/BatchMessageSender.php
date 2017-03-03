<?php

namespace Library\MyFooty\Notifications;

use Illuminate\Support\Facades\Log;
use \Illuminate\Support\Collection as Collection;
use App\Models\NotificationLog;
use Davibennun\LaravelPushNotification\Facades\PushNotification;
use \Davibennun\LaravelPushNotification\App as PushApp;
use \Sly\NotificationPusher\Model\Message;
use Library\MyFooty\Notifications\MessageSender;

class BatchMessageSender extends MessageSender
{
    /**
     * Create a new MessageSender instance.
     *
     * @param  \Davibennun\LaravelPushNotification\App $pushApp
     * @return void
     */
    public function __construct(PushApp $pushApp)
    {
        parent::__construct($pushApp);
    }

    /**
     * Send a message to collention of users
     *
     * @param  \Sly\NotificationPusher\Model\Message $message
     * @param  \Illuminate\Support\Collection        $users
     * @return void
     */
    public function send(Message $message, Collection $users)
    {
        Log::info("BatchMessageSender",[
            'users_count' => $users->count(),
            'user_ids' => $users->implode('id', ','),
            'message_title' => $message->getOption('title'),
            'message_text' => $message->getText()
        ]);

        $devices = PushNotification::DeviceCollection([]);
        $logs = collect([]);
        $pushToDevices = false;

        if ($this->sendMode == self::SEND_MODE_APNS) {
            $pushToDevices = true;
        }

        $users->each(function ($user, $key) use ($message, $devices, $logs) {
            $device = $this->createDeviceForUser($user);

            try {
                $log = $this->createMatchNotificationLog($device, $message, $user);
            } catch(\Exception $iae) {
                $error = $iae->getMessage();
                Log::error("Can't create match notification log - {$error}", [
                    'user_id' => $user->id,
                    'message_title' => $message->getOption('title'),
                    'device_token' => $device->getToken()
                    ]);
                return;
            }

            $devices->add($device);
            $logs->push($log);
        });

        $sendingTo = $pushToDevices ? 'devices' : 'db';
        Log::info("Attempting send to {$sendingTo}",[
            'devices_count' => $devices->count(),
            'logs_count' => $logs->count(),
            'message_title' => $message->getOption('title'),
            'message_text' => $message->getText(),
            'user_ids' => $logs->implode('user_id', ','),
            'devices' => implode($devices->getTokens(), ',')
        ]);

        if ($pushToDevices) {
            $collection = $this->pushApp->to($devices)->send($message);

            // get response for each device push
            foreach ($collection->pushManager as $push) {
                $response = $push->getAdapter()->getResponse();
                Log::info('Apns response', [
                    'id' => $response->getId(),
                    'code' => $response->getCode()
                ]);
            }
        }

        // Save the log after sending apns
        $logs->each(function ($log, $key) {
            if ($log instanceof NotificationLog) {
                $log->save();
            }
        });

        return null;
    }
}
