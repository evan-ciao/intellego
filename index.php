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
	$parsedown = new Parsedown();

	$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	
	$pagesPath = 'pages/';
	
	# get all files in pages folder
	$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pagesPath));
	$dirslist = [];
	$fileslist = [];
	foreach ($files as $file) {
		if ($file->isDir()) { $dirslist[] = $file->getPathname(); }
		$fileslist[] = $file->getPathname();
	}

	### HEADER
	echo '<header>';
	echo '	<h1>intellego</h1>';
	echo '</header>';

	### NAVBAR
	echo '<nav>';
	echo '	<ul>';
	echo '		<li><a href="/">home</a></li>';
	echo '	</ul>';
	echo '</nav>';

	### MAIN
	echo '<main>';

	switch ($requestUri) {
		case '/':
			# load homepage
			$homeText = file_get_contents($pagesPath . 'home.md');
			$html = $parsedown->text($homeText);
			echo $html;
			break;
		
		default:
			# load requested page
			# get page name from url route request
			$pageName = substr($requestUri, 1);
			$pageName .= '.md'; 

			# load note
			foreach ($fileslist as $file) {
				if (str_contains($file, $pageName))
				{
					$noteText = file_get_contents($file);
					$html = $parsedown->text($noteText);
					echo $html;
					break 2;
				}
			}
			
			$notfoundText = file_get_contents($pagesPath . 'notfound.md');
			$html = $parsedown->text($notfoundText);
			echo $html;

			break;
	}

	echo '</main>';

	### FOOTER
	echo '<footer>';
	echo '	<p>footer</p>';
	echo '</footer>';
	?>
</body>
</html>
