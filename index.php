<?php
// Start the session
session_start();

/**
 * Directory to use for search and download
 *
 * @return string
 */
function searchDirectory()
{
	$dir = ".";
	// read a configuration file if it exists.
	if (file_exists("config.inc.php")) {
		require_once 'config.inc.php';
	}
	if (isset($config['searchDir'])) {
		$dir = $config['searchDir'];
	}

	$dir = preg_replace('/\/*$/', "", $dir);
	return $dir;
}

/**
 * Regular expression for auto-validated access.
 *
 * @return string
 */
function allowedRedirect()
{
	if (isset($config['redirRegex'])) {
		return $config['redirRegex'];
	} else {
		return '/No Redirect Registered/';
	}
}

/**
 * Cookie Name
 *
 * @var string
 */
define("SESSION_COOKIE_NAME", "mp3PlaylistSessionCookie");


/**
 * Show the HTML header.
 */
function show_header($location = "")
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">

<head>
<?php
	if (isset($location) && $location != "") {
		print "\t<meta http-equiv=\"refresh\" content=\"".$location."\">\n";
	}
?>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="expires" content="0" />

	<meta name="language" content="de" />
	<title>Liste der Aufnahmen</title>
	<link rel="stylesheet" type="text/css" href="css/style.css"></link>
    <script type="text/javascript" src="js/jquery-1.12.4.min.js"></script>
    <script type="text/javascript" src="js/playlistHandler.js"></script>
</head>
<body>
<?php
}

/**
 * Show the audio interface
 */
function show_audio()
{
?>
	<div class="download">
	<div>
   	<audio
    	id="audio"
		preload="auto"
		controls
		volume="1.0"
		type="audio/mp3"></audio>
	<img src="img/noloop.svg" id="loopimage" class="" alt="Loop All" />
	</div>
	<div>
		<a href="#" id="download" download target="_blank">
			<img src="img/download.svg" alt="Download" class="download" />
		Download: <span id="info"></span>
		</a>
	</div>
	</div>
<?php
}

// Setup the session.
session_name(SESSION_COOKIE_NAME);

// Check session and / or referrer.
if (!isset($_SESSION['validated']) || ("true" != $_SESSION['validated'])) {
	if (($_SERVER["SERVER_ADDR"] == "127.0.0.1") ||
			($_SERVER["LOCAL_ADDR"] == "127.0.0.1") ||
			(isset($_SERVER["HTTP_REFERER"]) &&
			preg_match(allowedRedirect(), $_SERVER["HTTP_REFERER"]))) {
		$_SESSION['validated'] = "true";
	#} else {
	#	print ("<!--".$_SERVER["HTTP_REFERER"]."-->\n");
	}
}

// Show the list for download after successful validation
if (isset($_SESSION['validated']) && ("true" == $_SESSION['validated'])) {
	// Check if this is a download request
	if (isset($_REQUEST['f'])) {
		// If the session contains the value, allow a download / stream
		downloadFile($_REQUEST['f'], searchDirectory());
	} else {
		// Show a list which streams the file to the browser. No display of direct URLs.
		show_header();
		show_audio();
		showList(searchDirectory());
		show_footer();
	}
} else {
	show_header("10; https://www.stadtmission-mainz.de/egroupware/index.php");
	show_rules();
	show_footer();
}

/**
 * Show a list of recordings
 * 
 * @param string $directory
 */
function showList($directory)
{
	$files = getMp3Files($directory, $directory);
	
	$active = ' class="active"';
	if (count($files) < 1) {
		print "<p>No files found.</p>\n";
	} else {
		print("<ul id=\"playlist\">\n");
		foreach ($files as $entry) {
			$textfile = $directory.DIRECTORY_SEPARATOR.basename($entry, ".mp3").".txt";
			$fileId = filename_obfuscate($entry);
			$url = $_SERVER['SCRIPT_NAME']."?f=".$fileId;
			$mp3info = NULL;
			if (function_exists(id3_get_tag)) {
				$mp3info = id3_get_tag($entry);
			}
			$info = "";
			if (file_exists($textfile)) {
				$info = file_get_contents($textfile);
				$info = preg_replace("/\r*\n *$/", "", $info);
				$info = preg_replace("/\r*\n/", " | ", $info);
			} elseif (is_array($mp3info) && count($mp3info) > 0) {
				if (isset($mp3info["title"])) {
					$info .= $mp3info["title"]."\n";
				}
				if (isset($mp3info["artist"])) {
					$info .= " | ".$mp3info["artist"]."\n";
				}
				if (isset($mp3info["comment"])) {
					$info .= " | ".$mp3info["comment"]."\n";
				}
			} else {
				$info = $entry;
			}
			print('  <li'.$active.'>'."\n");
			print('    <a href="'.$url.'" download target="_blank">'."\n");
			print('	     <img src="img/download.svg" alt="Download '.
				$info.'" class="download" />'."\n");
			print('    </a>'."\n");
			print('    <a href="'.$url.'" id="'.$fileId.'" class="soundfile">'."\n");
			print($info."\n");
			print('    </a>'."\n");
			print('  </li>'."\n");
			$active = "";
		}
		print("</ul>\n");
	}
}

