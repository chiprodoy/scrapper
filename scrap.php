<?php
/*require "vendor/autoload.php";
use PHPHtmlParser\Dom;

$dom = new Dom;
$dom->load('https://shopee.co.id/shop/118069580/search?page=0&sortBy=sales');
$html = $dom->outerHtml;
echo $html;
$contents = $dom->find('.shop-search-result-view__item');
echo count($contents); // 10*/


set_time_limit(0);

class DataGroup{
 const SHOPEE_SALES='sales';
 const SHOPEE_POPULAR='pop';
 const SHOPEE_NEWEST='ctime';

}

$marketPlace="shopee";
$idToko=267130746;
$group=DataGroup::SHOPEE_SALES;
$cat=null;
$start=0;
$limit=10;
$dirName=$marketPlace."_".$idToko."_".$cat;
//$data=getProduk($idToko,$group,$cat,$start,$limit);
$data=getProduk($idToko,$group,$cat,$start);
$data=json_decode($data);

if (empty($data->items)) {
    echo json_encode(['success'=>false,'data'=>[0=>['message'=>'Koneksi Gagal / Toko tidak ditemukan']]]);
    exit();
}

$col=array("link","shopid","itemid","model","name","stok","brand","harga_discount","harga","diskon","sold","stock","desc","img1","img2","img3","img4","img5",
     "variant1","variant2","variant3","variant4","variant5");

if(!is_dir($dirName)) mkdir($dirName);

$fp = fopen($dirName.'/import_result.csv', 'a');
fputcsv($fp, $col,';');


foreach ($data->items as $k => $v){
    $str="\"";
//https://shopee.co.id/api/v1/shop_collections/?filter_empty=1&limit=20&offset=0&shopid=118069580
//https://shopee.co.id/api/v0/search/api/facet/?match_id=118069580&page_type=shop
//https://shopee.co.id/api/v2/search_items/?by=pop&categoryids=12456&limit=30&match_id=118069580&newest=0&order=desc&page_type=shop
    $str.="https://shopee.co.id/".str_replace(' ','-',preg_replace('/[^A-Za-z0-9\-\+]/', ' ', $v->name)).".".$v->shopid.".".$v->itemid;
    $str.="^#".$v->shopid;
    $str.="^#".$v->itemid;
    $str.="^#".cleanText($v->tier_variations[0]->name);
    $str.="^#".cleanText($v->name);
    $str.="^#".$v->stock;
    $str.="^#".$v->brand;
    $str.="^#".$v->price/100000;
    $str.="^#".$v->price_before_discount/100000;
    $str.="^#".$v->show_discount;
    $str.="^#".$v->sold;
    $str.="^#".$v->stock;
    $detail=getDetail($v->shopid,$v->itemid);
    $detail=json_decode($detail);

    $str.="^#".preg_replace('/[\x00-\x1F\x80-\xFF]/', '',str_replace(";",'',$detail->item->description));

/*    foreach ($detail->item as $dk => $dv){

    }*/


    for($ic=1;$ic<=5;$ic++) {
        if ($ic < count($v->images)) {
            $ik = $v->images[$ic];
            if (!file_exists($dirName . '/' . $ik . '.jpg')){
                $content = file_get_contents("https://cf.shopee.co.id/file/" . $ik . "_tn");
                file_put_contents($dirName . '/' . $ik . '.jpg', $content);
            }
            $str .= "^#" . $ik . ".jpg";
        } else {
            $str .= "^#' '";

        }
    }


    foreach ($v->tier_variations as $vk => $vVal){
        for($ic=1;$ic<=5;$ic++) {
            if ($ic < count($vVal->options)) {
               $str .= "^#" . $vVal->options[$ic];
            }
            else {
                $str .= "^#' '";
            }
        }
    }
    $str.="\"";
    //$result=[];
    $str=explode("^#",$str);
    //array_push($result,$str);
    fputcsv($fp, cleanText($str),";");
}
$zip = new ZipArchive;
if ($zip->open($dirName.'.zip', ZipArchive::CREATE) === TRUE)
{
    if ($handle = opendir($dirName))
    {
        // Add all files inside the directory
        while (false !== ($entry = readdir($handle)))
        {
            if ($entry != "." && $entry != ".." && !is_dir($dirName.'/' . $entry))
            {
                $zip->addFile($dirName.'/' . $entry);
            }
        }
        closedir($handle);
    }

    $zip->close();
}

$link=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/download.php?f=".$dirName.".zip";

echo json_encode(['success'=>true,'data'=>[0=>['row_count'=>$data->total_count,'start'=>$start,'limit'=>$limit,'file'=>$link]]]);

//rmdir($dirName);

function getDetail($shopID,$itemID){
    $param="itemid=$itemID";
    $param.="&shopid=$shopID";
    $url="https://shopee.co.id/api/v2/item/get?".$param;
    $ch = curl_init();
    // set url
    curl_setopt($ch, CURLOPT_URL, $url);

    // set user agent
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:68.0) Gecko/20100101 Firefox/68.0');
    $header[0] = "Accept: */*";
    $header[] = "Accept-Language: en-US,en;q=0.5";
    $header[] = "X-API-SOURCE: pc";
    $header[] = "X-Requested-With: XMLHttpRequest";

    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_REFERER, 'https://shopee.co.id/shop/118069580/search?page=0&sortBy=sales');
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate, br');
    // return the transfer as a string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // $output contains the output string
    $output = curl_exec($ch);

    // tutup curl
    curl_close($ch);
    return $output;

}

function getProduk($idToko,$groupBy='pop',$category=null,$newest=0,$limit=30){
//https://shopee.co.id/api/v2/search_items/?by=pop&categoryids=12456&limit=30&match_id=118069580&newest=30&order=desc&page_type=shop
//https://shopee.co.id/api/v2/search_items/?by=pop&categoryids=12456&limit=30&match_id=118069580&newest=60&order=desc&page_type=shop
    // persiapkan curl
    $param="by=$groupBy";
    $param.="&limit=$limit";
    (empty($category)) ? $param.="&categoryids=$category" : $param.="" ;
    $param.="&match_id=$idToko";
    $param.="&newest=$newest";
    $param.="&order=desc";
    $param.="&page_type=shop";
    $url="https://shopee.co.id/api/v2/search_items/?".$param;
    $ch = curl_init();

    // set url
    curl_setopt($ch, CURLOPT_URL, $url);

    // set user agent
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:68.0) Gecko/20100101 Firefox/68.0');
    $header[0] = "Accept: */*";
    $header[] = "Accept-Language: en-US,en;q=0.5";
    $header[] = "X-API-SOURCE: pc";
    $header[] = "X-Requested-With: XMLHttpRequest";

    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_REFERER, 'https://shopee.co.id/shop/118069580/search?page=0&sortBy=sales');
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate, br');
    // return the transfer as a string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // $output contains the output string
    $output = curl_exec($ch);

    // tutup curl
    curl_close($ch);
    return $output;
    // mengembalikan hasil curl
//    echo json_encode($output,  JSON_PRETTY_PRINT);
}
function cleanText($str){
    $keyword=[','];
    $replace=['.'];
    return str_replace($keyword,$replace,$str);
}
?>
