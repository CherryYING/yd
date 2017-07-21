<?php
class redgirl
{   
    //计算星座$birth='1990-03-14'
    public function star($birthday){
        $star=array(
            1=>array('name'=>'白羊座','begin_m'=>'03','begin_d'=>'21','end_m'=>'04','end_d'=>'20'),
            2=>array('name'=>'金牛座','begin_m'=>'04','begin_d'=>'21','end_m'=>'05','end_d'=>'21'),
            3=>array('name'=>'双子座','begin_m'=>'05','begin_d'=>'21','end_m'=>'06','end_d'=>'22'),
            4=>array('name'=>'巨蟹座','begin_m'=>'06','begin_d'=>'22','end_m'=>'07','end_d'=>'22'),
            5=>array('name'=>'狮子座','begin_m'=>'07','begin_d'=>'23','end_m'=>'08','end_d'=>'23'),
            6=>array('name'=>'处女座','begin_m'=>'08','begin_d'=>'24','end_m'=>'09','end_d'=>'22'),
            7=>array('name'=>'天秤座','begin_m'=>'09','begin_d'=>'23','end_m'=>'10','end_d'=>'23'),
            8=>array('name'=>'天蝎座','begin_m'=>'10','begin_d'=>'24','end_m'=>'11','end_d'=>'22'),
            9=>array('name'=>'射手座','begin_m'=>'11','begin_d'=>'23','end_m'=>'12','end_d'=>'21'),
            10=>array('name'=>'摩羯座','begin_m'=>'12','begin_d'=>'22','end_m'=>'01','end_d'=>'20'),
            11=>array('name'=>'水瓶座','begin_m'=>'01','begin_d'=>'21','end_m'=>'02','end_d'=>'19'),
            12=>array('name'=>'双鱼座','begin_m'=>'02','begin_d'=>'20','end_m'=>'03','end_d'=>'22'),
        );
        $birth=explode('-',$birthday);
        foreach($star as $key=>$val){
            if($birth[1]==$val['begin_m']){
                if($birth[2]>=$val['begin_d']){
                    $starkey=$key;
                    continue;
                }
            }elseif($birth[1]==$val['end_m']){
                if($birth[2]<=$val['end_d']){
                    $starkey=$key;
                    continue;
                }
            }
        }
        return $star[$starkey]['name'];
    }
    //体型计算[体型身高／体重]
    public function bodyType($str){
        $data=explode('/',str_replace('体型','',$str));
        if (count($data)!==2&&is_array($data))
            return "主人，正确的查询方式是:体型身高／体重；例如：体型170／65";
        $height  = $data[1] / 100;
        $weight  = $data[2];
        $Broca   = ($height * 100 - 80) * 0.7;
        $kaluli  = 66 + 13.7 * $weight + 5 * $height * 100 - 6.8 * 25;
        $chao    = $weight - $Broca;
        $zhibiao = $chao * 0.1;
        $res     = round($weight / ($height * $height), 1);
        if ($res < 18.5) {
            $info = '您的体形属于骨感型，需要增加体重' . $chao . '公斤哦!';
            $pic  = 1;
        } elseif ($res < 24) {
            $info = '您的体形属于圆滑型的身材，需要减少体重' . $chao . '公斤哦!';
        } elseif ($res > 24) {
            $info = '您的体形属于肥胖型，需要减少体重' . $chao . '公斤哦!';
        } elseif ($res > 28) {
            $info = '您的体形属于严重肥胖，请加强锻炼，或者使用我们推荐的减肥方案进行减肥';
        }
        return $info;
    }
    //天气查询
    public function tianqi($n)
    {
        $cityName = str_replace('天气','',$n);
        if ($cityName == "" || (strstr($cityName, "+"))){
            return array('type'=>'text','msg'=>'查询天气，请发送天气+城市，例如"天气深圳"');
    	}
        $url="http://api.map.baidu.com/telematics/v3/weather?location=".urlencode($cityName)."&output=json&ak=HjMeDy7YsQORjc0EjQs8TfgG";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($output, true);
        if ($result["error"] != 0){
                $reply=$result["status"];
                return array('type'=>'text','msg'=>'这里是城市天气预报，请输入“天气城市”或“天气县区”');
        }else{
            $weather = $result["results"][0];
            $url='http://'.$_SERVER['HTTP_HOST'].'/index.php?g=Wx&m=Wweb&a=weather&cityName='.$cityName;
            return array(array(
                        'Title'=>'今天'.$weather["weather_data"][0]["date"], 
                        'Description'=>$cityName.'今天【天气】：'.$weather["weather_data"][0]["weather"].'    【风力】：'.$weather["weather_data"][0]["wind"].'    【温度】：'.$weather["weather_data"][0]["temperature"].'明天【天气】：'.$weather["weather_data"][1]["weather"].'    【风力】：'.$weather["weather_data"][1]["wind"].'    【温度】：'.$weather["weather_data"][1]["temperature"],
                        'PicUrl'=>$weather["weather_data"][0]["dayPictureUrl"],
                        //'Url'=>$url
                        )
                );
        }
        
    }
     //手机归属地查询
    public function shouji($n){
        $name = substr($n,6);
	if ($name == ""){
            return "发送手机+手机号，例如'手机13888888888'";
    	}
        $str  = "http://apix.sinaapp.com/mobilephone/?appkey=trialuser&number=".$name;
        /*$json = json_decode(file_get_contents($str));
        $reply = urldecode($json->content);
        $reply   = str_replace('{br}', "\n", $reply);*/
        $reply=file_get_contents($str);
        $reply=str_replace("\\n", '　', $reply);
        $reply=str_replace("\"", '　', $reply);
        return $reply;
    }
    //根据身份证号码查询相关信息
    public function shenfenzheng($n)
    {
        $n = implode('', $n);
        if (count($n) > 1) {
            $this->error_msg($n);
            return false;
        }
        ;
        $str1     = file_get_contents('http://www.youdao.com/smartresult-xml/search.s?jsFlag=true&type=id&q=' . $n);
        $array    = explode(':', $str1);
        $array[2] = rtrim($array[4], ",'gender'");
        $str      = trim($array[3], ",'birthday'");
        if ($str !== iconv('UTF-8', 'UTF-8', iconv('UTF-8', 'UTF-8', $str)))
            $str = iconv('GBK', 'UTF-8', $str);
        $str = '【身份证】 ' . $n . "\n" . '【地址】' . $str . "\n 【该身份证主人的生日】" . str_replace("'", '', $array[2]);
        return $str;
    }
}