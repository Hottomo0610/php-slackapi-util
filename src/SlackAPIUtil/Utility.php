<?php 
namespace SlackAPIUtil;

use DateTime;

/**
 * @author Tomohito Hotta
 * 
 * ※Event Subscriptionのエンドポイント認証について
 * 「 <?php echo file_get_contents('php://input'); 」
 * とだけ書いたPHPスクリプトファイルをエンドポイントにして認証すれば通ります。
 * 
 */

class Utility
{

    /**
     * 所属するワークスペース内に存在するpublicチャンネルの一覧を取得します。
     * 
     * $token (string)
     * 
     * @return
     */
    public function getChannelList($token) 
    {
        //SlackのWPワークスペース内にあるチャンネルの情報を取得

        $post_data = [
            'token' => $token
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => "https://slack.com/api/conversations.list?exclude_archived=true&limit=500&types=public_channel"
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
    
        $decodedData = json_decode($resp, true);
        $channel_list = $decodedData['channels'];
    
        $channel_list = array_values($channel_list);

        return $channel_list;
    }


    /**
     * 特定のチャンネルからメッセージを取得します。
     * 
     * $token, $channelId (string)
     * 
     * @return
     */
    public function getMessage(string $token, string $channelId) 
    {
        $post_data = [
            'token' => $token
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => "https://slack.com/api/conversations.history?channel=$channelId&inclusive=true"
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);

        $decodedData = json_decode($resp, true);
        return $decodedData;
    }


    /**
     * 特定の期間に特定のチャンネルに投稿されたメッセージを取得します。
     * 
     * $token, $channelId (string)
     * $before_time, $near_time (DateTime)
     * 
     * @return
     */
    public function getMessageInPeriod(string $token, string $channelId, DateTime $before_time, DateTime $near_time) 
    {
        //Slackの特定のチャンネルから、今日から〇日前までのメッセージを取得

        $post_data = [
            'token' => $token
        ];

        $before_time_U = $before_time -> format('U');
        $near_time_U = $near_time -> format('U');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => "https://slack.com/api/conversations.history?token=$token&channel=$channelId&inclusive=true&oldest=$before_time_U&latest=$near_time_U"
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
 
        $decodedData = json_decode($resp, true);
        return $decodedData;
    }


    /**
     * SlackAPIの設定画面で取得したWebHookURLを用いて、メッセージを特定のチャンネルに送信します。
     * 
     * $hookURL, $rawJson (string)
     * 
     * @return
     */
    public function sendMessage(string $hookURL, string $rawJson) 
    {
        //CURLでSlackに送信

        $ch = curl_init($hookURL);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $rawJson );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}