<?php
require 'vendor/autoload.php';
use QL\QueryList;
use Medoo\medoo;
require_once 'Medoo-1.7.10/src/Medoo.php';


//连接数据库
global $database;
$database =new medoo([
    'database_type' => 'mysql',
    'database_name' => 'aaa',
    'server' => '127.0.0.1',
    'port' => '8889',
    'username' => 'root',
    'password' => 'root',
    'charset' => 'utf8'
]);
//主函数
function index(){
    echo "爬虫开始。。。\n";
    //循环获取列表20次
    for ($i=1;$i<5;$i++){
        echo"正在爬取第{$i}页\n";
        $url="https://www.doujiju.com/home/courses/?per_page={$i}&category=all&price=all&level=all&role_number=all&language=all&rating=all&orderby=order_time";
        echo "url为：{$url}\n";

        $list_rule=[
            'detail_url'=>['body .course-details >a:first-child','href'],
            'title'=>['body .course-details >a:first-child','text'],#body > section.category-course-list-area > div > div.row > div.col-lg-9 > div > ul > li:nth-child(1) > div > div.course-details > a.course-title
            'intro'=>['body .hidetype2','text'],#body > section.category-course-list-area > div > div.row > div.col-lg-9 > div > ul > li:nth-child(1) > div > div.course-details > div.course-subtitle.hidetype2
            'author'=>['body .course-instructor','text'],#body > section.category-course-list-area > div > div.row > div.col-lg-9 > div > ul > li:nth-child(1) > div > div.course-details > a.course-instructor > span
            'price'=>['body .course-price','text'],#body > section.category-course-list-area > div > div.row > div.col-lg-9 > div > ul > li:nth-child(1) > div > div.course-price-rating > div.course-price > span
            'rate'=>['body .rating','text'],#body > section.category-course-list-area > div > div.row > div.col-lg-9 > div > ul > li:nth-child(1) > div > div.course-price-rating > div.rating > span
        ];
        $list_data=crawl_data($url,$list_rule);

        //$list_data=cleandata($list_data);
        #循环列表页数据，获取详情，写入数据库
        foreach ($list_data as $key=>$value){
            echo "开始获取<<{$list_data[$key]['title']}>>的详情..\n";
            //爬取详情
            $detail_rule=[
                'updatetime'=>['body .last-updated-date','text']#body > section.course-header-area > div > div > div.col-lg-8 > div > div.created-row > span.last-updated-date
            ];
           $detail_data=crawl_data($value['detail_url'],$detail_rule);
           #print_r($detail_data);die;
            print_r(finddate($detail_data['updatetime']));die;

            //组合数据
            $dbdata['id']=choose_a_uid();
            $dbdata['detail_url']=$list_data[$key]['detail_url'];
            $dbdata['title']=$list_data[$key]['title'];
            $dbdata['intro']=$list_data[$key]['intro'];
            $dbdata['author']=$list_data[$key]['author'];
            $dbdata['price']=$list_data[$key]['price'];
            $dbdata['rate']=$list_data[$key]['rate'];
            $dbdata['updatetime']=finddate($detail_data[0]['updatetime']);

            //写入数据库
            echo "开始写入数据库..\n";
            $GLOBALS['database']->insert('doujijucrawl',$dbdata);
            $res_id=$GLOBALS['database']->id();#res_id 如果是0的话就是存在，1为不存在，用于判断true false
            $res=$GLOBALS['database']->error();#res显示出来的就是错误原因
            if($res_id){
                echo "<<{$list_data[$key]['title']}>>写入数据库成功！\n";
                #die;
            }
            else{
                echo"<<{$list_data[$key]['title']}>>写入数据库失败\n";
                $res=$GLOBALS['database']->error();
                echo $res[2]."\n";
                die;
            }




        }
        #print_r($list_data);
        #die;
    }echo "爬虫结束";die;
}
//querylist爬取数据函数
function crawl_data($url,$rule){
    $data=QueryList::Query($url,$rule)->data;
    #print_r($data);die;
    return $data;

}
#随机从user中获取uid
function choose_a_uid(){
$datas = $GLOBALS['database']->select('doujijucrawl',['id']);
#var_dump($datas);
#die;
}
function finddate($string){

    $result=preg_match('/(\d{4}-\d{1,2}-\d{1,2})/',$string,$matchs);#正则来读取日期
    if($result){
        return $matchs[0];}
        else{
            return "2021-05-13";
        }
    #var_dump($matchs[1]);die;
    #return $match[1];
   #var_dump($match);die;

}
//function cleandata($data){
   // return str_replace(' ', '__', $data);
//}
index();