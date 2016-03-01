<?php
//$url='http://news.dayoo.com/china/201312/20/53868_34104760.htm';
//$url='http://news.dayoo.com/guangzhou/201312/04/73437_33829327.htm';
//$url='http://news.dayoo.com/china/57400/201312/20/57400_110553993.htm';
if(isset($_GET['url'])){
	$urlSource=$_GET['url'];
}else{
	echo '请输入要测试的url链接';exit;
}
echo '<pre>';
//url采集页面
$url='61532_4279302_2.htm';

print_r(getFullLink($urlSource,$url));exit;


		preg_match('/[http\:\/\/]*?([\S]+\.dayoo\.com)+?[\/]*?([a-z0-9]*?)[\/]*?([0-9]+?)[\/]*?([0-9]+?)[\/]*?([0-9_]+?)\.([html]+).?/s',$urlSource,$urlMatch2);

		print_r($urlMatch2);exit;

$html=getHtml($urlSource);

//print_r($html);exit;

$content=analyticHtml($html[0],$urlSource);

$result=handleTextUrl($urlSource,$content['content']);

$content=contentfilter3($result);
//print_r($result);exit;

function getFullLink($sourceUrl,$url){

		preg_match('/[http\:\/\/]*?([\S]+\.dayoo\.com)+?[\/]*?([a-z0-9]*?)[\/]*?([0-9]+?)[\/]*?([0-9]+?)[\/]*?([0-9_]+?)\.([html]+).?/s',$url,$urlMatch2);

		if(!empty($urlMatch2)){
			return $url;
		}else{
			$urls='';
			preg_match('/[http\:\/\/]*?([\S]+\.dayoo\.com)+?[\/]*?([a-z0-9]*?)[\/]*?([0-9]+?)[\/]*?([0-9]+?)[\/]*?([0-9_]+?)\.([html]+).?/s',$sourceUrl,$urlMatch);
			$level=substr_count($url,'../');
			$url=str_replace('../','',$url);
			
			//拼接
			if(empty($urlMatch)){
				$urlMatch=explode('/',$sourceUrl);
				$count=(count($urlMatch)-$level-2);
				for($i=$count;$i>0;$i--){
					if(!isset($urlMatch[$i])){continue;}
					$urls='/'.$urlMatch[$i].$urls;
				}
				$url=$urls.'/'.$url;
			}else{
				for($i=1;$i<5-$level;$i++){
					if(!isset($urlMatch[$i])){break;}
					$urls.=$urlMatch[$i].'/';
				}
				$url=$urls.$url;
			}
			
			
			return $url;
		}
	}

//内容属性过滤
	 function contentfilter2($string){
		
		$contentSecurityFilter=array(
			'tag'=>"<b><a><img><p><center><div><table><tr><td><th><strong><pagination><Extranet>",//注释过滤
			'style'=>'/style=".*?"/',//style过滤
			'pagination'=>'<!--pagination-->',//
			'Extranet'=>'<!--Extranet-->',//要替换的分页标签
		);
		$string=strip_tags($string, $contentSecurityFilter['tag']);//去除非b、a、img、p的HTML标签
		$string=preg_replace($contentSecurityFilter['style'],'', $string);//去除所有style属性
		$string=preg_replace("/>[　|\xc2\xa0| ]+/u",">",$string);//去除空格
		$string=str_replace(array('&#160;','&#12288;','&nbsp;'),'',$string);
		$string=preg_replace("/<pagination>/",$contentSecurityFilter['pagination'],$string);
		$string=preg_replace("/<Extranet>/",$contentSecurityFilter['Extranet'],$string);
		
		/*去除图片链中的href 待优化*/
		preg_match_all('/<a.*href="([^"]+)".*><img/i',$string,$urlMath);
		if(isset($urlMath[1])){
			$string=str_replace($urlMath[1],'javascript:void(0)',$string);
		}
		return $string;
	}
	
	 function replaceArticleTags($html){
		preg_match_all('/\(articleUrl\:([0-9]+?[_]+[0-9]+?)\)/',$html,$articleMath);//////////////划分到内容页处理
		//print_r($articleMath);exit;
		
		return $html;
	}

