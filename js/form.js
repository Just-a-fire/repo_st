function clear_list(index) {
	if (index === undefined) {
		$("#tool-tip li").each(function(){
			$(this).removeClass("shown");
		});	
	} else {
		$("#tool-tip li").each(function(j){
			if (j >= index - 1)
			$(this).removeClass("shown");
		});	
	}			
}

$(document).ready(function(){
	// console.log($('#url').offset());
	$('#tool-tip').css('left', $('#url').offset().left);
	$('#tool-tip').css('top', $('#url').offset().top - $(document).scrollTop() + $('#url').innerHeight() - 5);

	$('#url').on("input", function(){

		$(this).val( $(this).val().replace(/^\/+/, '') ); // убирание лишних слешов вначале адреса

		var GITHUB_LOGIN_MIN_LENGTH = 3;
		var adress = $(this).val();

		if (adress.length < GITHUB_LOGIN_MIN_LENGTH) {
			clear_list();
			return false;
		}

		var slash_count = adress.split('/').length - 1;

		if (slash_count === 0) { // слешей нет, значит пока только пользователь
			var params = {user: adress};
			$.post('ajax/github_users.php', params, function(data) {
				console.log(data);
				if (data.length === 0) {
					clear_list();
					return false;
				}
				var data_array = JSON.parse(data);
				console.log(data_array);
				if (data_array.success == "1") {
					var tool_tips = data_array.tool_tips;					
					for (var i = 0; i < tool_tips.length; ++i) {
						if (tool_tips[i] != "") {
							$("#tool-tip li").eq(i).addClass("shown").html(tool_tips[i]);
						}							
					}
				}				
			});
		} else if (slash_count === 1) { // есть один слеш - набирается имя репозитория
			var params = {user: adress};
			$.post('ajax/github_users.php', params, function(data) {
				console.log(data);
				if (data.length === 0) {
					clear_list();
					return false;
				}
				var data_array = JSON.parse(data);
				console.log(data_array);
				if (data_array.success == "1") {
					var tool_tips = data_array.tool_tips;					
					for (var i = 0; i < tool_tips.length; ++i) {
						if (tool_tips[i] != "") {
							$("#tool-tip li").eq(i).addClass("shown").html(tool_tips[i]);
						}							
					}
				}				
			});
		} else {
			clear_list();
		}
	});

	$('#tool-tip').on("click", function(e){
		if (e.target.tagName == "B") {
		$("#url").val(e.target.parentNode.innerText + '/');
		} else {
			$("#url").val(e.target.innerText + '/');
		}
		
		clear_list();
	});
});	