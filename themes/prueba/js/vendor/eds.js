var serviceName = 'Articles and more';
var authtype = 'ip,cookie,shib';
var custid = 's8438947';
var groupid = 'main';
var profile = 'eds';
var searchUrl = 'http://search.ebscohost.com/login.aspx?authtype='+authtype+'&custid='+custid+'&groupid='+groupid+'&profile='+profile+'&direct=true&type=0&site=eds-live&bquery=';
var loginUrl = 'http://search.ebscohost.com/login.aspx?authtype='+authtype+'&custid='+custid+'&groupid='+groupid+'&profile='+profile;

$(document).ready(function() {

	$('#searchForm>ul.nav.nav-tabs').append('<li id="edsurl"><a href="javascript:searchEds();">'+serviceName+'</a></li>');
	
});


function searchEds(){
	console.log('trying this');
	var search = encodeURIComponent($('#searchForm_lookfor').val());
	if(search == '') {
		window.open(loginUrl);		
	}
	else{
		window.open(searchUrl+search);	
	}
}
