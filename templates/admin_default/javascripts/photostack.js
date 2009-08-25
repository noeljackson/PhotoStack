//
//==========================================
// Check All boxes
//==========================================
function CheckAll(fmobj)
{
	for (var i=0;i<fmobj.elements.length;i++)
	{
		var e = fmobj.elements[i];
		if ((e.name != 'allbox') && (e.type=='checkbox') && (!e.disabled))
		{
			e.checked = fmobj.allbox.checked;
		}
	}
}

//==========================================
// Check all or uncheck all?
//==========================================
function CheckCheckAll(fmobj)
{
	var TotalBoxes = 0;
	var TotalOn = 0;
	for (var i=0;i<fmobj.elements.length;i++)
	{
		var e = fmobj.elements[i];
		if ((e.name != 'allbox') && (e.type=='checkbox'))
		{
			TotalBoxes++;
			if (e.checked)
			{
				TotalOn++;
			}
		}
	}

	if (TotalBoxes==TotalOn)
	{
		fmobj.allbox.checked=true;
	}
	else
	{
		fmobj.allbox.checked=false;
	}
}


var linkArray = new Array();
function disablelinks(){

var objLink = document.getElementById("album_list").getElementsByTagName("a");
for(var i=0;i < objLink.length;i++) {
linkArray[i] = objLink[i].href.toString();
if (objLink[i].id=='dwA1') {
//this just allows me to skip some
}
else {
objLink[i].disabled=true;
objLink[i].style.cursor="move";
objLink[i].onclick = new Function("return false;");
}
}
}
function enablelinks() {
var objLink = document.getElementById("album_list").getElementsByTagName("a");
for(var i=0;i < objLink.length;i++) {
objLink[i].disabled=false;
objLink[i].style.cursor="normal";
objLink[i].href=linkArray[i];
objLink[i].onclick = linkArray[i];
}
}
