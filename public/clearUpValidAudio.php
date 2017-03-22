<?php
include __DIR__ . "/../bootstrap.php";
use App\book;

/**
 * 清理本地无效的audio
 * 基于大小判断小于10k则删除
 * 执行顺序 X
 * Class spiderOfCambridge
 */
class clearUpValidAudio
{
    protected $min = 2;//文件最小值
    protected $directors = [
        __DIR__ . '/cambridge/',
        __DIR__ . '/vocabulary/',
    ];

    public function handle()
    {
        $deleteCount = 0;//清理数量
        $total = 0;//文件总量
        $excuteCount = 0;//已执行总量
        $invalidCount = 0;//失败数量
        $invilids = [];
        foreach ($this->directors as $director) {

            $files = getDirFiles($director);//获取目录
            $total += count($files);
            foreach ($files as $k => $file) {
                $excuteCount++;

                $directorFile = $director . trim($file);//文件全路径
                if (!file_exists($directorFile)) {
                    $invalidCount++;
                    continue;
                }
                $fileSize = (filesize($directorFile) / 1024);
                if ($fileSize < $this->min) {
                    /**
                     * 大小小于最小值删除
                     */
                    $invilids[] = [
                        'file'     => $file,
                        'director' => $director,
                        'size'     => $fileSize,
                    ];
                    unlink($directorFile);
                    $deleteCount++;
                }
                $word = preg_replace('/\.[\w\W]*/', '', $file);

            }
        }
        $deleteCount = count($invilids);
        echo "\r\n 已执行到" . $excuteCount . ' / 总共查找到:' . $total . " / 不存在的文件个数:{$invalidCount} / 清理的文件个数{$deleteCount} \r\n";
        echo "\r\n以下是清理列表\r\n";
        print_r($invilids);
        echo "\r\n";
    }
}

$clearUpValidAudio = new clearUpValidAudio();
$clearUpValidAudio->handle();

