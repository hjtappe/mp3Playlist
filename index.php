<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">

<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="expires" content="0" />

	<meta name="language" content="de" />
	<title>Liste der Aufnahmen</title>
	<style type="text/css">
#playlist,audio{background:#888888;width:400px;padding:20px;}
.active a{color:#5DB0E6;text-decoration:none;}
li a{color:#eeeedd;background:#333;padding:5px;display:block;}
li a:hover{text-decoration:none;}
</style>
    <script type="text/javascript" src="js/jquery-1.12.4.min.js"></script>
    <script type="text/javascript">
var audio;
var playlist;
var tracks;
var current;

$(document).ready(function() {
  init();
});
function init(){
    current = 0;
    audio = $("audio");
    playlist = $("#playlist");
    tracks = playlist.find("li a");
    len = tracks.length - 1;
    audio[0].volume = .10;
    audio[0].play();
    playlist.find("a").click(function(e){
        e.preventDefault();
        link = $(this);
        current = link.parent().index();
        run(link, audio[0]);
    });
    audio[0].addEventListener("ended",function(e){
        current++;
        if(current == len){
            current = 0;
            link = playlist.find("a")[0];
        }else{
            link = playlist.find("a")[current];    
        }
        run($(link),audio[0]);
    });
}
function run(link, player){
        player.src = link.attr("href");
        par = link.parent();
        par.addClass("active").siblings().removeClass("active");
        audio[0].load();
        audio[0].play();
}
</script>
</head>
<body>
    <audio id="audio" preload="auto" tabindex="0" controls="" type="audio/mpeg">

    </audio>
<?php
// TODO Check for a valid REFERRER

// TODO If found, start a session and save a value in the session

// Show a list which streams the file to the browser. No display of direct URLs.
showList();

// If the session contains the value, allow a download / stream


function showList()
{
	$active = ' class="active"';
	print("<ul id=\"playlist\">\n");
	foreach (getFiles() as $entry) {
		$textfile = "../Archiv/".basename($entry, ".mp3").".txt";
		print('  <li'.$active.'><a href="'.$_SERVER['SCRIPT_NAME']."?d=".$entry.'">');
		if (file_exists($textfile)) {
			readfile($textfile);
		} else {
			print($entry);
		}
		print('</a></li>'."\n");
		$active = "";
	}
	print("</ul>\n");
}

// TODO: Download handler.

function getFiles($path = '../Archiv')
{
	$entries = array();
	// Open the path set
	if ($handle = opendir($path)) {

		// Loop through each file in the directory
		while ( false !== ($file = readdir($handle)) ) {
			// Remove the . and .. directories
			if ( $file == "." || $file == ".." ) {
				continue;
			}

			// Check to see if the file is a directory
			if( is_dir($path.'/'.$file) ) {
				continue;
			} else {
				// Get the information about the file
				$fileInfo = pathinfo($file);

				// Set multiple extension types that are allowed
				$allowedExtensions = array('mp3');

				// Check to ensure the file is allowed before returning the results
				if( in_array($fileInfo['extension'], $allowedExtensions) ) {
					array_push($entries, $file);
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
?>
	</body>
</html>
