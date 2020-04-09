<?php

namespace App;

class Notification {
    private const HOST = "https://fcm.googleapis.com/fcm/send";
    private const SERVER_KEY = "AAAAbMZ2fgk:APA91bHmrSje42gDZSsmQGauuv52zDZNVg7d1pKpvwWRIJgDehQkSS9L4tDXruJZVdPuu7PL816zYgx7w4HjrTzwGVo73wqq07Vjn_meUKlnD9teIz19JQg1hyCsH46CpVnB2FjIonM9";
    private $deviceId;
    private $title;
    private $message;
    private $curl;

    function __construct($deviceId, $title, $message) {
        $this->deviceId = $deviceId;
        $this->title = $title;
        $this->message = $message;
        $this->initCurl();
    }

    private function initCurl() {
        $fields = array (
            'registration_ids' => array (
                    $this->deviceId
            ),
            'notification' => array (
                    "title" => $this->title,
                    "body" => $this->message
            )
        );
        
        $headers = array(
            'Content-Type:application/json',
            'Authorization:key='.Notification::SERVER_KEY
        );
                    
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_URL, Notification::HOST);
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($fields));
    }

    public function send() {
        $result = curl_exec($this->curl);
        curl_close($this->curl);
        return json_decode($result, true);
    }
}

?>