<?php


namespace ANDS\Util;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Predis\Client as PredisClient;

class NotifyUtil
{
    public static function notify($channel, $content)
    {
        try {
            $redisUrl = Config::get("app.redis_url");
            $redis = new PredisClient($redisUrl);
            $redis->ping();
            return $redis->publish($channel, $content);
        } catch (\Exception $e) {
            // log notify error
            return false;
        }
    }

    public static function sendSlackMessage($text, $data_source_id, $message_type='INFO')
    {
        $log_levels = ["ERROR"=>100, "INFO"=>50, "DEBUG"=>10];
        $log_level = Config::get("slack.log_level");
        // send messages only greater than the default log_level
        if($log_levels[$message_type] < $log_levels[$log_level])
            return;
        $colour = "#00AA00";
        if ($message_type === 'ERROR')
            $colour = "#AA0000";
        if ($message_type === 'DEBUG')
            $colour = "#0000AA";

        $web_hook_url = Config::get("slack.web_hook_url");
        $channel_id = Config::get("slack.channel_id");

        $environment_name = Config::get("app.environment_name");
        $datasource_view_url = Config::get("app.default_base_url") . 'registry/data_source/#!/view/'. $data_source_id;
        $data = [
        "channel"=> $channel_id,
            "text" => $environment_name . " "  . $message_type,
            "attachments" =>[
                ["text" => $text, "color" => $colour],
                ["type" => "mrkdwn", "text" =>  "View the <".$datasource_view_url. "|DataSource> for more details", "color" => $colour]]
        ];
        try {
            $client = new Client();
            return $client->post($web_hook_url, [
                "headers" => ['Content-Type' => 'application/json'],
                "body" => json_encode($data)
            ]);
        } catch (ClientException $e) {
            return null;
        }
    }
}