<?php
function pagestart($title = '',$active = null) {
	BootSome::document($title ? $title.' - '.TITLE : TITLE,'da');
	
	head();
	BootSome::$body->at(['onload'=>"FancyFilter.set_option('path','".__ROOT__."/');"],true);
	navbar($active);
	
	$container = BootSome::$body->container(false);
	if(defined('NOTICE')) {
		if(file_exists(NOTICE)) {
			include NOTICE;
		}
	}
	$container->el('h1')->te($title);
	
	return $container;
}
function head() {
	BootSome::$head->link('shortcut icon',__ROOT__.'/favicon.ico');
	
	BootSome::$head->css(__ROOT__.'/lib/boot-some/BootSome.css?'.CACHE);
	if(!empty($_SESSION['darktheme']) && $_SESSION['darktheme']) {
		BootSome::$head->css(__ROOT__.'/lib/bootswatch/cyborg.min.css?'.CACHE);
	}
	BootSome::$head->css(__ROOT__.'/lib/fontawesome/css/fontawesome.min.css?'.CACHE);
	BootSome::$head->css(__ROOT__.'/lib/fontawesome/css/solid.min.css?'.CACHE);
	
	BootSome::$head->el('script',['src'=>__ROOT__.'/lib/boot-some/bootstrap.bundle.min.js?'.CACHE]);
	BootSome::$head->el('script',['src'=>__ROOT__.'/lib/ufo-ajax/ufo.js?'.CACHE]);
	BootSome::$head->el('script',['src'=>__ROOT__.'/lib/tiny-template/TinyTemplate.js?'.CACHE]);
	BootSome::$head->el('script',['src'=>__ROOT__.'/lib/fancy-filter/fancyfilter.js?'.CACHE]);
	BootSome::$head->el('script',['src'=>__ROOT__.'/lib/wild-file/WildFile.js?'.CACHE]);
	BootSome::$head->el('script',['src'=>__ROOT__.'/lib/boot-some/BootSome.js?'.CACHE]);
	BootSome::$head->el('script',['src'=>__ROOT__.'/lib/boot-some/BootSomeForms.js?'.CACHE]);
	BootSome::$head->el('script',['src'=>__ROOT__.'/lib/webtoolkit/base64.js?'.CACHE]);
}
function navbar($page) {
	$navbar = BootSome::$body->navbar(false,'navbar-light bg-light');

	$brand = $navbar->brand();
	$brand->img(__ROOT__.'/logo.svg','Logo');
	$brand->te(TITLE);


	$navbar->toggler();

	$collapse = $navbar->collapse();
	$nav = $collapse->nav();
	if(SimpleAuth::access('employee','basic')) {
		$nav->a(__ROOT__.'/case/','Workorder',$page=='case');
	}
	$dropdown = $nav->dropdown('Konto');
	$dropdown->a(__ROOT__.'/logout.script.php','Log af');

	
	$swaggerButton = $nav->el('button')->at([
		'type' => 'button', 
		'onclick' => "location.href='".__ROOT__."/api/swagger';", 
		'class' => 'btn btn-secodary'
	]);
	
	$swaggerButton->el('i')->at(['id' => 'themeIcon_2', 'class' => 'fa-solid fa-code']);
	
	
	$darkLightMode = $nav->el('button')->at([
		'id' => 'toggleTheme', 
		'type' => 'button', 
		'class' => 'btn btn-secodary'
	]);
	
	$darkLightMode->el('i')->at(['id' => 'themeIcon', 'class' => 'fa-solid']); // Start uden ikon, JS sætter det
	
	

$style = "

	:root {
		--background-color: #FFF;
		--text-color: #222;
		--navbar-bg-color: #f8f9fa;
		--navbar-text-color: #000;
	}

	body {
		background-color: var(--background-color);
		color: var(--text-color);
	}

	html[data-bs-theme='dark'] {
		--background-color: hsl(228, 5%, 15%) !important;
		--text-color: hsl(228, 5%, 80%) !important;
		--navbar-bg-color: #343a40 !important;
		--navbar-text-color: #fff !important;
	}

	html[data-bs-theme='light'] {
		--background-color: #FFF !important;
		--text-color: #222 !important;
		--navbar-bg-color: #f8f9fa !important;
		--navbar-text-color: #000 !important;
	}

	.navbar {
		background-color: var(--navbar-bg-color) !important;
		color: var(--navbar-text-color) !important;
	}

	.navbar a {
		color: var(--navbar-text-color) !important;
	}

	.table-responsive {
		background-color: var(--background-color) !important;
	}

	.table th, .table td {
		color: var(--text-color) !important;
	}

	html[data-bs-theme='dark'] .table th {
		background-color: #343a40 !important;
		color: #fff !important;
	}

	html[data-bs-theme='dark'] .table {
		background-color: #222 !important;
	}

	html[data-bs-theme='dark'] .table td {
		background-color: #333 !important;
		color: #fff !important;
	}

	html[data-bs-theme='dark'] .table tr {
		border: #242528 !important;
	}

	html[data-bs-theme='dark'] .card {
		background-color: #222 !important;
		color: #fff !important;
	}

	html[data-bs-theme='dark'] .card-header {
		background-color: #343a40 !important;
		color: #fff !important;
	}

	html[data-bs-theme='dark'] .dropdown-menu {
		background-color: #333 !important;
		color: #fff !important;
	}

	html[data-bs-theme='dark'] .dropdown-item:hover {
		background-color: var(--background-color) !important;
	}
	#themeIcon,
	#themeIcon_2,
	.navbar-toggler-icon {
		color: var(--navbar-text-color) !important;
	}
	.navbar-brand {
		color: var(--navbar-text-color) !important;
	}
	.modal-content {
		background-color: var(--background-color);  /* Mørk baggrund for kort */
		color: var(--navbar-text-color) !important;
	}

	html[data-bs-theme='dark'] .modal-header {
		background-color: var(--navbar-bg-color) !important;
		border-color: #555 !important;
	}
	html[data-bs-theme='dark'] .form-select,
	html[data-bs-theme='dark'] .form-control {
		background-color: #333 !important;
		color: #fff !important;
		border-color: #555 !important;
	}

	html[data-bs-theme='dark'] .form-select:focus {
		border-color: #aaa !important;
		box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25) !important;
	}

	html[data-bs-theme='dark'] ::placeholder {
		color: #bbb !important;  /* Lys grå farve for placeholder */
	}

	.navbar-nav .nav-link.active, 
	.navbar-nav .nav-item.active .nav-link, 
	.navbar-nav .nav-link.show {
		color: var(--bs-navbar-active-color);
		border-bottom: 2px solid var(--navbar-text-color) !important;
	}

	html[data-bs-theme='dark'] input[type='date'] {
		background-color: #333 !important;
		color: #fff !important;
		border-color: #555 !important;
	}

	html[data-bs-theme='dark'] input[type='date']:focus {
		border-color: #aaa !important;
		box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25) !important;
	}

	html[data-bs-theme='dark'] .page-link {
		background-color: #343a40 !important;
		color: #fff !important;
	}