//内容属性过滤
	 function contentfilter3($string){
		
		$contentSecurityFilter=array(
			'tag'=>"<b><a><img><p><center><div><table><tr><td><th><strong><pagination><Extranet>",//注释过滤
			'style'=>'/style=".*?"/',//style过滤
			'pagination'=>'<!--pagination-->',//
			'Extranet'=>'<!--Extranet-->',//要替换的分页标签
		);
		$string=strip_tags($string, $contentSecurityFilter['tag']);//去除非b、a、img、p的HTML标签
		$string=preg_replace($contentSecurityFilter['style'],'', $string);//去除所有style属性
		
		$string=preg_replace("/>[　|\xc2\xa0| ]+/u",">",$string);//去除空格
		$string=str_replace(array('&#160;','&#12288;','&nbsp;'),'',$string);
		$string=preg_replace("/<pagination>/",$contentSecurityFilter['pagination'],$string);
		$string=preg_replace("/<Extranet>/",$contentSecurityFilter['Extranet'],$string);
		$string=preg_replace('/(target=".*?")+?/is','', $string);//去除所有a标签打开模式属性
		//preg_match_all('/target=".*?"/is',$string,$articleMath);//////////////划分到内容页处理
		//print_r($articleMath);exit;

		
		//print_r($string);exit;
		/*去除图片链中的href 待优化*/
		preg_match_all('/<a.*href="([^"]+)".*><img/i',$string,$urlMath);
		if(isset($urlMath[1])){
			$string=str_replace($urlMath[1],'javascript:void(0)',$string);
		}
		return $string;
	}


	 function tagsReplace($string){
		
		$contentSecurityFilter=array(
			'tag'=>"<b><a><img><p><center><div><table><tr><td><th><strong><pagination><Extranet>",//注释过滤
			'style'=>'/style=".*?"/',//style过滤
			'pagination'=>'<!--pagination-->',//
			'Extranet'=>'<!--Extranet-->',//要替换的分页标签
		);
	
		$string=preg_replace("/<pagination>/",$contentSecurityFilter['pagination'],$string);
		$string=preg_replace("/<Extranet>/",$contentSecurityFilter['Extranet'],$string);
		
		preg_match_all('/\(articleUrl\:([0-9]+?[_]+[0-9]+?)\)/',$string,$articleMath);//////////////划分到内容页处理
		//print_r($articleMath);exit;
		
		return $string;
	}


