<?php
include __DIR__ . "/../bootstrap.php";
use App\book;

/**
 * 采集剑桥的音标 和 音频 还有 翻译  执行顺序 X
 * Class spiderOfCambridge
 */
class spiderOfCambridge
{
    protected $guzzle;
    const baseUrl = "http://dictionary.cambridge.org/zhs/%E8%AF%8D%E5%85%B8/%E8%8B%B1%E8%AF%AD-%E6%B1%89%E8%AF%AD-%E7%AE%80%E4%BD%93/";

    public function __construct($guzzle)
    {
        $this->guzzle = $guzzle;
    }

    public function handle()
    {
        $count = 0;//统计总量用
        $params = getopt('t:u:');
        $title = isset($params['t']) ? $params['t'] : '扇贝循环单词书·四级核心词汇';
        $items = book::where('title', $title)->get();
        $total = $items->count();
        foreach ($items as $k => $item) {

            echo "\r\n" . $count . '/' . $total . "\r\n";
            $count++;
            /**
             * 开始前的准备
             */
            $saveFile = __DIR__ . '/cambridge/' . $item->word . '.mp3';//保存在本地音频名称
            //检查音频本地是否存在
            if (file_exists($saveFile)) {
                //检查是否抓取过
                if ($item->audioOfCambridge && $item->sourceOfCambridge && $item->phoneticOfUs) {
                    /**
                     * 因为已经抓去过，并且本地音频存在，所以跳过，如果需要更新，修改这里
                     */
                    echo 'local ' . $item->word . ' exists so continue' . "\r\n";
                    continue;
                }
            }
            /**
             * 获取目标页面
             */
            $wordUrl = self::baseUrl . $item->word;
//            $response = $this->guzzle->get($wordUrl, [
//                'timeout' => 60,
//                'http_errors' => false,
//                'curl' => [
//                    //CURLOPT_SSLVERSION => 3
//                    //CURLOPT_SSLVERSION => CURL_SSLVERSION_DEFAULT,
//                    CURLOPT_SSL_VERIFYPEER => false
//                ],
//            ]);
//            if ($response->getStatusCode() != 200) {
//                echo $wordUrl . '链接异常' . "\r\n";
//                continue;
//            }
//            $html = $response->getBody()->getContents();

            $html = cUrl($wordUrl);
            /**
             * 获取word用于比对和查询的是否一致 vocabulary 有这个问题
             */
            preg_match_all('/di-body">[\w\W]*?headword">[\w\W]*?class="hw">([\w\W]*?)<\/span><\/span>/', $html, $tempWord);
            if (!isset($tempWord[1][0])) {
                echo 'error step one' . "\r\n";
                continue;
            }
            $tempWord = trim($tempWord[1][0]);
            if ($item->word != $tempWord) {
                echo 'error step two' . "\r\n";
                continue;
            }
            /**
             * 获取音标
             */
            preg_match_all('/class="pron">[\w\W]*?class="ipa">([\w\W]*?)\/[\w\W]*?<\/span>/', $html, $phonetic);
            if (!isset($phonetic[1][0])) {
                echo $item->word . 'word phonetic not found so continue' . "\r\n";
                continue;
            } else {
                $phoneticOfUk = preg_replace('/<span[\w\W]*?>/', '^', $phonetic[1][0]);
                /**
                 * 检测英语音标是否存在，不存在则替换美音
                 */
                $phoneticOfUs =
                    isset($phonetic[1][1]) ?
                        preg_replace('/<span[\w\W]*?>/', '^', $phonetic[1][1]) :
                        $phoneticOfUk;
            }

            /**
             * 获取翻译
             */
            preg_match_all('/"entry-body">[\w\W]*?class="def-body">[\w\W]*?lang="zh-Hant">([\w\W]*?)<\/span>/', $html, $translate);
            if (!isset($translate[1][0])) {
                $translateOfCambridge = '';
            } else {
                $translateOfCambridge = trim($translate[1][0]);
            }
            /**
             * 获取audioID
             */
            preg_match_all('/class="entry-body">[\w\W]*?title[\w\W]*?data-src-mp3="([\w\W]*?)"/', $html, $autoUrl);
            if (!isset($autoUrl[1][0])) {
                echo 'error step three' . "\r\n";
                continue;
            }
            $autoUrl = $autoUrl[1][0];//音频地址

            //检查音频本地是否存在
            if (file_exists($saveFile)) {
                echo 'local ' . $item->word . ' audio exists' . "\r\n";
            } else {
                /**
                 * 获取音频
                 */
                $audio = cUrl($autoUrl);
                /**
                 * 保存音频
                 */
                $save = file_put_contents($saveFile, $audio);

                if ($save === false) {
                    echo 'error step four';
                    continue;
                }
            }


            $item->phoneticOfUk = $phoneticOfUk;//单词情景
            $item->phoneticOfUs = $phoneticOfUs;//单词情景
            $item->translateOfCambridge = $translateOfCambridge;//翻译
            $item->audioOfCambridge = $autoUrl;//单词情景
            $item->sourceOfCambridge = $wordUrl;//单词情景
            $item->save();
            continue;
        }
    }
}

$spiderOfCambridge = new spiderOfCambridge($guzzle);
$spiderOfCambridge->handle();

