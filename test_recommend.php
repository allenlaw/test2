<?php
//$url='http://news.dayoo.com/china/201312/20/53868_34104760.htm';
//$url='http://news.dayoo.com/guangzhou/201312/04/73437_33829327.htm';
//$url='http://news.dayoo.com/china/57400/201312/20/57400_110553993.htm';
if(isset($_GET['url'])){
	$url=$_GET['url'];
}else{
	echo '请输入要测试的url链接';exit;
}

$html=getHtml($url);

$content=analyticHtml($html[0],$url);

$head=dataHeadlines($content);


print_r($head);exit;


	function analyticHtml($html,$url){
		//判定是否为旧模版
		preg_match('/<div class="header">(.*)<div class="container">/s',$html,$sourceHtml);
		if(isset($sourceHtml[1])){
			$data=oldHtml($html,$url,31);
		}else{
			$data=imageHtml($html,$url,31);
		}
		return $data;
	}

//从图片内容页面中解析出内容
	function imageHtml($html,$url,$cid){
		//局部变量初始化
		$data=array();
		/*正则匹配提取信息 start*/
		//标题
		preg_match('/<h1>(.*)<\/h1>/Us',$html,$titleMatch);
		//print_r($titleMatch);exit;
		if(isset($titleMatch[1])){
			$data['title']=trim($titleMatch[1]);
		}else{
			return false;
		}
		//内容
		preg_match('/<div[\s\S]*?id="text_content"[\s\S]*?>(.*)?<div class="editor">/is',$html,$contentMatch);
		if(isset($contentMatch[1])){
			$data['content']=trim('<div class="content">'.$contentMatch[1]);
		}else{
			return false;
		}
		//关键词
		preg_match('/<meta name="keywords" content="(.*?)"\/>/s',$html,$keywordsMatch);
		$data['keywords']=isset($keywordsMatch[1])?trim($keywordsMatch[1]):'';
		//摘要
		preg_match('/<meta name="description" content="(.*?)"\/>/s',$html,$summaryMatch);
		$data['summary']=isset($summaryMatch[1])?trim($summaryMatch[1]):'';
		//作者
		preg_match('/<div class="editor">\[(.*)\]<\/div>/s',$html,$authorMatch);
		$data['author']=isset($authorMatch[1])?trim(str_replace(array('编辑','：','&#160;','&nbsp;',' ',"　"),'',$authorMatch[1])):'';
		//来源
		preg_match('/<span class="source">来源\:(.*?)<\/span>/s',$html,$sourceMatch);
		$data['source']=isset($sourceMatch[1])?trim(strip_tags($sourceMatch[1],'')):'';
		//发布时间
		preg_match('/<span class="time">(.*?)<\/span>/s',$html,$timeMatch);
		$data['time']=isset($timeMatch[1])?trim($timeMatch[1]):'';
		//发布时间戳
		$data['date']=!empty($data['time'])?strtotime($data['time']):0;
		//提取内容中第一张图片
		preg_match_all('/<img.*src="([^"]+)".*\/>/isU',$data['content'],$picMatche);
		//print_r($picMatche);exit;
		$data['thumb']=isset($picMatche[1][0])?$picMatche[1][0]:'';
		//标签
		$data['tags']='';
		preg_match('/http\:\/\/(\S+).dayoo.com\/(\S+?)\/[0-9_\/]+\/([0-9_]+)\.([html]+).?/s',$url,$urlMatch);
		//子域名
		$data['subDomain']=isset($urlMatch[1])?$urlMatch[1]:'';
		//子目录
		$data['subDirectory']=isset($urlMatch[2])?$urlMatch[2]:'';
		//原文章ID
		$data['aid']=isset($urlMatch[3])?$urlMatch[3]:'';
		//url后缀
		$data['urlSuffix']=isset($urlMatch[4])?$urlMatch[4]:'';
		//创建/更新时间-时间戳微秒
		$data['ctime']=$data['utime']=microtime(true);
		//是否删除
		$data['isDel']=0;
		//文章状态
		$data['status']['isThumb']=empty($data['thumb'])?0:1;//是否有缩略图
		$data['status']['isTop']=0;//默认不置顶
		$data['status']['allowComment']=1;//默认打开评论
		$data['status']['isRelation']=0;//是否关联稿
		/*正则匹配提取信息 end*/

		/*处理分页 start*/
		$paginationUrls=array();
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$content = new DOMXPath($dom);
		$elements = $content->query("//div[@id='div_currpage']//a[@class='order']");
		foreach($elements as $key=>$element){
			$paginationUrls[]=$element->getAttribute('href');//提取分页链接
		}
		unset($html);
		unset($dom);
		unset($content);
		unset($elements);
	
		if(!empty($paginationUrls)){//存在分页内容
			preg_match('/(\S+)\/([0-9_]+)\.([shtml]+)/s',$url,$urlMatch);
			$nodeUrl=isset($urlMatch[1])?$urlMatch[1].'/':''; //获取url完整节点目录

			//提取分页内容
			foreach($paginationUrls as $purl){
				$phtml=securityfilter(getHtml($nodeUrl.$purl));//获取正文内容并做安全过滤
				preg_match('/<div[\s\S]*?id="text_content"[\s\S]*?>(.*)?<div class="editor">/is',$phtml[0],$pcontentMatch);
				if(isset($pcontentMatch[1])){
					$data['content'].='<pagination>'.trim('<div class="content">'.$pcontentMatch[1]);
				}else{
					$data['content'].='';
				}
			}
			unset($phtml);
			unset($pcontentMatch);
		}
		//
		/*处理分页 end*/
		$data['nodeId']=$cid;
		//获取url的情况
		$data['surl']=$url;
		//过滤标题标签
		$data['title']=strip_tags($data['title'],'');
		//过滤正文内容
		$data['content']=contentfilter2($data['content']);
		//将第一张图片压缩为三种格式的缩略图

		//返回数据
		return $data;
	}


