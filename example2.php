<?php
include "scrapper.php";
use App\Lib\Scrapper\ElementData;
use App\Lib\Scrapper\ElementType;
use App\Lib\Scrapper\Scrapper;

$row = 1;
if (($handle = fopen("result/shopee_267130746/import_result.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $col=
        $num = count($data);
        echo "$num fields in line $row: \n";
        $row++;
        if(strpos('url',$data[1])!==0){
            $e=new ElementData('nama_produk','._3g8My- > span');
            $e1=new ElementData('deskripsi','.Mhqp_x > span');

            $s= new Scrapper('https://shopee.co.id/shop/267130746/'.$data[1],9999,'.shopee-page-controller',5);
            $s->setResultPath('result/shopee_267130746')
            ->setData($e)
            ->setData($e1)
            ->scrap()
            ->toCSV('deskripsi.csv');
        }    
    }
    fclose($handle);
}