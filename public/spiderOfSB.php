<?php
include __DIR__ . "/../bootstrap.php";
use App\book;

class spiderOfSB
{
    protected $guzzle;
    const menuUrl = 'https://www.shanbay.com/wordbook/94129/';
    const baseUrl = "https://www.shanbay.com/";

    public function __construct($guzzle)
    {
        $this->guzzle = $guzzle;
    }

    public function handle()
    {
        $params = getopt('t:u:');
        $count = 0;
        $title = isset($params['t']) ? $params['t'] : '赖世雄美语入门';
        $menuUrl = isset($params['u']) ? $params['u'] : self::menuUrl;
        $response = $this->guzzle->get($menuUrl, ['timeout' => 60]);
        if ($response->getStatusCode() != 200) {
            exit('error step one');
        }
        $html = $response->getBody()->getContents();
        /**
         * 获取目录
         */
        preg_match_all('/-name[\w\W]*?<a href="([\w\W]*?)">([\w\W]*?)<\/a>/', $html, $menus);
        if (!isset($menus[1][0])) {
            exit('error step two\\r\\n');
        }
        $lesson = $menus[2];
        $menus = $menus[1];
        /**
         * 获取列表链接
         */
        foreach ($menus as $k => $menu) {
            $listBaseUrl = self::baseUrl . $menu;
            $listUrl = $listBaseUrl . '?page=1';
            $response = $this->guzzle->get($listUrl, ['timeout' => 60, 'http_errors' => false]);
            if ($response->getStatusCode() != 200) {
                echo $listUrl . '链接异常' . "\r\n";
                continue;
            }
            $html = $response->getBody()->getContents();
            /**
             * 获取第一页word+translate
             */
            preg_match_all('/<tr class="row">[\w\W]*?span2"><strong>([\w\W]*?)<\/strong>[\w\W]*?">([\w\W]*?)<\/td>[\w\W]*?<\/tr>/', $html, $tempList);
            if (!isset($tempList[1][0])) {
                echo '单词列表为空' . "\r\n";
                continue;
            }
            $words = $tempList[1];
            $translation = $tempList[2];
            /**
             * 获取current list num
             */
            preg_match_all('/num-vocab">(\d*?)<\/span>/', $html, $wordNum);
            $wordNum = isset($wordNum[1][0]) ? $wordNum[1][0] : 0;
            $pagination = ceil($wordNum / 20);
            for ($i = 2; $i <= $pagination; $i++) {
                $listUrl = $listBaseUrl . '?page=' . $i;
                $response = $this->guzzle->get($listUrl, ['timeout' => 60]);
                if ($response->getStatusCode() != 200) {
                    echo $listUrl . '链接异常' . "\r\n";
                    continue;
                }
                $html = $response->getBody()->getContents();

                /**
                 * 获取后几页word + 翻译
                 */
                preg_match_all('/<tr class="row">[\w\W]*?span2"><strong>([\w\W]*?)<\/strong>[\w\W]*?">([\w\W]*?)<\/td>[\w\W]*?<\/tr>/', $html, $tempList);
                if (!isset($tempList[1][0])) {
                    echo '单词列表为空' . "\r\n";
                    continue;
                }
                $words = array_merge($words, $tempList[1]);
                $translation = array_merge($translation, $tempList[2]);
            }
            foreach ($words as $wk => $word) {
                $data = [
                    'word'      => $word,
                    'title'     => $title,
                    'lesson'    => $lesson[$k],
                    'translate' => $translation[$wk],
                ];
                book::create($data);
                $count++;//统计数量
            }
            continue;
        }
        echo '执行完毕，一共' . $count . '条数据';
    }

}

$spiderOfSB = new spiderOfSB($guzzle);
$spiderOfSB->handle();

