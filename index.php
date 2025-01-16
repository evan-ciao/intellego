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
	$GLOBALS['parsedown'] = new Parsedown();

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
	echo '	<ul>';
	# root links
	foreach ($rootFiles as $file) {
		if (is_dir($file)) {
			echo '<li><a href="' . pathinfo($file)['filename'] . '-fdi' . '">>' . pathinfo($file)['filename'] . '</a></li>';
		}
	}
	echo '	</ul>';
	echo '	<ul>';
	# page directories links
	if($pagePath != '' && $pageName != '') {
		$branchFiles = glob($pagePath . '*');
		foreach ($branchFiles as $file) {
			if (is_dir($file)) {
				echo '<li><a href="' . pathinfo($file)['filename'] . '-fdi' . '">>' . pathinfo($file)['filename'] . '</a></li>';
			}
		}
		foreach ($branchFiles as $file) {
			$fileName = pathinfo($file)['filename'];
			if (!is_dir($file)) {
				if($fileName == $pageName)
					echo '<li><a class="selected" href="' . $fileName . '">' . $fileName . '</a></li>';
				else
					echo '<li><a href="' . $fileName . '">' . $fileName . '</a></li>';
			}
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
	echo '	<p>footer</p>';
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

		return '';
	}
	?>
</body>
</html>
