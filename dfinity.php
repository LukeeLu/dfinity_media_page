<?php
header('Content-Type:text/plain;charset=utf-8');
require 'thinkphp_5/vendor/autoload.php';
use QL\QueryList;
use Medoo\medoo;
require_once 'thinkphp_5/Medoo-1.7.10/src/Medoo.php';



//连接数据库
global $database;
$database =new medoo([
    'database_type' => 'mysql',
   'database_name' => 'dfinity',
    'server' => '127.0.0.1',
    'port' => '3306',
    'username' => 'root',
    'password' => '*****',
    'charset' => 'utf8'
]);
//主函数

function index(){
    echo "爬虫开始。。。\n";
    //循环获取列表20次
    echo"正在爬取media页面\n";

    $detailfile= file_get_contents("./click_python_file/output2.html");#获取python模拟点击保存下来的HTML文件output2.html

    $list_rule=[
        'detail_url'=>['#gatsby-focus-wrapper > section.css-12joorq.css-1vun3h2.css-aufz6.undefined > div:nth-child(2) > a','href'],
        'title'=>['#gatsby-focus-wrapper > section.css-12joorq.css-1vun3h2.css-aufz6.undefined > div:nth-child(2) > a > div > div:nth-child(2) > p.css-12ltcs3','text'],
        'date_and_author'=>['#gatsby-focus-wrapper > section.css-12joorq.css-1vun3h2.css-aufz6.undefined > div:nth-child(2) > a > div > div:nth-child(2) > p.css-bxdkrh','text'],
    ];
    $final_data=crawl_data($detailfile,$list_rule);
    #循环列表页数据，获取详情，写入数据库
    foreach ($final_data as $key=>$value){
        echo "开始获取<<{$final_data[$key]['title']}>>的详情..\n";
        $dbdata['title']=$final_data[$key]['title'];
        $dbdata['detail_url']=$final_data[$key]['detail_url'];
        $arr = explode('by',$final_data[$key]['date_and_author']);
        $dbdata['author']= $arr[1];
        $dbdata['date']=$arr[0];

        //写入数据库
        echo "开始写入数据库..\n";
        $GLOBALS['database']->insert('dfinity_data',$dbdata);
}
    echo "爬虫结束";
}
//querylist爬取数据函数
function crawl_data($url,$rule){
    $data=QueryList::Query($url,$rule)->data;

    return $data;

}
index();
