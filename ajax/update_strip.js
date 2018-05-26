/* JavaScript functions for AJAX functionality in strip editing */

var xmlHttp;
var scripturl = "ajax/ajax.php";

function showPanelInfo(stripId,panelId) {
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null)
	{
	  alert ("Your browser does not support AJAX!");
	  return;
	}
	var action = "getpanelinfo";
	var params = "&panel_strip="+stripId+"&panel_ID="+panelId;
	var sid = Math.random();
	var url = scripturl+"?action="+action+params+"&sid="+sid;
	xmlHttp.onreadystatechange = stateChanged;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}
function stateChanged() {
	if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete") { 
		var form = '<form onsubmit="updatePanelInfo(this); return false;">'+xmlHttp.responseText+"</form>";
		document.getElementById("panel_info_element").innerHTML=form;
	}
}

function reloadComic(url) {
	document.getElementById("comic_object").data = url;
}

function getPanelInfo() {
}

function updatePanelInfo(form) {
	//alert("update panel");
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null)
	{
	  alert ("Your browser does not support AJAX!");
	  return;
	}
	var action = "edit_panel";
	var params = "";
	with (form) { for (e in elements) {
		params += "&"+elements[e].name+"="+elements[e].value;
	} }
	var url = scripturl+"?action="+action+params;
	xmlHttp.onreadystatechange = stateChanged;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function GetXmlHttpObject()
{
	var xmlHttp=null;
	try {
	 // Firefox, Opera 8.0+, Safari
	 xmlHttp=new XMLHttpRequest();
	} catch (e) {
	 //Internet Explorer
	 try {
	  xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
	 } catch (e) {
	  xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
	 }
	}
	return xmlHttp;
}
