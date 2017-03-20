<?php
include __DIR__ . "/../bootstrap.php";
use App\book;

class spiderOfVocabulary
{
    protected $guzzle;
    const baseUrl = "https://www.vocabulary.com/dictionary/";
    const baseAudioUrl = "https://audio.vocab.com/1.0/us/";

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
            $saveFile = __DIR__ . '/vocabulary/' . $item->word . '.mp3';//保存在本地音频名称
            //检查音频本地是否存在
            if (file_exists($saveFile)) {
                //检查是否抓取过
                if ($item->audioOfVocabulary && $item->sourceOfVocabulary && $item->details) {
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
            preg_match_all('/<h1 class="dynamictext">([\s\S]*?)<a/', $html, $tempWord);
            if (!isset($tempWord[1][0])) {
                echo $item->word . ' error step one' . "\r\n";
                continue;
            }
            $tempWord = trim($tempWord[1][0]);
            if ($item->word != $tempWord) {
                echo 'error step two' . "\r\n";
                continue;
            }
            /**
             * 获取details word使用情景（超赞的）
             */
            preg_match_all('/blurb">[\w\W]*?class="short">([\w\W]*?)<\/p>/', $html, $details);
            if (!isset($details[1][0])) {
                echo 'notice   ' . $item->word . '   details not found' . "\r\n";
                $details = '';
            } else {
                $details = strip_tags($details[1][0]);
            }
            /**
             * 获取audioID
             */
            preg_match_all('/<a data-audio="([\w\W]*?)"/', $html, $autoId);
            if (!isset($autoId[1][0])) {
                echo 'error step three' . "\r\n";
                continue;
            }
            $autoId = $autoId[1][0];

            $autoUrl = self::baseAudioUrl . $autoId . '.mp3';//音频地址

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


            $item->details = $details;//单词情景
            $item->audioOfVocabulary = $autoUrl;//单词情景
            $item->sourceOfVocabulary = $wordUrl;//单词情景
            $item->save();
            continue;
        }
    }
}

$spiderOfVocabulary = new spiderOfVocabulary($guzzle);
$spiderOfVocabulary->handle();

