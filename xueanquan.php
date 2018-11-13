<?php
	function trimstring($str) {
		$lpos = strpos($str,'"');
		$rpos = strrpos($str,'"');
		return substr($str,$lpos+1,$rpos-$lpos-1);
	}
	function json_prepare($str) {
		if(preg_match('/\w:/', $str)) $str = preg_replace('/(\w+):/is', '"$1":', $str);
		$str = str_replace('(','',$str);
		$str = str_replace(');','',$str);
		$str = str_replace('"https"','https',$str);
		$str = str_replace('"http"','http',$str);
		$str = str_replace('/','\/',$str);
		$str = str_replace("'",'"',$str);
		return $str;
	}
	function xmsafetreelogin($username,$password) {
		for ($retry = 1;$retry <= 3;$retry ++) {
			$CurrentTime = explode(" ",microtime(false));
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL,'https://fujianlogin.xueanquan.com/LoginHandler.ashx?userName='. $username .'&password=' . $password . '&checkcode=&type=login&loginType=1&r=' . $CurrentTime[0] . '&_='  . $CurrentTime[1]);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_COOKIEJAR,$GLOBALS['cookie']);
			$getdata = curl_exec($ch);
			if (curl_getinfo($ch,CURLINFO_HTTP_CODE) == 200) break;
		}
		if ($retry == 4) return false;
		if (strpos($getdata,'ret:1') === false) return false;
		else return json_decode(json_prepare($getdata),true);
	}
	function _xmsafetreetopicsign($specialId) {
		//完成观看验证
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://xiamen.xueanquan.com/Topic/topic/platformapi/api/v2/records/sign?callback=&step=1&specialId='. $specialId .'&prvName=&cityName=&_='.sprintf("%d",microtime(true)*1000));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $GLOBALS['cookie']);
		$getdata = curl_exec($ch);
		if (curl_getinfo($ch,CURLINFO_HTTP_CODE) != 200) return false;
		//提交第二步(居然不交答卷直接调用完成API就行)
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://xiamen.xueanquan.com/Topic/topic/platformapi/api/v2/records/sign?callback=&step=2&specialId='.$specialId .'&prvName=&cityName=&_='.sprintf("%d",microtime(true)*1000));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $GLOBALS['cookie']);
		$getdata = curl_exec($ch);
		if (curl_getinfo($ch,CURLINFO_HTTP_CODE) != 200) return false;
		return true;
	}
	function xmsafetreetopicsign($specialId) {
		for ($retry = 1;$retry <= 3;$retry ++) {
			$stat = _xmsafetreetopicsign($specialId);
			if ($stat) break;
		}
		if ($retry == 4) return false;
		else return true;
	}
	function _xmsafetreeholidaysign($sportYear,$semester) {
		$CurrentTime = explode(" ",microtime(false));
		//完成观看验证
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://xiamen.xueanquan.com/WebApi/Holiday/FinishWork?jsoncallback=&r='.$CurrentTime[0].'&sportYear='.$sportYear.'&semester='. $semester .'&workStep=1&_='.sprintf("%d",microtime(true)*1000));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $GLOBALS['cookie']);
		$getdata = curl_exec($ch);
		if (curl_getinfo($ch,CURLINFO_HTTP_CODE) != 200) return false;
		//提交第二步(居然不交答卷直接调用完成API就行)
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://xiamen.xueanquan.com/WebApi/Holiday/FinishWork?jsoncallback=&r='.$CurrentTime[0].'&sportYear='.$sportYear.'&semester='. $semester .'&workStep=2&_='.sprintf("%d",microtime(true)*1000));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $GLOBALS['cookie']);
		$getdata = curl_exec($ch);
		if (curl_getinfo($ch,CURLINFO_HTTP_CODE) != 200) return false;
		return true;
	}
	function xmsafetreeholidaysign($sportYear,$semester) {
		for ($retry = 1;$retry <= 3;$retry ++) {
			$stat = _xmsafetreeholidaysign($sportYear,$semester);
			if ($stat) break;
		}
		if ($retry == 4) return false;
		else return true;
	}
	function _xmsafetreeanquanzuoye($gid,$li) {
		//获取视频信息
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,'https://xiamen.xueanquan.com/JiaTing/EscapeSkill/SeeVideo.aspx?gid=' . $gid . '&li=' . $li);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_COOKIEFILE,$GLOBALS['cookie']);
		$htmldata = curl_exec($ch);
		//假装看了视频 {
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, 'https://xiamen.xueanquan.com/jiating/ajax/FamilyEduCenter.EscapeSkill.SeeVideo,FamilyEduCenter.ashx?_method=SkillCheckName&_session=rw');
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_POST,1);
		$lpos = strpos($htmldata,'SeeVideo.SkillCheckName(');
		$rpos = strpos(substr($htmldata,$lpos+24),')');
		$data = explode(',',substr($htmldata,$lpos+24,$rpos));
		$postdata = 
			'videoid=' . trimstring($data[0]) . "\r\n" .
			'gradeid=' . trimstring($data[1]) . "\r\n" .
			'courseid=' . trimstring($data[2]) . "\r\n"
			;
		curl_setopt($ch,CURLOPT_POSTFIELDS,$postdata);
		curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type: text/plain;charset=UTF-8','Referer: https://xiamen.xueanquan.com/JiaTing/EscapeSkill/SeeVideo.aspx?gid=' . $gid .'&li=' . $li,'Origin: https://xiamen.xueanquan.com',)); 
		curl_setopt($ch,CURLOPT_COOKIEFILE,$GLOBALS['cookie']);
		$getdata = curl_exec($ch);
		//假装看了视频 }
		//提交答案
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, 'https://xiamen.xueanquan.com/jiating/ajax/FamilyEduCenter.EscapeSkill.SeeVideo,FamilyEduCenter.ashx?_method=TemplateIn2&_session=rw');
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_POST,1);
		$lpos = strpos($htmldata,'res = SeeVideo.TemplateIn2(');
		$rpos = strpos($htmldata,' ).value;');
		$data = explode(',',substr($htmldata,$lpos+27,$rpos-$lpos-27));
		//var_dump($data);
		$postdata = 
			'workid='      . trimstring($data[0]) . "\r\n" .
			'fid='         . trimstring($data[1]) . "\r\n" .
			'title='       . trimstring($data[2]) . "\r\n" .
			'require='     . "\r\n" .
			'purpose='     . "\r\n" .
			'contents='    . "\r\n" .
			'testwanser='  . '0|0|0' . "\r\n" .
			'testinfo='    . '已掌握技能' . "\r\n" .
			'testMark='    . '100' . "\r\n" .
			'testReulst='  . '1' . "\r\n" .
			'SiteName='    . "\r\n" .
			'siteAddrees=' . "\r\n" .
			'watchTime='   . "\r\n" .
			'CourseID='    . trimstring($data[13]) . "\r\n"
			;
		curl_setopt($ch,CURLOPT_POSTFIELDS,$postdata); 
		curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type: text/plain;charset=UTF-8','Referer: https://xiamen.xueanquan.com/JiaTing/EscapeSkill/SeeVideo.aspx?gid=' . $gid .'&li=' . $li,'Origin: https://xiamen.xueanquan.com',)); 
		curl_setopt($ch, CURLOPT_COOKIEFILE, $GLOBALS['cookie']);
		$getdata = curl_exec($ch);
		return $getdata;
	}
	function xmsafetreeanquanzuoye($gid,$li) {
		for ($retry = 1;$retry <= 3;$retry ++) {
			$stat = _xmsafetreeanquanzuoye($gid,$li);
			if ($stat == "'1'" || $stat == "'4'") break;
		}
		if ($retry == 4) return false;
		else return true;
	}
	function _xmsafetreemobilelogin() {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://xiamen.xueanquan.com/safeapph5/api/safeEduCardinalData/activeUser?uderId=-1&_='.sprintf("%d",microtime(true)*1000));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $GLOBALS['cookie']);
		$getdata = curl_exec($ch);
		if (curl_getinfo($ch,CURLINFO_HTTP_CODE) != 200) return false;
		return true;
	}
	function xmsafetreemobilelogin() {
		for ($retry = 1;$retry <= 3;$retry ++) {
			$stat = _xmsafetreemobilelogin();
			if ($stat) break;
		}
		if ($retry == 4) return false;
		else return true;
	}
	error_reporting(0);
	$GLOBALS['cookie'] = md5(microtime(true)).'.cookie';
	if (empty($argv[1])) $accfile = 'acc.txt';
	else $accfile = $argv[1];
	$file = file_get_contents($accfile);
	$file = explode("\n",$file);
	$errorlist = array();
	foreach($file as $each) {
		$username = trim($each);
		if ($username == "") continue;
		echo $username . ':';
		$userinfo = xmsafetreelogin($username,'123456');
		$stat = false;
		//var_dump($userinfo);
		if (!($userinfo === false)) {
			$stat = true;
			
			xmsafetreeanquanzuoye('832','1350');
			xmsafetreeanquanzuoye('832','1351');
			xmsafetreeanquanzuoye('832','1355');
			xmsafetreeanquanzuoye('832','1356');
			xmsafetreetopicsign('235');
		}
		if ($stat) echo "OK\n";
		else {
			array_push($errorlist,$username);
			echo "Error\n";
		}
	}
	if (count($errorlist) != 0) {
		echo "-----Error List BEGIN-----\n";
		$buf = "";
		foreach ($errorlist as $name) $buf .= $name . "\n";
		echo $buf;
		file_put_contents($accfile.'.err',$buf);
		echo "-----Error List  END -----\n";
		echo "Error username saved to ".$accfile.'.err'."\n";
	}
	unlink($GLOBALS['cookie']);
?>
