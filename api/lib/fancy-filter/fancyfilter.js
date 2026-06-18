var FancyFilter = (function(){
	var filter_prefix = 'ffilter_';
	var options = {};

	function read_cookie(filtername){
		var cookies = document.cookie.split(';');
		for(var i=0;i<cookies.length;i++){
			var cookie = cookies[i].trim().split('=');
			if(cookie[0]==filtername){
				try {
					return JSON.parse(decodeURIComponent(cookie[1]));
				} catch {
					console.log('Malformed cookie value:',cookie[1]);
				}
				break;
			}
		}
		return {};
	}

	var store_options = ['path','domain','max-age','expires','secure','samesite'];
	function write_cookie(name, value){
		if(Object.keys(value).length) {
			var cookie = name+'='+encodeURIComponent(JSON.stringify(value));
		}
		else {
			var cookie = name+'=;max-age=0';
		}
		for(var i=0;i<store_options.length;i++){
			if(options[store_options[i]]) cookie += ';'+store_options[i]+'='+options[store_options[i]];
		}
		document.cookie = cookie;
	}

	function set(filtername, key, value = undefined){
		filtername = filter_prefix+filtername;
		var cookie = read_cookie(filtername);
		if(typeof value == 'undefined' || value === ''){
			delete cookie[key];
		} else {
			cookie[key] = value;
		}
		write_cookie(filtername, cookie);
	}

	function toggle(filtername, key, value, set = true, defaults = []){
		filtername = filter_prefix+filtername;
		var cookie = read_cookie(filtername);
		if(typeof value == 'undefined'){
			delete cookie[key];
		} else {
			if(typeof cookie[key] == 'undefined') cookie[key] = defaults;
			const index = cookie[key].indexOf(value);
			if(set) {
				if(index==-1) cookie[key].push(value);
			}
			else {
				if(index!=-1) cookie[key].splice(index, 1);
			}
		}
		write_cookie(filtername, cookie);
	}

	function set_filter_prefix(prefix){
		filter_prefix = prefix;
	}

	function set_option(option, value){
		options[option] = value;
	}

	return {
		set:set,
		toggle:toggle,
		set_option:set_option,
		set_filter_prefix:set_filter_prefix
	}
})()
