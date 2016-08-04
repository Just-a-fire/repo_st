<?php
require_once('../constants.php');
require_once('../functions.php');

$search = $_REQUEST['user'];
$access_token = isset($_REQUEST['access_token']) && strlen($_REQUEST['access_token']) === GITHUB_TOKEN_LENGTH
	? $_REQUEST['access_token']
	: '';

if (strlen($search) < GITHUB_LOGIN_MIN_LENGTH) {
	exit( json_encode(array('success' => '0', 'fail_description' => 'Too short search world')) );
}

$limit = 5;
$sort_by = 'followers';

$slash_count = substr_count($search, '/');

if ($slash_count === 0) { // поиск пользователей, сортировка по количеству подписчиков в порядке убывания
	$url_users = GITHUB_API . 'search/users?q=' . $search . '&per_page=' . $limit . '&sort=' . $sort_by . '&order=desc';	
	$result = curlGetObj($url_users, CURL_AGENT, $access_token);

	$obj = json_decode($result, true);

	$tool_tips = array();

	if (isset($obj['items'])) {
		foreach ($obj['items'] as $key => $value) {
			$tool_tips[] = $value['login'];
		}
		echo json_encode(array('success' => '1', 'tool_tips'=>$tool_tips));
	}
} else if ($slash_count === 1) {
	$pos = strpos($search, '/');
	if ($pos < GITHUB_LOGIN_MIN_LENGTH)
		exit( json_encode(array('success' => '0', 'fail_description' => 'Too short user name')) );

	list($username, $rer_name_start) = explode('/', $search);

	$url_user_repositories = GITHUB_API . "users/$username/repos";

	$result = curlGetObj($url_user_repositories, CURL_AGENT, $access_token);

	$obj = json_decode($result, true);

	$tool_tips = array();

	if (is_array($obj)) {
		foreach ($obj as $key => $value) {
			if (isset($value['full_name']) && strpos($value['full_name'], $search) === 0)
				$tool_tips[] = $value['full_name'];
		}
		echo json_encode(array('success' => '1', 'tool_tips'=>$tool_tips));
	}
} else {
	exit ( json_encode(array('success' => '0', 'fail_description' => 'Too many slashes count: ' . $slash_count)) );
}
?>