<?php
/**
 * [cUrl cURL(支持HTTP/HTTPS，GET/POST)]
 * @version [V1.0版本]
 * @date 2016-05-30
 * @param  [type] $url     [请求地址]
 * @param  [Array] $header [HTTP Request headers  例如 ['App-Key:'.$app_key,]]
 * @param  [Array] $data   [参数数据]
 * @return [type]          [如果服务器返回xml则返回xml，不然则返回json]
 */
function cUrl($url,$header=null, $data = null){
    //初始化curl
    $curl = curl_init();
    //设置cURL传输选项

    if(is_array($header)){

        curl_setopt($curl, CURLOPT_HTTPHEADER  , $header);
    }

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);


    if (!empty($data)){//post方式
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }

    //获取采集结果
    $output = curl_exec($curl);

    //关闭cURL链接
    curl_close($curl);

    return $output;
}