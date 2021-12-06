<?php
require "vendor/autoload.php";
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;

$url = "https://shopee.co.id/shop/267130746/search";
$client = Client::createChromeClient("drivers/chromedriver", null, ["port" => 9558]);    // create a chrome client
$crawler = $client->request('GET', $url);
$client->waitFor('.shopee-page-controller');                                         // wait for the element with this css class until appear in DOM
echo $crawler->filter('._10Wbs-')->text();