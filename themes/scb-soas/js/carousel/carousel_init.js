    $(document).ready(function() {   
    $('[data-toggle="tooltip"]').tooltip();
    $("#owl-example").owlCarousel({
		items:8,
		loop:true,
		autoPlay : 5000,
		margin:0,
		navigation:true,
		navigationText: [
		"<img src='/themes/scb-soas/images/chevron_left1600.png' alt='' height='70px'/>",
		"<img src='/themes/scb-soas/images/chevron_right1600.png' alt='' height='70px' />"
		]
	}
	); 
    });
