<!DOCTYPE html>
<html lang="en">
<head>
  	<meta charset="UTF-8"/>

  	<title>Hello, world!</title>
	<meta name="viewport" content="width=device-width,initial-scale=1"/>
	<meta name="description" content=""/>
	<link rel="stylesheet" type="text/css" href="style.css"/>
	<link rel="icon" href="favicon.png">
</head>
<body>
	<?php
	require 'Parsedown.php';
	$GLOBALS['parsedown'] = new Extension();
	$GLOBALS['parsedown']->setMarkupEscaped(false);

	$GLOBALS['pagesPath'] = 'pages/';
	$GLOBALS['requestUri'] = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	
	$pageName = substr($GLOBALS['requestUri'], 1);
	$pageHtml = get_page_html($pageName);
	$pagePath = get_page_path($pageName);

	# construct tree directory
	$rootFiles = glob($GLOBALS['pagesPath'] . '*');

	### HEADER
	echo '<header>';
	echo '	<h1><a href="/">intellego</a></h1>';
	echo '</header>';

	### NAVBAR
	echo '<nav>';
	# recursive folders
	if($pagePath != 'null')
	{
		$dirs = explode('/', $pagePath);
		$currentPath = '';
	
		for ($i = 0; $i < count($dirs) - 1; $i++) {
			$currentPath .= $dirs[$i] . '/';
			$branchFiles = glob($currentPath . '*');
	
			echo '	<ul>';
			foreach ($branchFiles as $file) {
				if (is_dir($file)) {
					echo '<li><a href="' . pathinfo($file)['filename'] . '-fdi' . '">>' . pathinfo($file)['filename'] . '</a></li>';
				}
			}
			echo '	</ul>';
		}
	}
	# page directories links
	echo '	<ul>';
	$branchFiles = glob($pagePath . '*');
	foreach ($branchFiles as $file) {
		$fileName = pathinfo($file)['filename'];
		if (!is_dir($file)) {
			if($fileName == $pageName)
				echo '<li><a class="selected" href="' . $fileName . '">' . $fileName . '</a></li>';
			else
				echo '<li><a href="' . $fileName . '">' . $fileName . '</a></li>';
		}
	}
	echo '	</ul>';
	echo '</nav>';

	### MAIN
	echo '<main>';
	echo $pageHtml;
	echo '</main>';

	### FOOTER
	echo '<footer>';

	# arvelie date
	$epoch = new DateTime('2025-01-14');
	$now = new DateTime();
	$elapsed = $now->diff($epoch);

	$arvelieDay = $elapsed->d % 14;
	$arvelieYear = floor($elapsed->d / 364);
	$arvelieMonth = floor(($elapsed->d / 14)) - ($elapsed->y * 26);
	$alphabet = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

	echo '	<p>';
	echo '<strong>' . str_pad($arvelieYear, 2, "0", STR_PAD_LEFT) . $alphabet[$arvelieMonth] . str_pad($arvelieDay, 2, "0", STR_PAD_LEFT) . '</strong>';
	echo ' arvelie time since epoch ' . $epoch->format('Y-m-d') . '.';
	echo '	</p>';
	echo '</footer>';

	function get_page_html($name)
	{
		$pages = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($GLOBALS['pagesPath']));
		foreach ($pages as $page) {
			if(!is_dir($page) && str_contains($page, $name)) {
				$text = file_get_contents($page);
				$html = $GLOBALS['parsedown']->text($text);
				return $html;
			}
		}

		# check if the page requested is marked as a folder
		if(str_contains($name, '-fdi')) {
			return "<h1>" . substr($name, 0, strlen($name) - 4) . " folder</h1>\n<p>This folder doesn't contain a valid index file marked with \"-fdi\".";
		}

		return '<p>404 - Page not found</p>';
	}

	function get_page_path($name)
	{
		$pages = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($GLOBALS['pagesPath']));

		foreach ($pages as $page) {
			if(!is_dir($page) && str_contains($page, $name)) {
				return pathinfo($page)['dirname'] . '/';
			}
		}

		return 'null';
	}
	?>
</body>
</html>
