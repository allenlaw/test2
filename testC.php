<?php
//$url='http://news.dayoo.com/china/201312/20/53868_34104760.htm';
//$url='http://news.dayoo.com/guangzhou/201312/04/73437_33829327.htm';
//$url='http://news.dayoo.com/china/57400/201312/20/57400_110553993.htm';
if(isset($_GET['url'])){
	$url=$_GET['url'];
}else{
	echo '请输入要测试的url链接';exit;
}

//echo date("h:i:s");
$html=getHtml($url);

$content=analyticHtml($html[0],$url);
echo date("h:i:s");
echo '<pre>';
print_r($content);exit;


	function analyticHtml($html,$url){

		
		preg_match('/<div class="header">(.*)<div class="container">/s',$html,$sourceHtml);//判定是否为2011旧模版
		if(isset($sourceHtml[1])){
			preg_match('/<[div|p] class="editor">/is',$html,$editorHtml);//判定是否为2011旧模版
			if(empty($editorHtml)){
				$template='2000';
			}else{
				preg_match('/class="textimgbox"/is',$html,$photoHtml);//判定是否为2011旧模版
				if(empty($photoHtml)){
					$template='2011';
				}else{
					$template='2011Photo';
				}
			}
		}else{
			preg_match('/class="contentbox"/s',$html,$yearModelMath);//判定是否为2009旧模版
			if(isset($yearModelMath[0])){
				$template='2009';
			}else{
				$template='2013';
			}
		}
		
		//print_r($editorHtml);exit;
		//echo $template;exit;
		
		return template($html,$url,7,$template);
	}

