<?php
function curlGetObj($url, $agent, $token) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, $agent);
	curl_setopt($ch, CURLOPT_URL, $token ? $url . '&access_token=' . $token : $url);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

function getCommitsAuthorList($arr) {
	if (!is_array($arr))
		return '';
	$res_string = '';
	foreach ($arr as $key => $value) {
		$res_string .= $key . ': ' . $value . '<br>';
	}
	return $res_string;
}
?>