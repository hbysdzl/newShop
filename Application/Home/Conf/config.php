<?php
return array(
    'HTML_CACHE_ON'     =>    false, // 开启静态缓存
    'HTML_CACHE_TIME'   =>    60,   // 全局静态缓存有效期（秒）
    'HTML_FILE_SUFFIX'  =>    '.html', // 设置静态缓存文件后缀
    'HTML_CACHE_RULES'  =>     array(   // 定义静态缓存规则   
                
                'Index:index'    =>   array('idnex', 3600), // 首页缓存
                'Goods:goods'   =>    array('{goods_id|goodsdir}/goods_{goods_id}',3600),//商品详情页设置缓存                
     )
                   
);
//定义函数，每一百个缓存文件放在一个目录下
function goodsdir($id){
    return ceil($id/100); //计算所在目录的名称
    
}