function handleTextUrl($sourceUrl,$string){

	$urls=array();//要进采集队列的链接
	preg_match_all("/[\<a]+[\s\S].*?href=\"(.*?)\"[\s\S]*?[>]+[\s\S]*?\<\/a>/",$string,$urlMath);//提取所有内容文字链
	print_r($urlMath);
	if(isset($urlMath[1])){
		$urls=array();
		$urlReplace=array();
		foreach($urlMath[1] as $k=>$href){
			//验证是否为大洋网内容稿aid规则
			preg_match("/([0-9]+?[_]+[0-9]+?)[\.htm]+/",$href,$urlFilter);
			if(isset($urlFilter[1])){
				$urls[]=getFullLink($sourceUrl,$href);//返回完整链接，添加到redis采集处理列表队列中
				$urlReplace[$k]=str_replace($href,'(articleUrl:'.$urlFilter[1].')',$urlMath[0][$k]);//替换为规定的标签
			}else{
				//查看是否为链接，区分链接与javascript
				preg_match('/javascript/',$href,$urlFilter2);
				//print_r($urlFilter2);
				if(!isset($urlFilter2[0])){
					$urlReplace[$k]=$urlMath[0][$k].'<Extranet>';
				}else{
					unset($urlMath[0][$k]);
				}
			}
		}

		
		//print_r($urlIn);
		//print_r($urlOut);exit;
		print_r($urlMath[0]);
		print_r($urlReplace);
		$string=str_replace($urlMath[0],$urlReplace,$string);
		//print_r($content['content']);
		
	}
exit;
	return $string;
}


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

	function oldHtml($html,$url,$cid){

		$oldContentRule=array(
			'titleReg'=>'/<h1>(.*)<\/h1>/Us',//标题
			'contentReg'=>'/<div[\s\S]*?id="text_content"[\s\S]*?>(.*)?<p class="editor">/is',//内容
			'keywordsReg'=>'/<meta name="keywords" content="(.*?)"\/>/s',//关键词
			'summaryReg'=>'/<meta name="description" content="(.*?)"\/>/s',//摘要
			'authorReg'=>'/<div class="editor">\[(.*)\]<\/div>/s',//作者
			'sourceReg'=>'/<span id="source">来源\：(.*?)<\/span>/s',//来源
			'timeReg'=>'/<span class="red">(.*?)<\/span><span id="source">/s',//时间
			'thumbReg'=>'/<img.*src="([^"]+)".*\/>/isU',//内容第一张图片
			'urlReg'=>'/http\:\/\/(\S+).dayoo.com\/(\S+?)\/[0-9_\/]+\/([0-9_]+)\.([html]+).?/s',//链接信息
			'paginationXpath'=>"//div[@id='div_currpage']//a[@class='order']",//查找分页标识
			'paginationUrl'=>"/(\S+)\/([0-9_]+)\.([shtml]+)/s",//获取分页链接
			'paginationDelimiter'=>'<pagination>',//分页符号
		);
		
		//局部变量初始化
		$data=array();
		
		/*正则匹配提取信息 start*/
		//标题
		preg_match($oldContentRule['titleReg'],$html,$titleMatch);
		if(isset($titleMatch[1])){
			$data['title']=trim($titleMatch[1]);
		}else{
			return false;
		}
		
		//内容
		preg_match($oldContentRule['contentReg'],$html,$contentMatch);
		if(isset($contentMatch[1])){
			$data['content']=trim('<div class="content">'.$contentMatch[1]);
		}else{
			return false;
		}
		//关键词
		preg_match($oldContentRule['keywordsReg'],$html,$keywordsMatch);
		$data['keywords']=isset($keywordsMatch[1])?trim($keywordsMatch[1]):'';
		//摘要
		preg_match($oldContentRule['summaryReg'],$html,$summaryMatch);
		$data['summary']=isset($summaryMatch[1])?trim($summaryMatch[1]):'';
		//作者
		preg_match($oldContentRule['authorReg'],$html,$authorMatch);
		$data['author']=isset($authorMatch[1])?trim(str_replace(array('编辑','：','&#160;','&nbsp;',' ',"　"),'',$authorMatch[1])):'';
		//来源
		preg_match($oldContentRule['sourceReg'],$html,$sourceMatch);
		$data['source']=isset($sourceMatch[1])?trim(strip_tags($sourceMatch[1],'')):'';
		//发布时间
		preg_match($oldContentRule['timeReg'],$html,$timeMatch);
		$data['time']=isset($timeMatch[1])?trim($timeMatch[1]):'';
		//发布时间戳
		$data['date']=!empty($data['time'])?strtotime($data['time']):0;
		//提取内容中第一张图片
		preg_match_all($oldContentRule['thumbReg'],$data['content'],$picMatche);
		$data['thumb']=isset($picMatche[1][0])?$picMatche[1][0]:'';
		//标签
		$data['tags']='';
		preg_match($oldContentRule['urlReg'],$url,$urlMatch);
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
		$data['isPhoto']=0;
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
		$elements = $content->query($oldContentRule['paginationXpath']);//查找分页标签
		foreach($elements as $key=>$element){
			$paginationUrls[]=$element->getAttribute('href');//提取分页链接
		}
		unset($html);
		unset($dom);
		unset($content);
		unset($elements);
	
		if(!empty($paginationUrls)){//存在分页内容
			preg_match($oldContentRule['paginationUrl'],$url,$urlMatch);
			$nodeUrl=isset($urlMatch[1])?$urlMatch[1].'/':''; //获取url完整节点目录
			//提取分页内容
			foreach($paginationUrls as $purl){
				$phtml=securityfilter(getHtml($nodeUrl.$purl));//获取正文内容并做安全过滤
				preg_match($oldContentRule['contentReg'],$phtml[0],$pcontentMatch);
				if(isset($pcontentMatch[1])){
					$data['content'].=$oldContentRule['paginationDelimiter'].trim('<div class="content">'.$pcontentMatch[1]);
				}else{
					$data['content'].='';
				}
			}
			unset($phtml);
			unset($pcontentMatch);
		}
		unset($oldContentRule);
		//
		/*处理分页 end*/
		//节点ID
		$data['nodeId']=$cid;
		//获取url的情况
		$data['surl']=$url;
		//过滤标题标签
		$data['title']=strip_tags($data['title'],'');
		//过滤正文内容
		$data['content']=contentfilter2($data['content'],$url);
		//将第一张图片压缩为三种格式的缩略图
		
		//返回数据
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
