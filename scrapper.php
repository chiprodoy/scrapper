<?php
namespace App\Lib\Scrapper;

require "vendor/autoload.php";


class ElementType{
    const TEXT='text';
    const IMAGE='image';
    const LINK='link';

}

class ElementData{
    public $label;
    public $elementNode;
    public $elementType;

    public function __construct($label,$elementNode,ElementType $elementType=ElementType::TEXT)
    {   
        $this->label=$label;
        $this->elementNode=$elementNode;
        $this->elementType=$elementType;
        
    }
}

use Exception;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;
class Scrapper{

    private $url;
    private $data=array();
    private $client;
    private $waitFor;
    private $crawler ;
    private $result;

    public function __construct($url,$waitFor='')
    {
        $this->client=Client::createChromeClient("drivers/chromedriver", null, ["port" => 9696]);
        $this->crawler  = $this->client->request('GET', $url);
        $this->waitFor=$waitFor;
        return $this;
    }


    public function setData(ElementData $elmData){
       array_push($this->data,$elmData);
        return $this;
    }
    public function connect(){
        $this->client->waitFor($this->waitFor);  
        return $this;
    }
    private function download(){
        
    }
    public function scrap(){
        $result=array();
        $data=$this->data;
        $this->crawler->filter('div.shop-search-result-view__item.col-xs-2-4')
        ->each(function(Crawler $parentCrawler, $i) use($data,&$result){
            foreach($data as $k => $v){
                    echo $v->label.",".$v->elementNode.",".strpos($v->elementNode,'.');
                    try{
                        if($v->elementType==ElementType::IMAGE){
                           $result[$i][$v->label]=$parentCrawler->filter($v->elementNode)->getAttribute('src');
                        }elseif($v->elementType==ElementType::LINK){
                            $result[$i][$v->label]=$parentCrawler->filter($v->elementNode)->getAttribute('href');
                        }else{
                            $result[$i][$v->label]=$parentCrawler->filter($v->elementNode)->text();
                        }  

                    }catch(Exception $e){
                        $result[$i][$v->label]='';
                    }
            }
        });
        /*
        foreach($this->data as $k => $v){
            if(strpos($v->elementNode,'.')===0){
                echo $v->label.",".$v->elementNode.",".strpos($v->elementNode,'.');

                $this->crawler->filter($v->elementNode)->each(function(Crawler $parentCrawler, $i) use($v,&$result){
                    
                    $result[$i][$v->label]=$parentCrawler->text();
                   
                });
            }else{
                $result[][$v->label]=  $this->crawler->filter($v->elementNode)->text();
            }
 
           // $a=$this->crawler->filter($v->elementNode)->text();

        }        
        */                               // wait for the element with this css class until appear in DOM
        print_r($result);
    }
 }

 $e=new ElementData('nama_produk','._10Wbs-');
 $e2=new ElementData('harga','.zp9xm9');
 $e3=new ElementData('harga_diskon','._1d9_77');
 $e4=new ElementData('diskon','._3yCxz- > span.percent');
 $e5=new ElementData('terjual','._2VIlt8');
 $e6=new ElementData('gambar','img._3-N5L6',ElementType::IMAGE);
 
 //$disc=new ElementData('diskon',)

 $s= new Scrapper('https://shopee.co.id/shop/267130746/search','.shopee-page-controller');
 $s->setData($e)
 ->setData($e2)
 ->setData($e3)
 ->setData($e4)
 ->connect()
 ->scrap();
//print_r($s);
?>
