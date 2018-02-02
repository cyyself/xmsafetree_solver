<?php
	function trimstring($str) {
		$lpos = strpos($str,'"');
		$rpos = strrpos($str,'"');
		return substr($str,$lpos+1,$rpos-$lpos-1);
	}
	function xmsafetreelogin($username,$password) {
		for ($retry = 1;$retry <= 3;$retry ++) {
			$CurrentTime = explode(" ",microtime(false));
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL,'https://fujianlogin.safetree.com.cn/LoginHandler.ashx?userName='. $username .'&password=' . $password . '&checkcode=&type=login&loginType=1&r=' . $CurrentTime[0] . '&_='  . $CurrentTime[1]);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_COOKIEJAR,'safetree.cookie');
			$getdata = curl_exec($ch);
			if (curl_getinfo($ch,CURLINFO_HTTP_CODE) == 200) break;
		}
		if ($retry == 4) return false;
		if (strpos($getdata,'ret:1') === false) return false;
		else return true;
	}
	function _xmsafetreeanquanzuoye($gid,$li) {
		//获取视频信息
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,'https://xiamen.safetree.com.cn/JiaTing/EscapeSkill/SeeVideo.aspx?gid=' . $gid . '&li=' . $li);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_COOKIEFILE,'safetree.cookie');
		curl_setopt($ch,CURLOPT_COOKIEJAR,'safetree.cookie');
		$htmldata = curl_exec($ch);
		//假装看了视频 {
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, 'https://xiamen.safetree.com.cn/jiating/ajax/FamilyEduCenter.EscapeSkill.SeeVideo,FamilyEduCenter.ashx?_method=SkillCheckName&_session=rw');
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
		curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type: text/plain;charset=UTF-8','Referer: https://xiamen.safetree.com.cn/JiaTing/EscapeSkill/SeeVideo.aspx?gid=' . $gid .'&li=' . $li,'Origin: https://xiamen.safetree.com.cn',)); 
		curl_setopt($ch,CURLOPT_COOKIEFILE,'safetree.cookie');
		curl_setopt($ch,CURLOPT_COOKIEJAR,'safetree.cookie');
		$getdata = curl_exec($ch);
		//假装看了视频 }
		//提交答案
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, 'https://xiamen.safetree.com.cn/jiating/ajax/FamilyEduCenter.EscapeSkill.SeeVideo,FamilyEduCenter.ashx?_method=TemplateIn2&_session=rw');
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
		curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type: text/plain;charset=UTF-8','Referer: https://xiamen.safetree.com.cn/JiaTing/EscapeSkill/SeeVideo.aspx?gid=' . $gid .'&li=' . $li,'Origin: https://xiamen.safetree.com.cn',)); 
		curl_setopt($ch,CURLOPT_COOKIEFILE,'safetree.cookie');
		curl_setopt($ch,CURLOPT_COOKIEJAR,'safetree.cookie');
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
	function _xmsafetreezhuantihuodong($SpecialID) {
		$CurrentTime = explode(" ",microtime(false));
		//完成观看验证
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://xiamen.safetree.com.cn/WebApi/SpecialService/FinishWork?r=' . $CurrentTime[0] . '&SpecialID=' . $SpecialID . '&WorkStep=1&_=' . $CurrentTime[1]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_COOKIEFILE, "safetree.cookie");
		$getdata = curl_exec($ch);
		if (curl_getinfo($ch,CURLINFO_HTTP_CODE) != 200) return false;
		//提交第二步(居然不交答卷直接调用完成API就行)
		$CurrentTime = explode(" ",microtime(false));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://xiamen.safetree.com.cn/WebApi/SpecialService/FinishWork?r=' . $CurrentTime[0] . '&SpecialID=' . $SpecialID . '&WorkStep=2&_=' . $CurrentTime[1]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_COOKIEFILE, "safetree.cookie");
		$getdata = curl_exec($ch);
		if (curl_getinfo($ch,CURLINFO_HTTP_CODE) != 200) return false;
		return true;
	}
	function xmsafetreezhuantihuodong($SpecialID) {
		for ($retry = 1;$retry <= 3;$retry ++) {
			$stat = _xmsafetreezhuantihuodong($SpecialID);
			if ($stat) break;
		}
		if ($retry == 4) return false;
		else return true;
	}
	function _xmsafetreetopicsign($schoolYear,$semester,$specialId) {
		//完成观看验证
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://xiamen.safetree.com.cn/Topic/topic/platformapi/api/v2/Holiday/sign?callback=&schoolYear=' . $schoolYear . '&semester=' . $semester . '&step=1&specialId='. $specialId .'&prvName=&cityName=&_='.sprintf("%d",microtime(true)*1000));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_COOKIEFILE, "safetree.cookie");
		$getdata = curl_exec($ch);
		if (curl_getinfo($ch,CURLINFO_HTTP_CODE) != 200) return false;
		//提交第二步(居然不交答卷直接调用完成API就行)
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://xiamen.safetree.com.cn/Topic/topic/platformapi/api/v2/Holiday/sign?callback=&schoolYear=' . $schoolYear . '&semester=' . $semester . '&step=2&specialId='. $specialId .'&prvName=&cityName=&_='.sprintf("%d",microtime(true)*1000));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_COOKIEFILE, "safetree.cookie");
		$getdata = curl_exec($ch);
		if (curl_getinfo($ch,CURLINFO_HTTP_CODE) != 200) return false;
		return true;
	}
	function xmsafetreetopicsign($schoolYear,$semester,$specialId) {
		for ($retry = 1;$retry <= 3;$retry ++) {
			$stat = _xmsafetreetopicsign($schoolYear,$semester,$specialId);
			if ($stat) break;
		}
		if ($retry == 4) return false;
		else return true;
	}
	error_reporting(0);
	//你需要把学生的用户名放入acc.txt中，然后执行php safetree.php。
	//注意：多线程执行时请确保文件在不同文件夹内。该脚本会写入运行目录下的safetree.cookie，将产生干扰。
	$file = file_get_contents('acc.txt');
	$file = explode("\n",$file);
	$errorlist = array();
	foreach($file as $each) {
		$username = trim($each);
		if ($username == "") continue;
		echo $username . ':';
		$stat = false;
		if (xmsafetreelogin($username,'123456')) {
			$stat = true;
			$stat &= xmsafetreetopicsign('2018','1','132');
			/*
			$stat &= xmsafetreeanquanzuoye('809','822');
			$stat &= xmsafetreeanquanzuoye('809','821');
			$stat &= xmsafetreeanquanzuoye('809','815');
			$stat &= xmsafetreeanquanzuoye('809','814');
			*/
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
		file_put_contents("err.txt",$buf);
		echo "-----Error List  END -----\n";
		echo "Error username saved to err.txt\n";
	}
?>
