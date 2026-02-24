<?php namespace ProcessWire;

class SlackLogger {

    public function log($message) {

        if(stripos($message, 'warning') !== false) {
            $icon = ':warning:';
        }
        elseif(stripos($message, 'exception') !== false || stripos($message, 'error') !== false) {
            $icon = ':octagonal_sign:';
        }
        else {
            $icon = ':information_source:';
        }

        $this->message($icon . ' ' . $message);
    }

    protected $endpointUrl = 'https://slack.com/api/';
    protected $endpoint;
    protected $query = [];

    public function message($message) {

        $this->endpoint = 'chat.postMessage';

        $this->query = [
            "token" => TracyDebugger::getDataValue('slackAppOauthToken'),
            "text" => $message,
            "channel" => TracyDebugger::getDataValue('slackChannel'),
            "username" => 'TracyDebugger',
            "icon_emoji" => ':beetle:',
        ];

        return $this->send();
    }

    private function send() {
        $ch = curl_init($this->endpointUrl . $this->endpoint);
        $data = http_build_query($this->query);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        if (PHP_MAJOR_VERSION < 8) curl_close($ch);
        return $result;
    }


}
