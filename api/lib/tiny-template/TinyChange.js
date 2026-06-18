/*
TinyTemplate is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/tiny-template/blob/master/LICENSE
*/
var TinyChange = (function(){
	function remove(elem, id = 'clone') {
		elem = findclone(elem,id);
		elem.parentNode.removeChild(elem);
	}
	function next(elem, id = 'clone') {
		elem = findclone(elem,id);
		var next = elem.nextElementSibling;
		if(!next || next.dataset[id] === undefined) return;
		swap(elem,next);
	}
	function prev(elem, id = 'clone') {
		elem = findclone(elem,id);
		var prev = elem.previousElementSibling;
		if(!prev || prev.dataset[id] === undefined) return;
		swap(prev,elem);
	}
	function swap(first, second){
		var second_parent = second.parentNode;
		var following = second.nextSibling;
		first.parentNode.insertBefore(second, first);
		if(following) second_parent.insertBefore(first, following);
		else second_parent.appendChild(first);
	}
	function findclone(elem, id) {
		if(elem.dataset[id] !== undefined) {
			return elem;
		}
		return findclone(elem.parentElement, id);
	}

	var exportobj = {
		remove: remove,
		next: next,
		prev: prev,
	};

	return exportobj;
})();
