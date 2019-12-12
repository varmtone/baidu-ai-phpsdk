<?php
/*
* Copyright (c) 2017 Baidu.com, Inc. All Rights Reserved
*
* Licensed under the Apache License, Version 2.0 (the "License"); you may not
* use this file except in compliance with the License. You may obtain a copy of
* the License at
*
* Http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
* WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
* License for the specific language governing permissions and limitations under
* the License.
*/
namespace Bdai;

use Bdai\http\Exception;

/**
 * 百度语音
 */
class AipSpeech extends Base {

    /**
     * asr请求接口地址
     *
     * @var string
     */
    private $asrUrl = 'http://vop.baidu.com/server_api';

    /**
     * tts请求接口地址
     *
     * @var string
     */
    private $ttsUrl = 'http://tsn.baidu.com/text2audio';

    /**
     * 判断认证是否有权限
     *
     * @param  array   $authObj
     * @return boolean
     */
    protected function isPermission($authObj)
    {
        return true;
    }

    /**
     * 处理请求参数
     *
     * @param string $url
     * @param array $params
     * @param array $data
     * @param array $headers
     */
    protected function proccessRequest($url, &$params, &$data, $headers){

        $token = isset($params['access_token']) ? $params['access_token'] : '';
        $data['cuid'] = md5($token);

        if($url === $this->asrUrl){
            $data['token'] = $token;
            $data = json_encode($data);
        }else{
            $data['tok'] = $token;
        }

        unset($params['access_token']);
    }

    /**
     * 格式化结果
     *
     * @param $content string
     * @return mixed
     */
    protected function proccessResult($content){
        $obj = json_decode($content, true);

        if($obj === null){
            $obj = array(
                'content' => $content
            );
        }

        return $obj;
    }

    /**
     * 语音识别
     *
     * @param binarybuffer $speech 建立包含语音内容的Buffer对象, 语音文件的格式，pcm 或者 wav 或者 amr。不区分大小写
     * @param string $format 语音文件的格式，pcm 或者 wav 或者 amr。不区分大小写。推荐pcm文件
     * @param array $options [
     *  'dev_pid'=>1537, //语种选择,1536:普通话(支持简单的英文识别和自定义词库),1737:英语（不支持自定义词库），1537：普通话(纯中文识别)，1936：普通话远场
     *  'cuid'=>'', //用户唯一标识，用来区分用户，填写机器 MAC 地址或 IMEI 码，长度为60以内
     * ]
     * @return array|mix|mixed
     * @throws Exception
     */
    public function asr($speech, $format, $options=[]){
        $data = [];
        if(!empty($speech)){
            $data['speech'] = base64_encode($speech);
            $data['len'] = strlen($speech);
        }
        $data['format'] = $format;
        $data['rate'] = 16000; //采样率，16000，固定值
        $data['channel'] = 1;
        $data = array_merge($data, $options);
        return $this->request($this->asrUrl, $data, []);
    }

    /**
     * 语音合成
     *
     * @param  string $text 合成的文本，使用UTF-8编码。小于2048个中文字或者英文数字。（文本在百度服务器内转换为GBK后，长度必须小于4096字节）
     * @param  string $lang 固定值zh。语言选择,目前只有中英文混合模式，填写固定值zh
     * @param  array $options [ //选填
     *  'ctp'=>1, //客户端类型选择，web端填写固定值1
     *  'spd'=>5, //语速，取值0-15，默认为5中语速
     *  'pit'=>5, //音调，取值0-15，默认为5中语调
     *  'vol'=>5, //音量，取值0-15，默认为5中音量
     *  'per'=>1, //度小宇=1，度小美=0，度逍遥=3，度丫丫=4, 精品：度博文=106，度小童=110，度小萌=111，度米朵=103，度小娇=5
     *  'aue'=>4 //3为mp3格式； 4为pcm-16k；5为pcm-8k；6为wav（默认，内容同pcm-16k）; 注意aue=4或者6是语音识别要求的格式，但是音频内容不是语音识别要求的自然人发音，所以识别效果会受影响。
     * ]
     *
     * @return binary 二进制语音数据 wav.pcm-16k
     * @throws Exception
     */
    public function tts($text, $lang='zh', $options=[]){
        $data = [];
        $data['tex'] = $text;
        $data['lan'] = $lang;
        $data['ctp'] = 1;
        $data['aue'] = 6;
        $data = array_merge($data, $options);

        $result = $this->request($this->ttsUrl, $data, []);
        if(isset($result['err_no'])){
            throw new Exception($result['err_msg'], '2'.$result['err_no'], $result);
        }
        return $result['content'];
    }

}
