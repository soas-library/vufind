var serviceName = 'Online resources/articles'; 
var authtype = 'ip,cookie,shib';
var custid = 's8438947';
var groupid = 'main';
var profile = 'eds';
var searchUrl = 'http://search.ebscohost.com/login.aspx?authtype='+authtype+'&custid='+custid+'&groupid='+groupid+'&profile='+profile+'&direct=true&type=0&site=eds-live&bquery=';
var loginUrl = 'http://search.ebscohost.com/login.aspx?authtype='+authtype+'&custid='+custid+'&groupid='+groupid+'&profile='+profile;

$(document).ready(function() {

        $('#searchForm>ul.nav.nav-tabs').append('<li id="edsurl"><a class="searchOff" href="javascript:searchEds();">'+serviceName+'</a></li>');
$('#searchForm>ul.nav.nav-tabs').append('<li id="archiveurl"><a id="archivelink" class="searchOff" href="javascript:searchArchives();">Archives -beta</a></li>');
});


function searchEds(){
        console.log('trying this');
        var search = encodeURIComponent($('#searchForm_lookfor').val());

        if(search !="Undefined" && search !="undefined" && search == '') {
		window.open('http://search.ebscohost.com/login.aspx?authtype=ip,cookie,shib&custid=s8438947&groupid=main&profile=eds&direct=true&type=0&site=eds-live&bquery='+search);
	        //window.open(loginUrl);
        }
        else{
		 window.open('http://search.ebscohost.com/login.aspx?authtype=ip,cookie,shib&custid=s8438947&groupid=main&profile=eds');
             // window.open(searchUrl+search);
        }
}
function searchArchives(){
        var search = encodeURIComponent($('#searchForm_lookfor').val());
        window.open(location.protocol+'/Search/Results?type=AllFields&filter%5B%5D=~collection%3A%22SOAS+Archive%22&sort=callnumber#Collection', "_self");
}


