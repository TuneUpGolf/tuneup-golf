<?php

namespace App\Actions;

use Exception;
use ExpoSDK\Expo;
use ExpoSDK\ExpoMessage;
use Lorisleiva\Actions\Concerns\AsAction;

class SendPushNotification
{

    use AsAction;

    public function handle($token = null, $title, $body)
    {
        try {
            if (isset($token)) {
                $message = (new ExpoMessage([
                    'title' => $title,
                    'body' => $body,
                ]))
                    ->setChannelId('default')
                    ->setBadge(0)
                    ->playSound()
                    ->setContentAvailable(true);

                (new Expo)->send($message)->to($token)->push();
            } else {
                throw new Exception('no expo token found');
            }
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
