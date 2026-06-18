/*
TinyTemplate is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/tiny-template/blob/master/LICENSE
*/
var TinyTemplate = (function(){
	function fill(element, data){
		if(!element || !element.dataset) return;
		data = data || {};
		function fill_element(inst){
			inst = inst.split(':');
			if(typeof data[inst[1]] != 'undefined'){
				if(inst[0].substring(0,5)=='data-'){
					this.dataset[inst[0].substring(5)] = data[inst[1]];
				} else if(inst[0] == 'value'){
					this.value =  data[inst[1]];
				} else if(inst[0] == 'content'){
					this.textContent = data[inst[1]];
				} else if(inst[0] == 'src'){
					this.src = data[inst[1]];
				} else if(inst[0] == 'id'){
					this.id = data[inst[1]];
				} else if(inst[0] == 'checked'){
					this.checked = data[inst[1]];
				} else if(inst[0] == 'radio'){
					this.checked = data[inst[1]] == this.value;
				} else if(inst[0] == 'class'){
					var className = (this.dataset.tmplClassPrefix ? this.dataset.tmplClassPrefix + ' ' : '') + data[inst[1]];
					if(this.className.baseVal) this.className.baseVal = className;
					else this.className = className;
				} else if(inst[0] == 'href'){
					this.setAttribute('href',(this.dataset.tmplHrefPrefix ? this.dataset.tmplHrefPrefix : '') + data[inst[1]]);
				}
			}
		}
		if(element.dataset.tmpl) element.dataset.tmpl.split(',').forEach(fill_element, element);
		var elems = element.querySelectorAll('[data-tmpl]');
		for(var i = 0; i < elems.length; i++){
			elems[i].dataset.tmpl.split(',').forEach(fill_element,elems[i]);
		}
	}

	function clone_template(template, data){
		var content = template.content ? template.content : template;
		var node = content.firstElementChild ? content.firstElementChild : content.firstChild;
		var clone = document.importNode(node, true);
		fill(clone, data);
		if(!(data && data.disable_random_radio)){
			var radios = clone.querySelectorAll('input[type=radio]');
			if(radios.length > 0){
				var converter = {};
				for(var i = 0; i < radios.length; i++){
					var name = radios[i].name;
					if(!converter[name]) converter[name] = 'radio_'+random_string(7);
					var labels = clone.querySelectorAll('label[for="'+radios[i].id+'"]');
					var newid = converter[name]+':'+radios[i].value;
					for(var j = 0; j < labels.length; j++){
						labels[j].setAttribute('for', newid);
					}
					radios[i].id = newid;
					radios[i].name = converter[name];
				}
			}
		}
		if(!(data && data.disable_random_checkbox)){
			var checkboxes = clone.querySelectorAll('input[type=checkbox]');
			if(checkboxes.length > 0){
				var converter = {};
				for(var i = 0; i < checkboxes.length; i++){
					var name = checkboxes[i].id;
					if(!converter[name]) converter[name] = 'checkbox_'+random_string(7);
					var labels = clone.querySelectorAll('label[for="'+checkboxes[i].id+'"]');
					for(var j = 0; j < labels.length; j++){
						labels[j].setAttribute('for', converter[name]);
					}
					checkboxes[i].id = converter[name];
				}
			}
		}
		if(data && data.randomize_name_index){
			var inputs = clone.querySelectorAll('[name$="]"]');
			if(inputs.length > 0){
				var converter = {};
				var regexp = new RegExp('([^[]+)\\[([^\\]]+)\]');
				for(var i = 0; i < inputs.length; i++){
					var name = inputs[i].name.match(regexp);
					var name_index = name[2];
					if(!converter[name_index]) converter[name_index] = 'rni_'+random_string(7);
					inputs[i].name = name[1]+'['+converter[name_index]+']';
				}
			}
		}
		return clone;
	}

	function activate(name, data, elem, do_not_insert){
		if(!name) return;
		data = data || {};
		elem = elem || document;
		var template = elem.querySelector('template[data-tmpl-name="'+name+'"]');
		if(!template) return;
		var clone = clone_template(template, data);
		if(!do_not_insert) template.parentNode.insertBefore(clone, template);
		return clone;
	}

	function random_string(){
		return Math.random().toString(36).substr(2, 5);
	}

	return {
		'activate': activate,
		'fill': fill
	}
})()
