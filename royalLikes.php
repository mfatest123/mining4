<?php

class RoyalLikes extends Random {
    protected $key = "a3c8fb21c7340792a02d7d6967d2c04c4c66a7fecc1b157bca1faad882c7bc6a";
    protected $base = "http://52.8.205.19/";

    protected $session = '';
    
    protected $igis = false;
    
    public $lastResponse;
    
    public function __cosntruct(){
        return $this;
    }
    public function setIgis($id){
        $this->igis = $id;
        return $this;
    }
    public function addOrderFollowers($package, $username, $startfollowers){
        switch($package){
            case 1:
                $package = "com.ty.vl.follower1";
            break;
            case 2:
                $package = "com.ty.vl.follower2";
            break;
            case 3:
                $package = "com.ty.vl.follower3";
            break;
            case 4:
                $package = "com.ty.vl.follower4";
            break;
            case 5:
                $package = "com.ty.vl.follower5";
            break;
            case 6:
                $package = "com.ty.vl.follower6";
            break;
            default:
                return false;
        }
        $content = '{"avatarUrl":"http://scontent-sit4-1.cdninstagram.com/t51.2885-19/11906329_960233084022564_1448528159_a.jpg","goodsId":"'.$package.'","userName":"'.$username.'","startAt":'.$startfollowers.'}';
        $http = $this->response($this->http("user/".$this->igis."/getFollowers/".$this->session, true, $content));
        return $this;
    }
    public function getFollowersList($type = 0) { // 1 Followers 0 Likes
        $http = $this->response($this->http("user/".$this->igis."/getBoard/".$type."/" . $this->session));
        if(empty($http['boardList']) OR count($http['boardList']) == 0){
            return [];
        } else {
            return $http['boardList'];
        }
    }
    public function followAction($orderid){
        $content = '{"actionToken":"'.$this->getactionKey($orderid).'","action":0,"orderId":'.$orderid.'}';
        $http = $this->response($this->http("user/".$this->igis."/trackAction/".$this->session, true, $content));
        return $this;
    }
    public function login($igs, $igi){
        $this->igis = $igi; // Setting session id
        $this->session = hash("sha256", time() . rand() . $igi);
        $data = '{"deviceId":"'.$this->device().'","imei":"","parseKey":"'.hash("sha256", $igi).'.bebaskaliha","platform":"0","sessionToken":"'.$this->session.'","viPassword":"","viUserId":"'.$igi.'","viUserName":"'.$igs.'"}';
        $http = $this->response($this->http("user/login", true, $data));
        return $this;
    }
    protected function getactionKey($orderid){
        return strtoupper(md5("" . $orderid . round(microtime(true) * 1000)));
    }
    protected function response($response){
        if(!empty($response) AND $response !== NULL AND $response !== FALSE AND is_array($response)){
            if(!empty($response['status']) AND $response['status']['status'] == 200){
                return $response['data'];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    public function http($path, $post = false, $postdata = false){
        $random=new Random();
        $userAgent="(".$random->alphabet(20)."+/19/".$random->numeric(1).".".$random->numeric(1).".".$random->numeric(1).")";
        $opts = array('http' =>
            array(
                'header'  =>    "Content-Type: application/json; charset=utf-8\n" . 
                                "appVersion: 12\n" .
                                "systemVersion: royallikesandroid/ $userAgent \n" .
                                "User-Agent: royallikes 12 $userAgent\n" . 
                                "appName: royallikesandroid\n" .
                                "deviceModel: $userAgent\n" .
                                "timeZone: Asia/Jakarta\n" . 
                                "Host: instalike.socialmarkets.info",
            )
        );
        if($post){
            $opts['http']['method'] = 'POST';
            $opts['http']['content'] = $postdata;
            $opts['http']['header'] .= "\nSignature: ".$this->Signature($postdata)."\n" .
                                        "Content-Length: " . strlen($postdata);
        } else {
            $opts['http']['method'] = 'GET';
        }
        $context  = stream_context_create($opts);        

        $result = @file_get_contents($this->base . $path, false, $context);
        if($result == FALSE){
            $error = error_get_last();
        }
        $res = json_decode($result, true);
        $this->lastResponse = $res;
        return $res;
    }
    protected function device(){
        $i = 0;
        $tmp = mt_rand(1,9);
        do {
            $tmp .= mt_rand(0, 9);
        } while(++$i < 14);
        return $tmp;
    }
    protected function deviceID($type){
        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
          mt_rand(0, 0xffff), mt_rand(0, 0xffff),
          mt_rand(0, 0xffff),
          mt_rand(0, 0x0fff) | 0x4000,
          mt_rand(0, 0x3fff) | 0x8000,
          mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
        return $type ? $uuid : str_replace('-', '', $uuid);
    }
    protected function Signature($data){
        return hash_hmac("sha256", $data, $this->key);
    }
}