<?php
ini_set("allow_url_fopen", 1);

define('GITHUB_API', 'https://api.github.com/');
define('GITHUB_TOKEN_LENGTH', 40);

$url = str_replace('\\', '/', $_REQUEST['url']);
$url = rtrim($url, '/') . '/';

$start = $_REQUEST['start'] . 'T00:00:00Z';
$end = $_REQUEST['end'] . 'T23:59:59Z';

$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$per_page = isset($_REQUEST['per_page']) ? $_REQUEST['per_page'] : 20;

$access_token = isset($_REQUEST['access_token']) && strlen($_REQUEST['access_token']) === GITHUB_TOKEN_LENGTH
	? $_REQUEST['access_token']
	: '';

$access_token = '7a0450af06cd2d4942909805084c6b894c44931c'; // TODO: del

if (empty($url)) die('Empty url.');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Статистика репозитория <?=$url?> с <?=$start?> по <?=$end?></title>
</head>
<style>
	.main_table {
		border-collapse: collapse;
	}
	.main_table thead {
		background: #bbb;
	}
	.main_table tr {
		vertical-align: top;
	}
	.main_table td {
		border: 1px solid #ccc;
	}
	.main_table .number {
		text-align: right;
	}

	.pagination input[type="number"]{
		width: 42px;
		text-align: center;
	}
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script>
$(document).ready(function(){
	$('.nav.prev').on("click", function(e){
		var query_params = Pagination.getQueryParams();
		if (query_params.page == "1") return false;;
		if (query_params.page === undefined) query_params.page = 1;
		else query_params.page = parseInt(query_params.page, 10) - 1;

		var per_page = parseInt($('#per_page').val(), 10);
		if (per_page === 0) per_page = 20;
		query_params.per_page = per_page;

		var query_string = Pagination.makeQueryString(query_params);
		location.href = location.origin + location.pathname + query_string;
	});
	$('.nav.next').on("click", function(e){
		var query_params = Pagination.getQueryParams();
		if (query_params.page === undefined) query_params.page = 2;
		else query_params.page = parseInt(query_params.page, 10) + 1;

		var per_page = parseInt($('#per_page').val(), 10);
		if (per_page === 0) per_page = 20;
		query_params.per_page = per_page;

		var query_string = Pagination.makeQueryString(query_params);
		location.href = location.origin + location.pathname + query_string;
	});
});


var Pagination = {
	getQueryParams: function() {
		var query_params = {};
		var query = window.location.search.substring(1);
		var vars = query.split("&");
		for (var i=0;i<vars.length;i++) {
		    var pair = vars[i].split("=");
		    // If first entry with this name
		    if (typeof query_params[pair[0]] === "undefined") {
			    query_params[pair[0]] = decodeURIComponent(pair[1]);
		        // If second entry with this name
		    } else if (typeof query_params[pair[0]] === "string") {
		    	var arr = [ query_params[pair[0]],decodeURIComponent(pair[1]) ];
		    	query_params[pair[0]] = arr;
		        // If third or later entry with this name
		    } else {
		    	query_params[pair[0]].push(decodeURIComponent(pair[1]));
		    }
		} 
		return query_params;
	},
	makeQueryString: function(params) {
		var query_string = '?';
		for (key in params) {
			if (!params.hasOwnProperty(key)) continue;
			query_string += key + '=' + params[key] + '&';
		}
		return query_string.slice(0, -1);
	}

};
</script>
<body>

<?php

$commits_by_file = 'https://api.github.com/repos/mmanela/SnippetDesigner/commits?path=src/SnippetDesigner/StringConstants.cs';
$branches = 'https://api.github.com/repos/mmanela/SnippetDesigner/branches';

$url_commits = 'https://api.github.com/repos/Just-a-fire/rgk_test/commits?sha=master'; // &per_page=1

$agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';
// $ch = curl_init();
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch, CURLOPT_USERAGENT, $agent);
// curl_setopt($ch, CURLOPT_URL, $url_commits);
// $result = curl_exec($ch);
// curl_close($ch);
// // print_r($result);
// $obj = json_decode($result, true);

// echo "<pre>";
// print_r($obj);

// echo $obj_count = count($obj);

// for ($i=0; $i < $obj_count; $i++) { 
// 	echo $obj[$i]['commit']['author']['date'] . '<br>';
// }
?>
<h3>Период с <?=substr($start, 0, 10)?> по <?=substr($end, 0, 10)?> </h3>
<?php

$url_trees = GITHUB_API . 'repos/mmanela/SnippetDesigner/git/trees/master?recursive=1';
$url_trees = GITHUB_API . 'repos/' . $url . 'git/trees/master?recursive=1&access_token=7a0450af06cd2d4942909805084c6b894c44931c';

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, $agent);
curl_setopt($ch, CURLOPT_URL, $url_trees);
$result = curl_exec($ch);
curl_close($ch);
// print_r($result);
$obj = json_decode($result, true);

// echo "<pre>";
// print_r($obj);

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

if (!isset($obj['tree'])) {
	echo 'Что-то пошло не так ' . $url_trees . '<br>';
	print_r($obj);
	die();
}

$trees = $obj['tree'];

// $qwe = array();

?>
<table class="main_table">
	<thead>
		<tr>
			<td>Файл</td>
			<td>Количество коммитов</td>
			<td>Авторы коммитов</td>
		</tr>
	</thead>
	<tbody>
<?php
$trees_counter = 0;
$trees_number_range_start = ($page - 1) * $per_page + 1;
$trees_number_range_end   = $page * $per_page;
foreach ($trees as $tree) {
	if ($tree['type'] === 'tree') continue; // папки не выводим
	++$trees_counter;
	if ($trees_counter < $trees_number_range_start) continue;
	// if ($trees_counter > $trees_number_range_end) break;
	if ($trees_counter > $trees_number_range_end) continue;
	// https://api.github.com/repos/izuzak/pmrpc/commits?path=README.markdown&since=2013-08-01T17:41:23Z
	$commits_url = GITHUB_API . 'repos/' . $url . 'commits?path=' . $tree['path'] . '&since=' . $start . '&until=' . $end;
	// $commits_url = GITHUB_API . 'repos/' . $url . 'commits?path=' . $tree['path'] . '';
	// echo $commits_url . '<br>';
	$res = curlGetObj($commits_url, $agent, $access_token);
	$com = json_decode($res, true);
	// echo '<pre>';
	// print_r($com);
	// echo '</pre>';
	$authors_arr = array();
	if (is_array($com)) {
		foreach ($com as $key => $commit_obj) {
			$name = $commit_obj['commit']['author']['name'];
			if (isset($authors_arr[$name])) ++$authors_arr[$name];
			else $authors_arr[$name] = 1;
		}
	}
	// $rr = $com[0]['commit']['author']['name'];
	echo '<tr><td>' . $tree['path'] . '</td><td class="number">' . count($com) . '</td><td>' . getCommitsAuthorList($authors_arr) . '</td></tr>';
}
?>		
	</tbody>

	
</table>

<p>Всего файлов <?=$trees_counter?></p>

<div class="pagination">
	<button class="nav prev" title="Предыдущая страница">&larr;</button>
	&nbsp;<?=$page?>&nbsp;
	<button class="nav next" title="Следующая страница">&rarr;</button>
	&nbsp;Элементов на странице <input type="number" id="per_page" name="per_page" value="<?=$per_page?>">
</div>

</body>
</html>