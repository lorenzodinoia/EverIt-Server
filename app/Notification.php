<?php

namespace App;

class Notification {
    private const HOST = "https://fcm.googleapis.com/fcm/send";
    private const SERVER_KEY = "AAAAbMZ2fgk:APA91bHmrSje42gDZSsmQGauuv52zDZNVg7d1pKpvwWRIJgDehQkSS9L4tDXruJZVdPuu7PL816zYgx7w4HjrTzwGVo73wqq07Vjn_meUKlnD9teIz19JQg1hyCsH46CpVnB2FjIonM9";

    public const ACTION_RES_SHOW_ORDER_DETAIL = "everit.restaurateur.order.detail";
    public const ACTION_CUSTOMER_SHOW_ORDER_DETAIL = "everit.customer.order.detail";
    public const ACTION_RIDER_SHOW_PROPOSAL_DETAIL = "everit.rider.proposal.detail";

    private $deviceId;
    private $title;
    private $message;
    private $clickAction;
    private $data;
    private $curl;

    function __construct(string $deviceId, string $title, string $message, string $clickAction, $data) {
        $this->deviceId = $deviceId;
        $this->title = $title;
        $this->message = $message;
        $this->clickAction = $clickAction;
        $this->data = $data;
        $this->initCurl();
    }

    private function initCurl() {
        $message = array (
            "title" => $this->title,
            "body" => $this->message
        );

        if(isset($this->clickAction)) {
            $message['click_action'] = $this->clickAction;
        }

        $fields = array (
            "to" => $this->deviceId,
            "notification" => $message
        );

        if(isset($this->data)) {
            $fields['data'] = $this->data;
        }

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
