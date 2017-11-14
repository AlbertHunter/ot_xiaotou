<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use OT\DataDictionary;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends HomeController {

	//系统首页备份
    public function index_bak(){
        $category = D('Category')->getTree();
        $lists    = D('Document')->lists(null);
        $this->assign('category',$category);//栏目
        $this->assign('lists',$lists);//列表
        $this->assign('page',D('Document')->page);//分页                
        $this->display('index');
    }




	public function index($parameter=''){
		@set_time_limit(120);
		@ini_set('pcre.backtrack_limit', 1000000);
		date_default_timezone_set('PRC');

		//采集规则
		$rule_id = M('CaijiConfig')->where('name="DEFAULT_RULE"')->getField('value');
		//采集配置
		$config = M('CaijiRule')->where("id=$rule_id")->find();
		$geturl = $config['weburl'];
		if($parameter){
			$geturl = $geturl.$parameter;
		}
		$content = $this->getwebcontent($geturl);

		
		/*当前域名*/
		$mydomain = 'http://'.$_SERVER['SERVER_NAME'].'/';
		/** 替换域名 */
		$html = str_replace($geturl,$mydomain,$content);


//	响应头信息

		$extension = pathinfo($geturl,PATHINFO_EXTENSION);
		$extension = strtolower($extension);
		if($extension == 'css'){
			header('Content-Type: text/css');
		}elseif($extension == 'js'){
			header('Content-Type: application/x-javascript');		
		}


		echo $html;	
	}


	function getalljs($html) {
		$regx = "~(<script\s+[^>]+>)~iUs";
		preg_match_all($regx, $html, $match);
		$jsArr=array();
		if($match){
			foreach($match[1] as $k=>$vo){
				if(preg_match('~src\s*=\s*(["|\']?)\s*([^"\'\s>\\\\]+)\s*\\1~i', $vo,$jsmatch)){
					$jsArr[]=$jsmatch[2];
				}
			}
			$jsArr=array_unique($jsArr);
		}
		sort($jsArr);
		return $jsArr;
	} 
	function getallcss($html) {
		$regx = "~(<link[^>]+>)~iUs";
		preg_match_all($regx, $html, $match);
		$cssHrefArr=array();
		if($match){
			foreach($match[1] as $k=>$vo){
				if(!preg_match('~rel\s*=\s*(["|\']?)\s*stylesheet\s*\\1~i', $vo)){
					unset($match[1][$k]);
					continue;
				}
				if(preg_match('~href\s*=\s*(["|\']?)\s*([^"\'\s>\\\\]+)\s*\\1~i', $vo,$hrefmatch)){
					$cssHrefArr[]=$hrefmatch[2];
				}
			}
			$cssHrefArr=array_unique($cssHrefArr);
		}
		sort($cssHrefArr);
		return $cssHrefArr;
	} 


	public function getwebcontent($url){ 
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url); 
		//模拟浏览器类型
		curl_setopt($curl, CURLOPT_USERAGENT,  "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0");      
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1); 
		$contents = trim(curl_exec($ch)); 
		curl_close($ch); 
		return $contents; 
	}

	/****/
	public function downloadcss($url){
		foreach($url as $list){
			echo '.';
		}
	
	}

	//此函数提供了国内的IP地址
	public function randIP(){
       $ip_long = array(
           array('607649792', '608174079'), //36.56.0.0-36.63.255.255
           array('1038614528', '1039007743'), //61.232.0.0-61.237.255.255
           array('1783627776', '1784676351'), //106.80.0.0-106.95.255.255
           array('2035023872', '2035154943'), //121.76.0.0-121.77.255.255
           array('2078801920', '2079064063'), //123.232.0.0-123.235.255.255
           array('-1950089216', '-1948778497'), //139.196.0.0-139.215.255.255
           array('-1425539072', '-1425014785'), //171.8.0.0-171.15.255.255
           array('-1236271104', '-1235419137'), //182.80.0.0-182.92.255.255
           array('-770113536', '-768606209'), //210.25.0.0-210.47.255.255
           array('-569376768', '-564133889'), //222.16.0.0-222.95.255.255
       );
       $rand_key = mt_rand(0, 9);
       $ip= long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
       $headers['CLIENT-IP'] = $ip; 
       $headers['X-FORWARDED-FOR'] = $ip; 

       $headerArr = array(); 
       foreach( $headers as $n => $v ) { 
           $headerArr[] = $n .':' . $v;  
       }
       return $headerArr;    
   }

}