$(document).ready(function(){
	$('.nav.prev').on("click", function(e){
		var query_params = Pagination.getQueryParams();
		if (query_params.page == "1") return false;;
		if (query_params.page === undefined) query_params.page = 1;
		else query_params.page = parseInt(query_params.page, 10) - 1;

		var per_page = parseInt($('#per_page').val(), 10);
		if (per_page === 0 || isNaN(per_page)) per_page = 20;
		query_params.per_page = per_page;

		var query_string = Pagination.makeQueryString(query_params);
		location.href = location.origin + location.pathname + query_string;
	});
	$('.nav.next').on("click", function(e){
		var query_params = Pagination.getQueryParams();
		if (query_params.page === undefined) query_params.page = 2;
		else query_params.page = parseInt(query_params.page, 10) + 1;

		var per_page = parseInt($('#per_page').val(), 10);
		if (per_page === 0 || isNaN(per_page)) per_page = 20;
		query_params.per_page = per_page;

		var query_string = Pagination.makeQueryString(query_params);
		location.href = location.origin + location.pathname + query_string;
	});
});


var Pagination = {
	getQueryParams: function() {
		var query_params = {};
		var query = window.location.search.substring(1);
		var vars = query.split("&");
		for (var i=0;i<vars.length;i++) {
		    var pair = vars[i].split("=");
		    // If first entry with this name
		    if (typeof query_params[pair[0]] === "undefined") {
			    query_params[pair[0]] = decodeURIComponent(pair[1]);
		        // If second entry with this name
		    } else if (typeof query_params[pair[0]] === "string") {
		    	var arr = [ query_params[pair[0]],decodeURIComponent(pair[1]) ];
		    	query_params[pair[0]] = arr;
		        // If third or later entry with this name
		    } else {
		    	query_params[pair[0]].push(decodeURIComponent(pair[1]));
		    }
		} 
		return query_params;
	},
	makeQueryString: function(params) {
		var query_string = '?';
		for (key in params) {
			if (!params.hasOwnProperty(key)) continue;
			query_string += key + '=' + params[key] + '&';
		}
		return query_string.slice(0, -1);
	}

};

$(document).ready(function(){
	$('.filter_search').on("click", function(e){
		var query_params = Pagination.getQueryParams();

		var path_search = $('#path_search').val();
		var count_range_min = parseInt($('#count_range_min').val(), 10);
		var count_range_max = parseInt($('#count_range_max').val(), 10);

		query_params.path_search = path_search;
		query_params.count_range_min = !isNaN(count_range_min) ? count_range_min : '';
		query_params.count_range_max = !isNaN(count_range_max) ? count_range_max : '';

		var query_string = Pagination.makeQueryString(query_params);
		location.href = location.origin + location.pathname + query_string;
	});
});