var audio;
var playlist;
var tracks;
var current;
var download;
var infospan;

$(document).ready(function() {
  init();
});
function init(){
    current = 0;
    audio = $("audio");
    playlist = $("#playlist");
	download = $("#download");
	infospan = $("#info");
    tracks = playlist.find("a.soundfile");
    audio[0].volume = 0.70;
	// Register the click functions
    playlist.find("a.soundfile").click(function(e){
        e.preventDefault();
        current = $(this).parent().index();
		update(playlist.find("a.soundfile")[current]);
        play();
    });
	// Manage the transitions in the loop checkbox
	$("#loopimage").click(function() {
		if ($('#loopimage').hasClass("checked")) {
        	$(this).attr('src','img/noloop.svg');
		} else {
        	$(this).attr('src','img/loop.svg');
		}
		$('#loopimage').toggleClass("checked")
    });
	// Continue and loop
    audio[0].addEventListener("ended", function(e){
		if ($('#loopimage').hasClass("checked")) {
        	current++;
        	if (current >= tracks.length){
        	    current = 0;
			}
			update(playlist.find("a.soundfile")[current]);
        	play();
		}
    });
	// Load the first track
	load(tracks[current].href, audio[0],
		tracks[current].text);
	// play immediately
    //play();
}
function update(a) {
		anchor = $(a);
        link = anchor.attr("href");
        load(link, audio[0], anchor.text());
        par = anchor.parent();
        par.addClass("active").siblings().removeClass("active");
}
function load(link, player, text){
        player.src = link;
		download[0].href = link;
		infospan[0].textContent = text;
        audio[0].load();
}
function play(){
        audio[0].play();
}
