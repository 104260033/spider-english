<?php
include __DIR__ . "/../bootstrap.php";
use App\book;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Remote\DesiredCapabilities;

/**
 *
 * Class spiderSelenium
 */
class spiderSelenium
{


    public function __construct($guzzle)
    {
        $this->guzzle = $guzzle;
    }

    public function handle()
    {


        $waitSeconds = 15;
        $host = 'http://localhost:4444/wd/hub'; // this is the default
        $desired_capabilities = DesiredCapabilities::chrome();
        $desired_capabilities->setCapability('acceptSslCerts', false);
        $driver = RemoteWebDriver::create($host, $desired_capabilities);


        $driver->get('https://www.flipkart.com/mens-clothing/pr?sid=2oq,s9b&otracker=categorytree');
        $html = $driver->getPageSource();
        preg_match_all('/id="pagination"([\w\W]*?)<\/ul>[\w\W]*?<\/div>[\w\W]*?<\/div>/', $html, $content);
        var_dump($content);
        $driver->quit();
    }
}

$spiderSelenium = new spiderSelenium($guzzle);
$spiderSelenium->handle();