";

$nav->el('style')->te($style);

	// JavaScript-funktion til åbning af slet-modal
	$jsFunction = <<<JS
	document.addEventListener("DOMContentLoaded", function () {
		const themeToggleButton = document.getElementById("toggleTheme");
		const themeIcon = document.getElementById("themeIcon");
		const htmlElement = document.documentElement;
	
		// Funktion til at ændre tema
		function setTheme(darkMode) {
			htmlElement.setAttribute("data-bs-theme", darkMode ? "dark" : "light");
			localStorage.setItem("theme", darkMode ? "dark" : "light");
			updateButtonIcon(darkMode);
		}
	
		// Funktion til at opdatere ikonet baseret på tema
		function updateButtonIcon(darkMode) {
			themeIcon.className = darkMode ? "fa-solid fa-sun" : "fa-solid fa-moon";
		}
	
		// Find systemets præference
		const savedTheme = localStorage.getItem("theme");
	
		// Brug gemt tema
		if (savedTheme) {
			setTheme(savedTheme === "dark");
		} else {
			setTheme(false);  // Standard tema er lyst
		}
	
		// Skift tema ved klik
		themeToggleButton.addEventListener("click", function () {
			const currentTheme = htmlElement.getAttribute("data-bs-theme");
			setTheme(currentTheme !== "dark");
		});
	});
	
	JS;
	BootSome::$head->el('script')->te($jsFunction);

}
function email_row($html,$ops,$id = null,$email = '') {
	$ops = 'email_'.$ops;
	$group = $html->form_horizontal();
	$group->label('E-mail',$ops.'['.$id.']');
	$ig = $group->inputgroup();
	$js = "this.parentElement.parentElement.parentElement.parentNode.removeChild(this.parentElement.parentElement.parentElement);";
	$ig->button(null,'ban','warning')->at(['onclick'=>$js]);
	$ig->input($ops.'['.$id.']',$email)->at(['required','type'=>'email']);
	$js = "window.location.href='mailto:'+this.parentElement.parentElement.getElementsByTagName('input')[0].value;";
	$ig->button('E-mail','envelope')->at(['onclick'=>$js]);
}