//从图片内容页面中解析出内容
	function template($html,$url,$cid,$templateName){
		$gatherContentRules=array(
			//提取图片页模块信息的正则规则
			'image'=>array(
				'titleReg'=>'/<h1>(.*)<\/h1>/Us',//标题
				'contentReg'=>'/<div[\s\S]*?id="text_content"[\s\S]*?>(.*)?<div class="editor">/is',//内容
				'keywordsReg'=>'/<meta name="keywords" content="(.*?)"\/>/s',//关键词
				'summaryReg'=>'/<meta name="description" content="(.*?)"\/>/s',//摘要
				'authorReg'=>'/<div class="editor">\[(.*)\]<\/div>/s',//作者
				'sourceReg'=>'/<span class="source">来源\:(.*?)<\/span>/s',//来源
				'timeReg'=>'/<span class="time">(.*?)<\/span>/s',//时间
				'thumbReg'=>'/<img.*[\s]+src="([^"]+)".*\/>/isU',//内容第一张图片
				'urlReg'=>'/http\:\/\/(\S+).dayoo.com\/(\S+?)\/[0-9_\/]+\/([0-9_]+)\.([html]+).?/s',//链接信息
				'paginationXpath'=>"//div[@id='div_currpage']//a[@class='order']",//查找分页标识
				'paginationUrl'=>"/(\S+)\/([0-9_]+)\.([shtml]+)/s",//获取分页链接
				'paginationDelimiter'=>'<pagination>',//分页符号
			),
			//提取旧内容模块（2011-2013年7月前的内容模版）信息的正则规则
			'2011'=>array(
				'titleReg'=>'/<h1>(.*)<\/h1>/Us',//标题
				'contentReg'=>'/<div[\s\S]*?id="text_content"[\s\S]*?>(.*)?<p class="editor">/is',//内容
				'keywordsReg'=>'/<meta name="keywords" content="(.*?)"\/>/s',//关键词
				'summaryReg'=>'/<meta name="description" content="(.*?)"\/>/s',//摘要
				'authorReg'=>'/<p class="editor">\((.*)\)<\/p>/',//作者
				'sourceReg'=>'/<span id="source">来源\：(.*?)<\/span>/s',//来源
				//'timeReg'=>'/<span class="red">(.*?)<\/span><span id="source">/s',//时间
				'timeReg'=>'/([0-9]{4}\-[0-9]{2}\-[0-9]{2}[\s]+[0-9\:]+)/s',//时间
				'thumbReg'=>'/<img.*[\s]+src="([^"]+)".*\/>/isU',//内容第一张图片
				'urlReg'=>'/http\:\/\/(\S+).dayoo.com\/(\S+?)\/[0-9_\/]+\/([0-9_]+)\.([html]+).?/s',//链接信息
				'paginationXpath'=>"//div[@id='div_currpage']//a[@class='order']",//查找分页标识
				'paginationUrl'=>"/(\S+)\/([0-9_]+)\.([shtml]+)/s",//获取分页链接
				'paginationDelimiter'=>'<pagination>',//分页符号
			),
			//提取旧内容模块（2009-2011年的内容模版）信息的正则规则
			'2009'=>array(
				'titleReg'=>'/<h1>(.*)<\/h1>/Us',//标题
				'contentReg'=>'/<div[\s\S]*?id="text_content"[\s\S]*?>(.*)?<[div|p] class="editor">/is',//内容
				'keywordsReg'=>'/<meta name="keywords" content="(.*?)"\/>/s',//关键词
				'summaryReg'=>'/<meta name="description" content="(.*?)"\/>/s',//摘要
				'authorReg'=>'/<p[\s\S]*?class="editor">\((.*)\)<\/p>/',//作者
				'sourceReg'=>'/<span>来源[\:\：]+(.*?)<\/span>/s',//来源
				'timeReg'=>'/<span class="red">(.*?)<\/span>/s',//时间
				'thumbReg'=>'/<img.*[\s]+src="([^"]+)".*\/>/isU',//内容第一张图片
				'urlReg'=>'/http\:\/\/(\S+).dayoo.com\/(\S+?)\/[0-9_\/]+\/([0-9_]+)\.([html]+).?/s',//链接信息
				'paginationXpath'=>"//div[@id='div_currpage']//a[@class='order']",//查找分页标识
				'paginationUrl'=>"/(\S+)\/([0-9_]+)\.([shtml]+)/s",//获取分页链接
				'paginationDelimiter'=>'<pagination>',//分页符号
			),

			//提取内容模块（2013年7月后的内容模版）信息的正则规则
			'2013'=>array(
				'titleReg'=>'/<h1>(.*)<\/h1>/Us',//标题
				'contentReg'=>'/<div[\s\S]*?id="text_content"[\s\S]*?>(.*)?<div class="editor">/is',//内容
				'keywordsReg'=>'/<meta name="keywords" content="(.*?)"\/>/s',//关键词
				'summaryReg'=>'/<meta name="description" content="(.*?)"\/>/s',//摘要
				'authorReg'=>'/<div class="editor">\[(.*)\]<\/div>/s',//作者
				'sourceReg'=>'/<span class="source">来源\:(.*?)<\/span>/s',//来源
				'timeReg'=>'/<span class="time">(.*?)<\/span>/s',//时间
				'thumbReg'=>'/<img.*src="([^"]+)".*\/>/isU',//内容第一张图片
				'urlReg'=>'/http\:\/\/(\S+).dayoo.com\/(\S+?)\/[0-9_\/]+\/([0-9_]+)\.([html]+).?/s',//链接信息
				'paginationXpath'=>"//div[@id='div_currpage']//a[@class='order']",//查找分页标识
				'paginationUrl'=>"/(\S+)\/([0-9_]+)\.([shtml]+)/s",//获取分页链接
				'paginationDelimiter'=>'<pagination>',//分页符号
			),

			//提取内容模块（2000年后的内容模版）信息的正则规则
			'2000'=>array(
				'titleReg'=>'/<h1>(.*)<\/h1>/Us',//标题
				'contentReg'=>'/<div[\s\S]*?id="text"[\s\S]*?>[\s\S]*?<\/h6>(.*)?<div id="digest" style="display:none;">/is',//内容
				'keywordsReg'=>'/<meta name="keywords" content="(.*?)"\/>/s',//关键词
				'summaryReg'=>'/<meta name="description" content="(.*?)"\/>/s',//摘要
				'authorReg'=>'/<div class="editor">\[(.*)\]<\/div>/s',//作者
				'sourceReg'=>'/<span class="source">来源\:(.*?)<\/span>/s',//来源
				'timeReg'=>'/<span class="red">(.*?)<\/span> <span>来源/s',//时间
				'thumbReg'=>'/<img.*src="([^"]+)".*\/>/isU',//内容第一张图片
				'urlReg'=>'/http\:\/\/(\S+).dayoo.com\/(\S+?)\/[0-9_\/]+\/([0-9_]+)\.([html]+).?/s',//链接信息
				'paginationXpath'=>"//div[@id='div_currpage']//a[@class='order']",//查找分页标识
				'paginationUrl'=>"/(\S+)\/([0-9_]+)\.([shtml]+)/s",//获取分页链接
				'paginationDelimiter'=>'<pagination>',//分页符号
			),
			//提取旧内容模块（2010-2011年的图片稿模版）信息的正则规则
			'2011Photo'=>array(
				'titleReg'=>'/<h1>(.*)<\/h1>/Us',//标题
				'contentReg'=>'/<div[\s\S]*?class="textimgbox"[\s\S]*?>(.*)?<div width="100%">/is',//内容
				'keywordsReg'=>'/<meta name="keywords" content="(.*?)"\/>/s',//关键词
				'summaryReg'=>'/<meta name="description" content="(.*?)"\/>/s',//摘要
				'authorReg'=>'/<p class="editor">\((.*)\)<\/p>/',//作者
				'sourceReg'=>'/<span[\s\S]*?>来源\：(.*?)<\/span>/s',//来源
				'timeReg'=>'/([0-9]{4}\-[0-9]{2}\-[0-9]{2}[\s]+[0-9\:]+)/s',//时间
				'thumbReg'=>'/<img.*[\s]+src="([^"]+)".*\/>/isU',//内容第一张图片
				'urlReg'=>'/http\:\/\/(\S+).dayoo.com\/(\S+?)\/[0-9_\/]+\/([0-9_]+)\.([html]+).?/s',//链接信息
				'paginationXpath'=>"//div[@id='div_currpage']//a[@class='order']",//查找分页标识
				'paginationUrl'=>"/(\S+)\/([0-9_]+)\.([shtml]+)/s",//获取分页链接
				'paginationDelimiter'=>'<pagination>',//分页符号
			),
		);
		//获取配置文件中的采集正则规则
		$contentRule=$gatherContentRules[$templateName];
		//局部变量初始化
		$data=array();
		/*正则匹配提取信息 start*/
		//标题
		preg_match($contentRule['titleReg'],$html,$titleMatch);
			//print_r($titleMatch);exit;
		if(isset($titleMatch[1])){
			$data['title']=trim($titleMatch[1]);
			//print_r($data['title']);exit;
			preg_match('/href/',$data['title'],$isHref);
			if(isset($isHref[0])){
				return false;
			}
		}else{
			return false;
		}

		//内容
		preg_match($contentRule['contentReg'],$html,$contentMatch);
		//print_r($contentMatch);exit;
		if(isset($contentMatch[1])){
			if(strlen(trim(str_replace(array('&#160;','&#12288;','&nbsp;',' ',' '),'',strip_tags($contentMatch[1],'<img><object><embed>'))))==0){return false;}//过滤内容是空的稿件
			$data['content']=trim('<div class="content">'.$contentMatch[1]);
		}else{
			return false;
		}
		/*
		//关键词
		preg_match($contentRule['keywordsReg'],$html,$keywordsMatch);
		$data['keywords']=isset($keywordsMatch[1])?trim($keywordsMatch[1]):'';
		//摘要
		preg_match($contentRule['summaryReg'],$html,$summaryMatch);
		$data['summary']=isset($summaryMatch[1])?trim($summaryMatch[1]):'';
		//作者
		preg_match($contentRule['authorReg'],$html,$authorMatch);
		$data['author']=isset($authorMatch[1])?trim(str_replace(array('编辑','：','&#160;','&nbsp;',' ',"　"),'',$authorMatch[1])):'';
		//来源
		preg_match($contentRule['sourceReg'],$html,$sourceMatch);
		$data['source']=isset($sourceMatch[1])?trim(strip_tags($sourceMatch[1],'')):'';
		//发布时间
		preg_match($contentRule['timeReg'],$html,$timeMatch);
		$data['time']=isset($timeMatch[1])?trim(strip_tags($timeMatch[1],'')):'';
		
		//发布时间戳
		$data['date']=!empty($data['time'])?strtotime($data['time']):0;
		//提取内容中第一张图片
		preg_match_all($contentRule['thumbReg'],$data['content'],$picMatche);
		$data['thumb']=isset($picMatche[1][0])?$picMatche[1][0]:'';
		//标签
		$data['tags']='';
		preg_match($contentRule['urlReg'],$url,$urlMatch);
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
		//是否图片稿
		//文章状态
		$data['status']['isThumb']=empty($data['thumb'])?0:1;//是否有缩略图
		$data['status']['isTop']=0;//默认不置顶
		$data['status']['allowComment']=1;//默认打开评论
		$data['status']['isRelation']=0;//是否关联稿
		*/
		/*正则匹配提取信息 end*/

		/*处理分页 start*/
		$paginationUrls=array();
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$content = new DOMXPath($dom);
		$xpathArr=array(
			$contentRule['paginationXpath'],
			"//div[@id='div_page_roll2']//a[@class='order']",
			"//div[@id='div_page_roll3']//a[@class='order']"
		);
		foreach($xpathArr as $xpathPage){
			$elements = $content->query($xpathPage);
			foreach($elements as $key=>$element){
				$paginationUrls[]=$element->getAttribute('href');//提取分页链接
			}
		}
		
		//print_r($paginationUrls);exit;
		unset($html);
		unset($dom);
		unset($content);
		unset($elements);

		$pageSize=count($paginationUrls);//分页大小
		//echo $pageSize;exit;
		if($pageSize>0){//存在分页内容
			preg_match($contentRule['paginationUrl'],$url,$urlMatch);
			$nodeUrl=isset($urlMatch[1])?$urlMatch[1].'/':''; //获取url完整节点目录
			
			//提取分页内容
			//foreach($paginationUrls as $purl){
			for($i=0;$i<$pageSize;$i++){
				$phtml=securityfilter(getHtml($nodeUrl.$paginationUrls[$i]));//获取正文内容并做安全过滤
				preg_match($contentRule['contentReg'],$phtml[0],$pcontentMatch);
				if(isset($pcontentMatch[1])){
					$data['content'].=$contentRule['paginationDelimiter'].trim('<div class="content">'.$pcontentMatch[1]);
				}else{
					$data['content'].='';
				}

			}
			unset($phtml);
			unset($pcontentMatch);
		}
		unset($contentRule);
		//
		/*处理分页 end*/
		$data['nodeId']=$cid;
		//获取url的情况
		$data['surl']=$url;
		//过滤标题标签
		$data['title']=strip_tags($data['title'],'');
		//过滤正文内容
		$data['content']=contentfilter($data['content'],$url);
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
?>
