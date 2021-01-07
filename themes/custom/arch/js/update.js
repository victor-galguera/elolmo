jQuery(document).ready(function($) {
	var i = 1;
	$('.project-details .project-details-item').each(function() {
		if(i % 2 != 0){
			$(this).children('.row').children('.project-details-info').addClass('fadeInLeft');
			$(this).children('.row').children('.project-details-img').addClass('col-md-offset-4');
		}else{
			$(this).children('.row').children('.project-details-info').addClass('fadeInRight');
		}
		console.log(i);
		i++;
		
	});
});