//安全过滤
 function securityfilter($string){
		$string=preg_replace("/<\!--.*?-->/si",'',$string);	 //注释 
		$string=preg_replace("/<(style.*?)>(.*?)<(\/style.*?)>/si",'',$string);	//过滤style标签 
		$string=preg_replace("/<(script.*?)>(.*?)<(\/script.*?)>/si",'',$string);	//过滤script标签 
		return $string;
	}




 function contentfilter($string){
	
		$string=strip_tags($string, '<b><a><img><p><center><div><table><tr><td><th><th><strong><pagination>');//去除非b、a、img、p的HTML标签
		$string=preg_replace('/style=".*?"/','', $string);//去除所有style属性
		$string=preg_replace("/>[　\&nbsp\;\xc2\xa0\&#160\;\&#12288\; ]+/u",">",$string);//去除空格
		$string=preg_replace("/<pagination>/","<!--pagination-->",$string);//去除空格

		preg_match_all("/[\<a]+[\s\S]+?href=\"(.*?)\"[\s\S]*?[><img]+/",$string,$urlMath);
		if(isset($urlMath[1])){
			foreach($urlMath[1] as $url){
				$string=str_replace($url,'',$string);
			}	
		}
		
		return $string;
	}

	
	//内容属性过滤
	 function contentfilter2($string){
		$contentSecurityFilter=array(
			'tag'=>"<b><a><img><p><center><div><table><tr><td><th><strong><pagination>",//注释过滤
			'style'=>'/style=".*?"/',//style过滤
			'pagination'=>'<!--pagination-->',//要替换的分页标签
		);
		$string=strip_tags($string, $contentSecurityFilter['tag']);//去除非b、a、img、p的HTML标签
		$string=preg_replace($contentSecurityFilter['style'],'', $string);//去除所有style属性
		$string=preg_replace("/>[　|\xc2\xa0| ]+/u",">",$string);//去除空格
		$string=str_replace(array('&#160;','&#12288;','&nbsp;'),'',$string);
		$string=preg_replace("/<pagination>/",$contentSecurityFilter['pagination'],$string);
		/*去除图片链中的href 待优化*/
		preg_match_all('/<a.*href="([^"]+)".*><img/i',$string,$urlMath);
		if(isset($urlMath[1])){
			$string=str_replace($urlMath[1],'javascript:void(0)',$string);
		}
		return $string;
	}

	

 function getHtml($url){
		$htmlCode=200;//http状态码
		$html=false;//抓取的内容
		$timeout = 10; //请求超时时间

		if(function_exists('curl_init')) { 		 
			$ch = curl_init();
			curl_setopt ($ch, CURLOPT_URL,$url);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch,CURLOPT_HEADER,0);
			curl_setopt ($ch, CURLOPT_NOBODY, 0);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$html = curl_exec($ch);
			$htmlCode= curl_getinfo($ch,CURLINFO_HTTP_CODE);
			curl_close($ch);
		}elseif(function_exists('file_get_contents')) {//不支持http状态判断
			$opts= array(
				'http'=>array(
				'method'=>'GET',
				'user_agent'=>'Mozilla/5.0 (Windows NT 5.1; rv:24.0) Gecko/20100101 Firefox/24.0',
				'timeout'=>$timeout,
				'ignore_errors'=>false,
			));
			$context = stream_context_create($opts);
			$html =@file_get_contents($url, false, $context);		
		}

		if($html!==false && !empty($html))//抓取成功
			return array($html,$htmlCode);
		else//抓取失败
			return array(false,false);
	}


