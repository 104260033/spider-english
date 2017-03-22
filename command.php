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
function cUrl($url, $header = null, $data = null)
{
    //初始化curl
    $curl = curl_init();
    //设置cURL传输选项

    if (!is_array($header)) {
        $header[] = 'user-agent:Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.63 Safari/537.36';
    }
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);


    if (!empty($data)) {//post方式
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }

    //获取采集结果
    $output = curl_exec($curl);
    $info = curl_getinfo($curl);//获取文件头信息

    //关闭cURL链接
    curl_close($curl);

    return [
        'output' => $output,
        'info'   => $info,
    ];
}

/**
 * 获取指定目录下文件列表
 * @param $dir
 * @return array|int
 */
function getDirFiles($dir)
{

    if (!is_dir($dir)) {
        return 1;
    }
    if (false === ($dh = opendir($dir))) {
        return 2;
    }
    $list = [];
    while (false !== ($file = readdir($dh))) {
        if ($file == '.' || $file == '..') {
            continue;
        }
        $list [] = $file;
    }

    closedir($dh);

    return $list;
}





















