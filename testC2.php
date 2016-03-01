<?php
$string='<div class="content" id="text_content">
<p align="center" style="TEXT-ALIGN: center">&#160;&#160;&#160; <a href="54034_33799649_2.htm" target="_self"><img align="center" alt="《武侠》激情戏被删原因：" border="0" height="307" hspace="0" id="9498497" md5="" sourcedescription="编辑提供的本地文件" sourcename="本地文件" src="http://img1.dayoo.com/photo/attachement/jpg/site1/20131202/001372af745c140666e100.jpg" style="BORDER-RIGHT: #000000 0px solid; BORDER-TOP: #000000 0px solid; BORDER-LEFT: #000000 0px solid; WIDTH: 500px; BORDER-BOTTOM: #000000 0px solid; HEIGHT: 307px" title="《武侠》激情戏被删原因：" width="500"/></a></p>

<p>&#160;&#160;&#160; 123456789987654321一些电影电视剧，为了剧情的需要，会时常出现一些暴力、情色和少儿不易的镜头，加上我们国内影片未分级，又有着严格的审查制度，所以就会经常出现一些因故被删除的影视片段，而这些片段基本上都不会在国内看到。《武侠》激情戏被删原因：因剧情需要 据悉，《武侠》大约在影片开场20分钟左右，汤唯和甄子丹在浴室里讨论着要不要用鱼鳔，或者食水银来避孕的私房话，但下一个镜头却非常突兀地转移了，那是因为其中有一场激情戏被删了。据导演陈可辛称，“删戏只有一个目的，看其是否为剧情主线服务，不管激情或不激情，再有卖点的戏，若偏离了轨道，一样得删。”面对记者追问，陈可辛强调《武侠》是其个人的独立创作，只因剧情而删，无关其他。</p>
<a href="54034_33799649_3.htm"><img src="http://img1.dayoo.com/photo/attachement/jpg/site1/20131202/001372af745c140666e100.jpg" width="500"/></a>
<a href="54034_33799649_4.htm">2222222222222222222222222222222222222222221</a>
<p>0612845无关其他00000。</p>
<p>00000无关其他。</p>
<p>11111无关其他。</p>
<p>22222无关其他。</p>
<p>33333无关其他。</p>
<p>44444无关其他。</p>
<p>55555无关其他。</p>
<p>66666无关其他。</p>
<p>77777无关其他。</p>
<p>88888无关其他。</p>
<p>99999无关其他。</p>
<p>12345无关其他。</p>
<p>54321无关其他。</p>


<p align="center"></p>
</div>';


		 
	
	$string=strip_tags($string, '<b><a><img><p><center><div><table><tr><td><th><strong><pagination>');//去除非b、a、img、p的HTML标签
		$string=preg_replace('/style=".*?"/','', $string);//去除所有style属性
		$string=preg_replace("/>[　|\xc2\xa0| ]+?/u",">",$string);//去除空格
		$string=str_replace(array('&#160;','&#12288;','&nbsp;'),'',$string);
		$string=preg_replace("/<pagination>/","<!--pagination-->",$string);//去除空格
		/*去除图片链中的href 待优化*/
		preg_match_all('/<a.*href="([^"]+)".*><img/i',$string,$urlMath);
		print_r($urlMath);exit;
		if(isset($urlMath[1])){
			$string=str_replace($urlMath[1],'javascript:void(0)',$string);
		}
	
	 echo $string;
		
?>
                                