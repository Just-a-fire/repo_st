<?php
require_once('constants.php');
require_once('functions.php');

$url = str_replace('\\', '/', $_REQUEST['url']);
$url = rtrim($url, '/') . '/';

$start = $_REQUEST['start'] . 'T00:00:00Z';
$end = $_REQUEST['end'] . 'T23:59:59Z';

$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$per_page = isset($_REQUEST['per_page']) ? $_REQUEST['per_page'] : 20;

$path_search = isset($_REQUEST['path_search']) ? $_REQUEST['path_search'] : '';
$count_range_min = isset($_REQUEST['count_range_min']) && intval($_REQUEST['count_range_min']) >= 0 ? $_REQUEST['count_range_min'] : '';
$count_range_max = isset($_REQUEST['count_range_max']) && intval($_REQUEST['count_range_max']) >= 1 ? $_REQUEST['count_range_max'] : '';

$access_token = isset($_REQUEST['access_token']) && strlen($_REQUEST['access_token']) === GITHUB_TOKEN_LENGTH
	? $_REQUEST['access_token']
	: '';

// $access_token = '7a0450af06cd2d4942909805084c6b894c44931c'; // TODO: del

if (empty($url)) die('Empty url.');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Статистика репозитория <?=$url?> с <?=$start?> по <?=$end?></title>
</head>
<!-- основной стиль всех страниц -->
<link rel="stylesheet" href="css/main.css">
<!-- стиль для таблицы с сортировкой -->
<link rel="stylesheet" href="themes/blue/style.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<!-- плагин для сортировки таблицы по столбцам -->
<script type="text/javascript" src="tablesorter/jquery.tablesorter.js"></script>
<script type="text/javascript" src="js/action.js"></script>
<script>
$(document).ready(function(){
	$("#myTable").tablesorter();
});
</script>
<style>
	.pagination input[type="number"], .tablesorter input[type="number"]{
		width: 42px;
		text-align: center;
	}
	.tablesorter .filter_search {
		color: #fff;
    	border: 0;
    	cursor: pointer;
    	padding: 4px;
		background-color: #1abc9c;
		border-radius: 3px;
	}
</style>
<body>
<h3>Период с <?=substr($start, 0, 10)?> по <?=substr($end, 0, 10)?> </h3>
<?php
$url_trees = GITHUB_API . 'repos/' . $url . 'git/trees/master?recursive=1';

$result = curlGetObj($url_trees, CURL_AGENT, $access_token);
$obj = json_decode($result, true);



if (!isset($obj['tree'])) {
	echo 'Что-то пошло не так ' . $url_trees . '<br>';
	print_r($obj);
	die();
}

$trees = $obj['tree'];
?>
<table id="myTable" class="tablesorter">
	<thead>
		<tr>
			<th>Файл</th>
			<th>Количество коммитов</th>
			<th>Авторы коммитов</th>
		</tr>
	</thead>
	<tbody>
<?php
$trees_counter = 0;
$trees_number_range_start = ($page - 1) * $per_page + 1;
$trees_number_range_end   = $page * $per_page;
foreach ($trees as $tree) {
	if ($tree['type'] === 'tree') continue; // папки не выводим
	if ($path_search != '')
		if (stripos($tree['path'], $path_search) === false) continue; // фильтр файлов

	$commits_url = GITHUB_API . 'repos/' . $url . 'commits?path=' . $tree['path'] . '&since=' . $start . '&until=' . $end;
	$res = curlGetObj($commits_url, CURL_AGENT, $access_token);
	$com = json_decode($res, true);
	$file_commits_count = count($com);

	if ($count_range_min != '') { // фильтр по количеству коммитов файла
		if ($file_commits_count < $count_range_min) continue;
	}
	if ($count_range_max != '') {
		if ($file_commits_count > $count_range_max) continue;
	}

	++$trees_counter;
	if ($trees_counter < $trees_number_range_start) continue;
	// if ($trees_counter > $trees_number_range_end) break;
	if ($trees_counter > $trees_number_range_end) continue;

	$authors_arr = array();
	if (is_array($com)) {
		foreach ($com as $key => $commit_obj) {
			$name = $commit_obj['commit']['author']['name'];
			if (isset($authors_arr[$name])) ++$authors_arr[$name];
			else $authors_arr[$name] = 1;
		}
	}
	echo '<tr><td>' . $tree['path'] . '</td><td class="number">' . $file_commits_count . '</td><td>' . getCommitsAuthorList($authors_arr) . '</td></tr>';
}
?>		
	</tbody>
	<tfoot>
		<tr>
			<th><input type="text" id="path_search" name="path_search" placeholder="Файл" value="<?=$path_search?>"></th>
			<th>От <input type="number" min="0" id="count_range_min" name="count_range_min" value="<?=$count_range_min?>"> 
				до <input type="number" min="1" id="count_range_max" name="count_range_max" value="<?=$count_range_max?>"></th>
			<th><button class="filter_search">Поиск</button></th>
		</tr>
	</tfoot>	
</table>

<p>Всего файлов <?=$trees_counter?></p>

<div class="pagination">
	<button class="nav prev" title="Предыдущая страница">&larr;</button>
	&nbsp;<?=$page?>&nbsp;
	<button class="nav next" title="Следующая страница">&rarr;</button>
	&nbsp;Элементов на странице <input type="number" min="1" max="100" id="per_page" name="per_page" value="<?=$per_page?>">
</div>

</body>
</html>