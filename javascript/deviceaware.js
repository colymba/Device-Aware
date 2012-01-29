/**
 * Device Aware: jQuery addon for the SilverStripe module
 * Sends the current screen resolution to SilverStripe to be store in a Session
 * Used to create optimized version of the images displayed
 * 
 * Copyright (c) 2011, Thierry Francois / COLYMBA
 * Licensed under the MIT license
 * http://colymba.com
 * 
 * @package DeviceAware
 * 
 * @copyright Thierry Francois / COLYMBA 
 * @author thierry@colymba.com
 * 
 * @requires Jquery
 */
$(document).ready(function(){
    jQuery.get( 'device_aware/saveScreenResolution', {width: screen.width, height: screen.height} );
});
// non JQuery version
/*
function device_aware_save_resolution()
{
    var XBrowserXMLHttp = [
        function () {return new XMLHttpRequest()},
        function () {return new XDomainRequest()},
        function () {return new ActiveXObject("Msxml2.XMLHTTP")},
        function () {return new ActiveXObject("Msxml3.XMLHTTP")},
        function () {return new ActiveXObject("Microsoft.XMLHTTP")}
    ];
    var xmlhttp = false;
	for (var i=0;i<XBrowserXMLHttp.length;i++) {
		try {
			xmlhttp = XBrowserXMLHttp[i]();
		}
		catch (e) {
			continue;
		}
		break;
	}
	if (xmlhttp)
    {
        xmlhttp.open('GET','device_aware/saveScreenResolution?width='+screen.width+'&height='+screen.height,true);
        xmlhttp.setRequestHeader('User-Agent','XMLHTTP/1.0');
        xmlhttp.send(null);
    }
}
device_aware_save_resolution();*/