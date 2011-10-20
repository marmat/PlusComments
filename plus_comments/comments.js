var scriptPath = ""; // Will be filled automatically

function renderComments() {
	// Get the location of this script in order to determine the location
	// of the PHP script which will be called to get the comments
	scriptTags = document.getElementsByTagName('script');

	for (var i = 0; i < scriptTags.length; i++) {
		// Try to match the src parameter with a RegEx in order to get
		// the relative location of the file
		matches = /(.*)comments\.js$/g.exec(scriptTags[i].src);
		if (matches != null) {
			scriptPath = matches[1];
		}
	}

	// Grab all elements of the class "comments" and fetch their contents
	commentSections = document.getElementsByClassName("comments");

	for (var i = 0; i < commentSections.length; i++) {
		getComments(commentSections[i]);
	}
}

function getComments(target) {
	target.innerHTML = "Fetching comments...";
	var ajax = new XMLHttpRequest();
	ajax.open("GET", scriptPath + "plus_comments.php?activityId=" + target.id, true);
	ajax.onreadystatechange = function(){
		if (ajax.readyState == 4) {
			target.innerHTML = ajax.response;
		}
	}
	ajax.send(null);
}

// Compatibility script for older IE versions, taken from 
// Wikipedia:XMLHttpRequest
if (typeof XMLHttpRequest == "undefined")
	XMLHttpRequest = function () {
	try { return new ActiveXObject("Msxml2.XMLHTTP.6.0"); }
	catch (e) {}
	try { return new ActiveXObject("Msxml2.XMLHTTP.3.0"); }
	catch (e) {}
	try { return new ActiveXObject("Microsoft.XMLHTTP"); }
	catch (e) {}
	//Microsoft.XMLHTTP points to Msxml2.XMLHTTP and is redundant
	throw new Error("This browser does not support XMLHttpRequest.");
};

// The following is due to laziness taken from 
// http://onlinetools.org/articles/unobtrusivejavascript/chapter4.html
function addEvent(obj, evType, fn){ 
	if (obj.addEventListener){ 
		obj.addEventListener(evType, fn, false); 
		return true; 
	} else if (obj.attachEvent){ 
		var r = obj.attachEvent("on"+evType, fn); 
		return r; 
	} else { 
		return false; 
	} 
}

addEvent(window, "load", renderComments);