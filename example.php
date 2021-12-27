<?php
include "scrapper.php";
use App\Lib\Scrapper\ElementData;
use App\Lib\Scrapper\ElementType;
use App\Lib\Scrapper\Scrapper;

$e=new ElementData('nama_produk','._10Wbs-');
$e=new ElementData('url','a[data-sqe="link"]',ElementType::LINK);
$e2=new ElementData('harga','.zp9xm9');
$e3=new ElementData('harga_diskon','._3c5u7X');
$e4=new ElementData('diskon','._2xsPH4 > span.percent');
$e5=new ElementData('terjual','._1uq9fs');
//$e6=new ElementData('gambar','div._25_r8I.ggJllv > img',ElementType::IMAGE);
$e6=new ElementData('gambar','._3-N5L6',ElementType::IMAGE);

//$disc=new ElementData('diskon',)

$s= new Scrapper('https://shopee.co.id/shop/267130746/search?page=5&sortBy=ctime',9999,'.shopee-page-controller',3);
$s->setResultPath('result/shopee_267130746')
->setData($e)
->setData($e2)
->setData($e3)
->setData($e4)
->setData($e5)
->setData($e6)
->scrap()
->toCSV();
?>