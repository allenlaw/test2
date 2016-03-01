<?php

//网页内容安全代码过滤
if(isset($_GET['url'])){
	$url=$_GET['url'];
}else{
	echo '请输入要测试的url链接';exit;
}

$GatherContent=new GatherContent;
//采集解析
list($html,$htmlCode)=$GatherContent->getHtml($url);
$data=$GatherContent->analyticHtml($GatherContent->securityfilter($html),$url,3333);//数据过滤
var_dump($data);exit;
	


class GatherContent
{
	public $joinRedisUrl=array();
	public static $gatherContentRules=array();
	public $params=array(
		'htmlSecurityFilter'=>array(
			'note'=>"/<\!--.*?-->/si",//注释过滤
			'style'=>"/<(style.*?)>(.*?)<(\/style.*?)>/si",//style过滤
			'script'=>"/<(script.*?)>(.*?)<(\/script.*?)>/si",//script过滤
		),

		//稿件内容代码过滤
		'contentSecurityFilter'=>array(
			'tag'=>"<b><a><img><p><center><div><table><tr><td><th><strong><pagination><Extranet><embed>",//注释过滤
			'style'=>'/style=".*?"/',//style过滤
			'pagination'=>'<!--pagination-->',//要替换的分页标签
			'Extranet'=>'<!--Extranet-->',//要替换的外网或pc链接标识
		),
		
		'urlListRule'=>'/<a href="(.+)".+>.*<\/a>/isU',//提取高级列表url的规则
		'urlFilter'=>'/http\:\/\/.+\.dayoo\.com\/.+\.[html]+/s',//url 过滤
		'dayooUrlFilter'=>'/[http\:\/\/]?[news|life|315|m]+\.dayoo\.com\/.+\/[0-9]+\_+[0-9]+\.[html]+/s',//大洋url 过滤
		'contentFilterStringLength'=>30,//除img、embed、object标签外，稿件内容字符长度少于这个值则过滤

		'contentRule'=>array(
			//提取图片页模块信息的正则规则
			'image'=>array(
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
			//提取旧内容模块（2011-2013年7月前的内容模版）信息的正则规则
			'2011'=>array(
				'titleReg'=>'/<h1>(.*)<\/h1>/Us',//标题
				'contentReg'=>'/<div[\s\S]*?id="text_content"[\s\S]*?>(.*)?<p class="editor">/is',//内容
				'keywordsReg'=>'/<meta name="keywords" content="(.*?)"\/>/s',//关键词
				'summaryReg'=>'/<meta name="description" content="(.*?)"\/>/s',//摘要
				'authorReg'=>'/<p class="editor">\((.*)\)<\/p>/',//作者
				'sourceReg'=>'/<span id="source">来源\：(.*?)<\/span>/s',//来源
				'timeReg'=>'/<span class="red">(.*?)<\/span><span id="source">/s',//时间
				'thumbReg'=>'/<img.*src="([^"]+)".*\/>/isU',//内容第一张图片
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
				'thumbReg'=>'/<img.*src="([^"]+)".*\/>/isU',//内容第一张图片
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
		)
		);
	
	//安全过滤
	public function securityfilter($string){
		$htmlSecurityFilter=$this->params['htmlSecurityFilter'];
		$string=preg_replace($htmlSecurityFilter['note'],'',$string);	 //注释 
		$string=preg_replace($htmlSecurityFilter['style'],'',$string);	//过滤style标签 
		$string=preg_replace($htmlSecurityFilter['script'],'',$string);	//过滤script标签 
		return $string;
	}

	//内容属性过滤
	public function contentfilter($string,$url){
		$contentSecurityFilter=$this->params['contentSecurityFilter'];
		$string=strip_tags($string, $contentSecurityFilter['tag']);//去除非b、a、img、p的HTML标签
		$string=preg_replace($contentSecurityFilter['style'],'', $string);//去除所有style属性
		$string=preg_replace("/>[　|\xc2\xa0| ]+/u",">",$string);//去除空格
		$string=str_replace(array('&#160;','&#12288;','&nbsp;'),'',$string);
		$string=preg_replace('/(target=".*?")+?/is','', $string);//去除所有a标签打开模式属性
		/*去除图片链中的href 待优化*/
		preg_match_all('/<a.*href="([^"]+)".*><img/i',$string,$urlMath);
		if(isset($urlMath[1])){
			$string=str_replace($urlMath[1],'javascript:void(0)',$string);
		}

		$string=$this->tagsReplace($this->handleVideoTag($this->handleTextUrl($url,$string)));//替换标签
		return $string;
	}

	/*处理内容中的视频标签
	*@params $string string 
	*@return $string string 已处理的字符
	*/
	public function handleVideoTag($string){
		//提取所有视频链
		preg_match_all('/<embed[\s\S].*?src="(.*?)"[\s\S].*?><\/embed>/',$string,$urlMath);
		if(isset($urlMath[1])){
			for($i=0;$i<count($urlMath[1]);$i++){
				$urlMath[1][$i]='<!--embedSrc:'.$urlMath[1][$i].'-->';
			}
			$string=str_replace($urlMath[0],$urlMath[1],$string);
		}
		return $string;
	}

	/*处理内容中的文字链接
	*@params $sourceUrl string 内容文字链
	*@params $string string 获取
	*@return array array('string'=>已替换的内容,'url'=>要进采集队列的链接)
	*/
	public function handleTextUrl($sourceUrl,$string){
		$urls=array();//要进采集队列的链接

		//提取所有内容文字链
		preg_match_all("/[\<a]+[\s\S].*?href=\"(.*?)\"[\s\S]*?[>]+[\s\S]*?\<\/a>/",$string,$urlMath);
		if(isset($urlMath[1])){
			$urlReplace=array();
			foreach($urlMath[1] as $k=>$href){
				//验证是否为大洋网内容稿aid规则
				preg_match("/([0-9]+?[_]+[0-9]+?)[\.htm]+/",$href,$urlFilter);
				if(isset($urlFilter[1])){
					$urls[]=$this->getFullLink($sourceUrl,$href);//返回完整链接，添加到redis采集处理列表队列中
					$urlReplace[$k]=str_replace($href,'(articleUrl:'.$urlFilter[1].')',$urlMath[0][$k]);//替换为规定的标签
				}else{
					preg_match('/javascript/',$href,$urlFilter2);//查看是否为链接，区分链接与javascript
					if(!isset($urlFilter2[0])){
						$urlReplace[$k]=$urlMath[0][$k].'<Extranet>';
					}else{
						unset($urlMath[0][$k]);
					}
				}
			}
			
			//将原文内容链接替换为规定的标签
			$string=str_replace($urlMath[0],$urlReplace,$string);
		}
		
		$this->joinRedisUrl=$urls;//添加到redis采集处理列表队列中
		return $string;
	}
	
	/*替换内容标签
	*@params $string string 要替换的内容
	*@return $string string 已替换的内容
	*/
	public function tagsReplace($string){
		$contentSecurityFilter=$this->params['contentSecurityFilter'];
		$string=preg_replace("/<pagination>/",$contentSecurityFilter['pagination'],$string);
		$string=preg_replace("/<Extranet>/",$contentSecurityFilter['Extranet'],$string);
		return $string;
	}

	/*补充完整链接
	*@params $sourceUrl string 原稿来源的链接
	*@params $url string 内容文字链
	*@return string $url
	*/
	public function getFullLink($sourceUrl,$url){

		preg_match('/[http\:\/\/]*?([\S]+\.dayoo\.com)+?[\/]*?([a-z0-9]*?)[\/]*?([0-9]+?)[\/]*?([0-9]+?)[\/]*?([0-9_]+?)\.([html]+).?/s',$url,$urlMatch2);
		if(!empty($urlMatch2)){
			return $url;
		}else{
			$urls='';
			preg_match('/[http\:\/\/]*?([\S]+\.dayoo\.com)+?[\/]*?([a-z0-9]*?)[\/]*?([0-9]+?)[\/]*?([0-9]+?)[\/]*?([0-9_]+?)\.([html]+).?/s',$sourceUrl,$urlMatch);
			$level=substr_count($url,'../');
			$url=str_replace('../','',$url);
			//拼接
			//拼接
			if(empty($urlMatch)){
				$urlMatch=explode('/',$sourceUrl);
				$count=(count($urlMatch)-$level-2);
				if(empty($urlMatch)){return $url;}
				for($i=$count;$i>0;$i--){
					if(!isset($urlMatch[$i])){continue;}
					$urls='/'.$urlMatch[$i].$urls;
				}
				$url=$urls.'/'.$url;
				$url=str_replace('//','http://',$url);
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

	/*获取网络内容
		*@params $url
		*@return array($html,$htmlCode) ，如果请求失败则返回false
	*/
	public function getHtml($url){
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

	
	/* 获取高级列表上的链接
		*@params $url string 高级列表链接
		*@return array  高级列表上的链接
	*/
	public function getUrl($url){
		$urlList=array();
		$urlListHtml=$this->securityfilter($this->getHtml($url));
		preg_match_all($this->params['urlListRule'],$urlListHtml[0],$urlListMatch);//获取所有链接
		if(isset($urlListMatch[1])){
			foreach($urlListMatch[1] as $url){
				preg_match($this->params['urlFilter'],$url,$urlFilter);//url 过滤
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
	
	/*获取当前时间
		*@params $type
		*@return time 
	*/
	public function iTime($type){
		$result='';
		switch($type){
			case 'stamp':
				$result=time();
			case 'microstamp':
				$result=microtime(true);
			case 'stringDate':
				$result=date('Y-m-d');
			case 'stringAll':
			default:
				$result=date('Y-m-d H:i:s');
		}
		return $result;
	}

	
	/*选取内容采集模版，从页面中解析出内容
		*@params $html 采集的页面html
		*@params $url 采集的页面来源链接
		*@params $cid 存储的节点Id（来源于采集时的高级列表ID）
		*@return array  分析过滤后的页面数据信息
	*/
	public function analyticHtml($html,$url,$cid){
		if($cid==31){
			$template='image';//判定是否为图片频道模版
		}else{
			preg_match('/<div class="header">(.*)<div class="container">/s',$html,$sourceHtml);//判定是否为2011旧模版
			if(isset($sourceHtml[1])){
				$template='2011';
			}else{
				preg_match('/class="contentbox"/s',$html,$yearModelMath);//判定是否为2009旧模版
				if(isset($yearModelMath[0])){
					$template='2009';
				}else{
					$template='2013';
				}
			}
		}

		return $this->template($html,$url,$cid,$template);
	}


	
	/*获取配置文件中的采集正则规则
		*@params $templateName 模板标识
		*@return array  采集正则规则
	*/
	protected function getContentRule($templateName){
		if(empty(self::$gatherContentRules)){	
			self::$gatherContentRules=$this->params['contentRule'];
		}
		return self::$gatherContentRules[$templateName];
	}

	/*从页面中解析出内容
		*@params $html 采集的页面html
		*@params $url 采集的页面来源链接
		*@params $cid 存储的节点Id（来源于采集时的高级列表ID）
		*@params $template 模板采集规则标识
		*@return array  分析过滤后的页面数据信息
	*/
	protected function template($html,$url,$cid,$template){
		//获取配置文件中的采集正则规则
		$contentRule=$this->getContentRule($template);
		//局部变量初始化
		$data=array();
		/*正则匹配提取信息 start*/
		//标题
		preg_match($contentRule['titleReg'],$html,$titleMatch);
		if(isset($titleMatch[1])){
			$data['title']=trim($titleMatch[1]);
		}else{
			return false;
		}
		//内容
		preg_match($contentRule['contentReg'],$html,$contentMatch);
		if(isset($contentMatch[1])){
			if(strlen(trim(str_replace(array('&#160;','&#12288;','&nbsp;',' ',' '),'',strip_tags($contentMatch[1],'<img><object><embed>'))))==0){return false;}
			$data['content']=trim('<div class="content">'.$contentMatch[1]);
		}else{
			return false;
		}
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
		$data['isPhoto']=($template=='image')?1:0;
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
		$elements = $content->query($contentRule['paginationXpath']);
		foreach($elements as $key=>$element){
			$paginationUrls[]=$element->getAttribute('href');//提取分页链接
		}
		unset($html);
		unset($dom);
		unset($content);
		unset($elements);
	
		if(!empty($paginationUrls)){//存在分页内容
			preg_match($contentRule['paginationUrl'],$url,$urlMatch);
			$nodeUrl=isset($urlMatch[1])?$urlMatch[1].'/':''; //获取url完整节点目录

			//提取分页内容
			foreach($paginationUrls as $purl){
				$phtml=$this->securityfilter($this->getHtml($nodeUrl.$purl));//获取正文内容并做安全过滤
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
		
		$data['content']=$this->contentfilter($data['content'],$url);
		//将第一张图片压缩为三种格式的缩略图
		//采用的采集规则的数组标识
		$data['template']=$template;
		//返回数据
		return $data;
	}

}