/**
 * Obfuscate a file name.
 * 
 * @param string $filename
 * @return string
 */
function filename_obfuscate($filename)
{
	$encrypted = base64_encode($filename);
	// Replace trailing = by number / count
	$tail = preg_replace('/=*$/', "", $encrypted);
	$tail = strrev($tail);
	$encrypted = $tail.strtoupper(chr(strlen($encrypted) - strlen($tail) + ord("A")));

	return $encrypted;
}

/**
 * Decrypt a file name from a request.
 * 
 * @param string $filename
 * @return NULL|string
 */
function filename_decrypt($filename)
{
	if (strlen($filename) < 2) {
		// echo "filename too short\n";
		return NULL;
	}
	$count = ord(substr($filename, -1)) - ord("A");
	$filename = substr_replace($filename, '', -1, 1);
	$filename = strrev($filename);
	for ($i = 0; $i < $count; $i ++) {
		$filename .= "=";
	}
	$decrypted = base64_decode($filename);

	return $decrypted;
}

/**
 * Provide the user with a download form.
 * 
 * @param string $filename
 * @param string $searchDirectory
 * @return boolean
 */
function downloadFile($filename, $searchDirectory)
{
	// Decrypt the file name.
	$filename = filename_decrypt($filename);
	$filename = $searchDirectory.DIRECTORY_SEPARATOR.$filename;
	// Check that file exists
	// Check that the file has the correct extension. Re-use global value.
	if (file_exists($filename)) {
		// Get the information about the file
		$fileInfo = pathinfo($filename);
		
		// Check to ensure the file is allowed before returning the results
		if ($fileInfo['extension'] == "mp3") {
			// Return file.
			// the file name of the download
			$public_name = basename($filename);

			// get the file's mime type to send the correct content type header
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime_type = finfo_file($finfo, $filename);
			
			// send the headers
			header("Content-Type: ".$mime_type);
			header("Content-Disposition: attachment; filename=\"".$public_name."\";");
			header('Content-Length: '.filesize($filename));
			header('Content-Transfer-Encoding: binary');
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache"); //keeps ie happy
			
			// stream the file
			$fp = fopen($filename, 'rb');
			ob_end_clean(); //required here or large files will not work
			fpassthru($fp);
		} else {
			header("Unsupported Media Type", true, 415);
			return;
		}
	} else {
		header("Not Found", true, 404);
		return;
	}
}

/**
 * Get the list of files
 * 
 * @param string $path
 * @param string $searchDirectory
 */
function getMp3Files($path, $searchDirectory)
{
	$entries = array();
	// Open the path set
	if ($handle = opendir($path)) {

		// Loop through each file in the directory
		while (false !== ($filename = readdir($handle))) {
			// Remove the . and .. directories
			if ($filename == "." || $filename == "..") {
				continue;
			}

			// Check to see if the file is a directory
			if (is_dir($path.DIRECTORY_SEPARATOR.$filename)) {
				// do not run recursively.
				continue;
			} else {
				// Get the information about the file
				$fileInfo = pathinfo($path.DIRECTORY_SEPARATOR.$filename);

				// Check to ensure the file has the mp3 extension
				// before returning the results
				if ($fileInfo['extension'] == "mp3") {
					$filename = substr($path.DIRECTORY_SEPARATOR.$filename, strlen($searchDirectory.DIRECTORY_SEPARATOR));
					array_push($entries, $filename);
				}
			}
		}
		
		// Close the handle
		closedir($handle);

		sort($entries);
		$entries = array_reverse($entries);
	} else {
		print("<p>Error reading directory ".$path."</p>\n");
	}
	return $entries;
}

/**
 * Show the download rules
 */
function show_rules()
{
?>
<div>Die Dateien sind nur Nutzern, die aus dem internen Bereich kommen zugänglich.
Bitte zunächst anmelden und dort den Links zurück zu dieser Seite folgen.</div>
<?php
}

/**
 * Show the HTML footer
 */
function show_footer()
{
?>
	</body>
</html>
<?php
}
?>
