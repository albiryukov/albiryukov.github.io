jQuery(document).ready(function($) {
	$("#contact").submit(function() {
		var str = $(this).serialize();
		$.ajax({
			type: "POST",
			url: "../contact/mail.php",
			data: str,
			success: function(msg) {
				if(msg == 'OK') {
					result = '<div class="ok">Сообщение отправлено</div>';
					$("#fields").hide();
				}
				else {result = msg;}
				$('#note').html(result);
			}
		});
		return false;
	});
});