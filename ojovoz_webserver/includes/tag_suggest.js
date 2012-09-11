var sug=0;

function ShowSuggestion(str,v) {
	if (str.length==0) { 
  		document.getElementById("Suggestions" + v).innerHTML="";
  		return;
  	}
	str_array = str.split(",")
	last_str = str_array[str_array.length-1]
	
	if (last_str.length==0) {
		document.getElementById("Suggestions" + v).innerHTML="";
  		return;
	}
	xmlHttp=GetXmlHttpObject()
	if (xmlHttp==null) {
	  return;
  	} 
	sug = v;
	var url="get_suggestion.php";
	url=url+"?q="+last_str;
	url=url+"&sid="+Math.random();
	xmlHttp.onreadystatechange=stateChanged();
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function GetXmlHttpObject() {
  var xmlHttp=null;
  try {
    // Firefox, Opera 8.0+, Safari
    xmlHttp=new XMLHttpRequest();
  }
  catch (e) {
    // Internet Explorer
    try {
      xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
    }
    catch (e) {
      	xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
  }
  return xmlHttp;
}

function stateChanged() { 
	if (xmlHttp.readyState==4) { 
		var element = "Suggestions"+sug
		if (xmlHttp.responseText.length > 0) {
			document.getElementById(element).innerHTML=" "+xmlHttp.responseText;
		} else {
			document.getElementById(element).innerHTML="";
		}
	}
}
