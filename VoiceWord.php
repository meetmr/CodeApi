<?php
/*
 * 功能：百度语音识别
 * 版本：1.1
 * 时间：2019-09-04
 */
 
class VoiceWord{
    static $API_KEY = '';  // 百度apikey
    static $SECRET_KEY = '';
    static $CUID = "123456PHP";
    static $RATE = 16000;  // 固定值
    static $DEMO_CURL_VERBOSE = false;

    /**
     * 获取授权
     * @return string
     */
    public  function get0auth(){
        $auth_url = "http://openapi.baidu.com/oauth/2.0/token?grant_type=client_credentials&client_id=".self::$API_KEY."&client_secret=".self::$SECRET_KEY;
        $res = $this->curl($auth_url);
        $response = json_decode($res, true);
        if (!isset($response['access_token'])){
            return "ERROR TO OBTAIN TOKEN\n";
        }
        if (!isset($response['scope'])){
            return "ERROR TO OBTAIN scopes\n";
        }
        $token = $response['access_token'];
        return $token;
    }

    /**
     * 普通识别模式
     * @param  string   $file_path
     * @return array
     */
    public function serverApi($file_path){
        $audio = file_get_contents($file_path);
        $FORMAT = substr($file_path, -3); // 文件后缀 pcm/wav/amr 格式 极速版额外支持m4a 格式

        $ASR_URL = "http://vop.baidu.com/server_api";
        # 根据文档填写PID，选择语言及识别模型
        $DEV_PID = 1537; //  1537 表示识别普通话，使用输入法模型。1536表示识别普通话，使用搜索模型
        $SCOPE = 'audio_voice_assistant_get'; // 有此scope表示有语音识别普通版能力，没有请在网页里开通语音识别能力

        $url = $ASR_URL . "?cuid=".self::$CUID. "&token=" . $this->get0auth() . "&dev_pid=" . $DEV_PID;

        /*测试自训练平台需要开启下面的注释*/
        //$url = $ASR_URL . "?cuid=".$CUID. "&token=" . $token . "&dev_pid=" . $DEV_PID . "&lm_id=" . $LM_ID;
        $headers[] = "Content-Length: ".strlen($audio);
        $headers[] = "Content-Type: audio/$FORMAT; rate=".self::$RATE;
        $res = $this->serverCurl1($url,$headers,$audio);
        $response = json_decode($res, true);
        return $response;
    }

    /**
     * 极速版
     * @param  string   $file_path
     * @return array
     */
    public function proApi($file_path){
        $audio = file_get_contents($file_path);
        $FORMAT = substr($file_path, -3); // 文件后缀 pcm/wav/amr 格式 极速版额外支持m4a 格式

        $ASR_URL = "http://vop.baidu.com/pro_api";
        $DEV_PID = 80001;
        $SCOPE = 'brain_enhanced_asr';  // 有此scope表示有极速版能力，没有请在网页里开通极速版
        $SCOPE = false; // 部分历史应用没有加入scope，设为false忽略检查
        $url = $ASR_URL . "?cuid=".self::$CUID. "&token=" . $this->get0auth() . "&dev_pid=" . $DEV_PID;

        /*测试自训练平台需要开启下面的注释*/
        //$url = $ASR_URL . "?cuid=".$CUID. "&token=" . $token . "&dev_pid=" . $DEV_PID . "&lm_id=" . $LM_ID;
        $headers[] = "Content-Length: ".strlen($audio);
        $headers[] = "Content-Type: audio/$FORMAT; rate=".self::$RATE;
        $res = $this->serverCurl1($url,$headers,$audio);
        $response = json_decode($res, true);
        return $response;
    }

    /**
     * 获取0authCurl
     * @return string
     */
    public function curl($url,$headers = []){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //信任任何证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // 检查证书中是否设置域名,0不验证
        curl_setopt($ch, CURLOPT_VERBOSE, self::$DEMO_CURL_VERBOSE);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    /**
     * 识别请求
     * @param  string   $url
     * @param  array   $headers
     * @param  string   $audio
     * @return string
     */
    public function serverCurl1($url,$headers,$audio){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); // 识别时长不超过原始音频
        curl_setopt($ch, CURLOPT_POSTFIELDS, $audio);
        curl_setopt($ch, CURLOPT_VERBOSE, self::$DEMO_CURL_VERBOSE);
        $res = curl_exec($ch);
        return $res;
    }
}

$voiceWord = new VoiceWord();
$content = $voiceWord->proApi('voicedictation.pcm');
print_r($content);
?>