function getUrl($url){
		$urlList=array();
		$urlListHtml=securityfilter(getHtml($url));
		preg_match_all('/<a href="(.+)".+>.*<\/a>/isU',$urlListHtml[0],$urlListMatch);//获取所有链接
		if(isset($urlListMatch[1])){
			foreach($urlListMatch[1] as $url){
				preg_match('/http\:\/\/.+\.dayoo\.com\/.+\.[html]+/s',$url,$urlFilter);//url 过滤
				if(empty($urlFilter)){
					continue;
				}else{
					$urlList[]=$urlFilter[0];
				}
			}
		}
		unset($urlListHtml);
		return $urlList;
	}

	/*推荐数据入库操作
	*@params $data array 网页数据
	*@return string
	*/
	function dataHeadlines($data){
		//初始化
		$result=array(
			'headlines'=>array(),	
			'headImg'=>array(),
			'focus'=>array()
		);
		//非推荐位文章状态
		$data['status']['isRecommend']=0;
		$urlSpecialLists=array(
			//头条
			'headlines'=>array(
				'url'=>'http://news.dayoo.com/test/133581/134211/134258/index.shtml',
				'id'=>1,
				'recommend'=>1
			),
			//头图
			'headImg'=>array(
				'url'=>'http://news.dayoo.com/test/133581/134211/134260/index.shtml',
				'id'=>2,
				'recommend'=>1
			),
			//焦点
			'focus'=>array(
				'url'=>'http://news.dayoo.com/test/133581/134211/134259/index.shtml',
				'id'=>3,
				'recommend'=>1
			)
		);

		//选择数据表
		$iRecommendCollection=getMongodb("i_recommend");
		/*获取推荐的高级列表*/
		$headlineUrl=getUrl($urlSpecialLists['headlines']["url"]);
		$headImgUrl=getUrl($urlSpecialLists['headImg']["url"]);
		$focusUrl=getUrl($urlSpecialLists['focus']["url"]);
		
		//头条
		if(in_array($data['surl'],$headlineUrl)){
			if(!$iRecommendCollection->update(array('aid'=>$data['aid'],"moduleId"=>1),recommendData($data,1,'headlines'),array('upsert'=>true,'multiple'=>false))){//不存在则写入数据，存在则更新
				$result['headlines']=$data['surl'];
			}else{
				$result['headlines']=1;
			}
		}
		//头图
		if(in_array($data['surl'],$headImgUrl)){
			if(!$iRecommendCollection->update(array('aid'=>$data['aid'],"moduleId"=>2),recommendData($data,2,'headImg'),array('upsert'=>true,'multiple'=>false))){//不存在则写入数据，存在则更新
				$result['headImg']=$data['surl'];
			}else{
				$result['headImg']=1;
			}
		}
		//焦点
		if(in_array($data['surl'],$focusUrl)){
			if(!$iRecommendCollection->update(array('aid'=>$data['aid'],"moduleId"=>3),recommendData($data,3,'focus'),array('upsert'=>true,'multiple'=>false))){//不存在则写入数据，存在则更新
				$result['focus']=$data['surl'];
			}else{
				$result['focus']=1;
			}
		}

		return $result;
	}
	
	/*组装推荐表数据
	*@params $data array 推荐表信息数据
	*@params $id int 模块ID
	*@return array
	*/
	function recommendData($data,$id,$type=''){
		return array(
			"moduleId"=>$id,
			"aid"=>$data['aid'],
			"title"=>$data['title'],
			"type"=>$type,
			"html"=>'',
			"thumb"=>$data['thumb'],
			"summary"=>$data['summary'],
			'domain'=>$data['subDomain'],
			'directory'=>$data['subDirectory'],
			"time"=>$data['date'],
			"order"=>1,
			"createTime"=>time(),
			//'thumbImg'=>$data['thumbImg'],
			'status'=>$data['status']
		);
	}


	//mongodb 连接
	function getMongodbConnect(){
		
		$mongoParams=array(
			'host'=>'192.168.94.112',
			'port'=>27017,
			'user'=>'idayoo',
			'password'=>'idayoo',
			'dbname'=>'idayoo',
			'timeout'=>30000,
		);

		$options = array(
			'connectTimeoutMS'=>$mongoParams['timeout'],//30s
			'username'=>$mongoParams['user'],//username
			'password'=>$mongoParams['password'],//pwd
			'db'=>$mongoParams['dbname'],//dbname
		);	
		
		try {
			$_mongodbResources = new Mongo("mongodb://".$mongoParams['host'].":".$mongoParams['port'],$options);

		}catch(MongoConnectionException $e) {
			print $e->getMessage();
			Yii::app()->end(); 
		}
			
		return $_mongodbResources;
	}

	/*
	* 选择mongodb库中的数据表
	* $table string 要进行进行操作的数据表
	* @return collection
	*/
	function getMongodb($table){
		//初始化
		$mongodbConnect=getMongodbConnect();//连接mongodb
		$mongodbDb=$mongodbConnect->selectDB('idayoo');//选择数据库
		$collection=$mongodbConnect->selectCollection($mongodbDb,$table);//选择数据表
		return $collection;
	}




?>
