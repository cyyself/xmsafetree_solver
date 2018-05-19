<?php
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
			xmsafetreemobilelogin();
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
