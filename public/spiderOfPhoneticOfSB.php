<?php
include __DIR__ . "/../bootstrap.php";
use App\book;

/**
 * 补充扇贝网的音标和官方翻译 执行顺序 X
 * Class spiderOfPhoneticOfSB
 */
class spiderOfPhoneticOfSB
{
    protected $guzzle;
    const baseUrl = "https://www.shanbay.com/api/v1/bdc/search/";

    public function __construct($guzzle)
    {
        $this->guzzle = $guzzle;
    }

    public function handle()
    {
        $count = 1;
        $params = getopt('t:u:');
        $title = isset($params['t']) ? $params['t'] : '赖世雄美语入门';
//        $items = book::where('title', $title)
//            ->orWhere('phoneticOfSbForUk', '')
        $items = book::orWhere('phoneticOfSbForUk', '')
            ->orWhere('phoneticOfSbForUs', '')
            ->orWhere('translateOfSb', '')
            ->get();
        $total = $items->count();
        foreach ($items as $k => $item) {
            if (($k % 20) == 1) {
                echo '暂停两秒';
                sleep(2);
            }
//            sleep(1);
            echo "\r\n" . $count . '/' . $total . "\r\n";
            $count++;

            $url = self::baseUrl . '?version=2&word=' . $item->word . '&_=' . (time() * 1000);
            $response = $this->guzzle->get($url, [
                'timeout'     => 25,
                'http_errors' => false,
            ]);
            if ($response->getStatusCode() != 200) {
                echo 'error step one';
                continue;
            }
            $content = $response->getBody()->getContents();
            $content = json_decode($content, true);
            if ($content === null) {
                echo 'error step two   json_decode($content) = null';
                continue;
            }
            $phoneticOfSbForUk = isset($content['data']['pronunciations']['uk']) ? $content['data']['pronunciations']['uk'] : '';
            $phoneticOfSbForUs = isset($content['data']['pronunciations']['us']) ? $content['data']['pronunciations']['us'] : '';
            /**
             * 组合官方翻译
             */
            $translateOfSb = '';
//            var_dump($content['data']);exit;
            if (isset($content['data']['definitions']['cn'])) {
                $list = $content['data']['definitions']['cn'];
                foreach ($list as $translate) {
                    $translateOfSb .= $translate['pos'] . ' ' . $translate['defn'] . ' ';
                }
            }
            $item->phoneticOfSbForUk = $phoneticOfSbForUk;//扇贝网的音标
            $item->phoneticOfSbForUs = $phoneticOfSbForUs;//扇贝网的音标
            $item->translateOfSb = $translateOfSb;//扇贝网忘得官方翻译
            $item->save();
            continue;
        }


    }

}

$spiderOfPhoneticOfSB = new spiderOfPhoneticOfSB($guzzle);
$spiderOfPhoneticOfSB->handle();

