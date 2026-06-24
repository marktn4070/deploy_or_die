<?php
declare(strict_types=1);

require_once __DIR__ . "/../../header.inc.php";

$unpkgLink = "https://unpkg.com/swagger-ui-dist@5.30.1/";
$root = __ROOT__;

BootSome::document("Swagger UI" ? "Swagger UI" . " - " . TITLE : TITLE, "en");

BootSome::$head->css($unpkgLink . "swagger-ui.css");
BootSome::$head->css($unpkgLink . "index.css");

/* Hide "Send empty value" message under "not required" input fields */
BootSome::$head->el("style")->te(
<<<CSS
	.parameter__empty_value_toggle {
		display: none !important;
	}
	.try-out {
		display: none !important;
	}

	/* swagger-overrides.css (ingen CSS-variabler) */

	/* ---- darkreader - fallback / base (konkrete værdier) ---- */
	html {
		background-color: #181a1b !important;
	}
	html,
	iframe {
		color-scheme: dark !important;
	}
	html, body, input, textarea, select, button, dialog {
		background-color: #1b1d1e;
	}
	html, body, input, textarea, select, button {
		border-color: #736b5e;
		color: #e8e6e3;
	}
	a {
		color: #3391ff;
	}
	table {
		border-color: #545b5e;
	}
	mark {
		color: #e8e6e3;
	}
	::placeholder {
		color: #b2aba1;
	}
	input:-webkit-autofill,
	textarea:-webkit-autofill,
	select:-webkit-autofill {
		background-color: #404400 !important;
		color: #e8e6e3 !important;
	}
	* {
		scrollbar-color: #454a4d #202324;
	}
	::selection,
	::-moz-selection {
		background-color: #004daa !important;
		color: #e8e6e3 !important;
	}
	.vimvixen-hint {
		background-color: #684b00 !important;
		border-color: #9e7e00 !important;
		color: #d7d4cf !important;
	}
	#vimvixen-console-frame {
		color-scheme: light !important;
	}
	::placeholder {
		opacity: 0.5 !important;
	}
	#edge-translate-panel-body,
	.MuiTypography-body1,
	.nfe-quote-text {
		color: #e8e6e3 !important;
	}
	.gr-main-header {
		background-color: #1b4958 !important;
	}
	.tou-z65h9k,
	.tou-mignzq,
	.tou-1b6i2ox,
	.tou-lnqlqk {
		background-color: #181a1b !important;
	}
	.tou-75mvi {
		background-color: #0f3a47 !important;
	}
	.tou-ta9e87,
	.tou-1w3fhi0,
	.tou-1b8t2us,
	.tou-py7lfi,
	.tou-1lpmd9d,
	.tou-1frrtv8,
	.tou-17ezmgn {
		background-color: #1e2021 !important;
	}
	.tou-uknfeu {
		background-color: #432c09 !important;
	}
	.tou-6i3zyv {
		background-color: #245d70 !important;
	}
	div.mermaid-viewer-control-panel .btn {
		background-color: #181a1b;
		fill: #e8e6e3;
	}
	svg g rect.er.attributeBoxEven {
		fill: #004daa;
		fill-opacity: 0.8 !important;
	}
	svg g rect.er,
	svg g rect.er.entityBox,
	svg g rect.er.attributeBoxOdd,
	svg rect.er.relationshipLabelBox,
	svg g g.nodes rect,
	svg g g.nodes polygon {
		fill: #181a1b !important;
	}
	svg g rect.task {
		fill: #004daa !important;
	}
	svg line.messageLine0,
	svg line.messageLine1 {
		stroke: #e8e6e3 !important;
	}
	div.mermaid .actor {
		fill: #181a1b !important;
	}
	.mitid-authenticators-code-app > .code-app-container {
		background-color: white !important;
		padding-top: 1rem;
	}
	iframe#unpaywall[src$="unpaywall.html"] {
		color-scheme: light !important;
	}
	select {
		background-color: rgba(22, 22, 22, 0) !important;
	}
	body#tumblr {
		--darkreader-bg--secondary-accent: 31, 32, 34 !important;
		--darkreader-bg--white: 23, 23, 23 !important;
		--darkreader-text--black: 228, 224, 218 !important;
	}
	:host {
		border-color: #262a2b !important;
		--d2l-button-icon-background-color-hover: #262a2b !important;
		color: #e8e6e3 !important;
		background-color: #181a1b !important;
	}
	:host([_floating]) .d2l-floating-buttons-container {
		background-color: #181a1b !important;
		border-top-color: #262a2b !important;
		opacity: 0.88 !important;
	}
	.d2l-card {
		background: #181a1b !important;
		border-color: #262a2b !important;
	}
	.d2l-dropdown-content > div,
	.d2l-menu-item {
		background-color: #181a1b !important;
		border-radius: 10px !important;
	}
	.d2l-empty-state-simple,
	.d2l-button-filter > ul > li > a.vui-button,
	.d2l-tabs-layout {
		border-color: #262a2b !important;
	}
	.d2l-label-text:has(.d2l-button-subtle-content):hover,
	.d2l-label-text:has(.d2l-button-subtle-content):focus,
	.d2l-label-text:has(.d2l-button-subtle-content):active {
		background-color: #262a2b !important;
	}
	.d2l-navigation-centerer {
		color: inherit !important;
	}
	.d2l-input,
	.d2l-calendar-date,
	.d2l-htmleditor-container {
		background-color: #181a1b !important;
	}
	.d2l-collapsible-panel {
		border: 1px solid #262a2b !important;
		border-radius: 0.4rem !important;
	}
	.d2l-collapsible-panel-divider {
		border-bottom: 1px solid #262a2b !important;
	}
	.d2l-w2d-flex {
		border-bottom: 2px solid #262a2b !important;
	}
	.d2l-collapsible-panel scrolled,
	.d2l-collapsible-panel-header,
	.d2l-w2d-collection-fixed {
		background-color: #181a1b !important;
	}
	.d2l-loading-spinner-bg-stroke {
		stroke: #262a2b !important;
	}
	.d2l-loading-spinner-bg,
	.d2l-loading-spinner-wrapper svg path,
	.d2l-loading-spinner-wrapper svg circle {
		fill: #181a1b !important;
	}
	.d2l-twopanelselector-side.d2l-twopanelselector-side-sep,
	.d2l-le-TreeAccordionItem-anchor::before {
		background: #262a2b !important;
	}
	.swagger-ui .scheme-container {
		background-image: initial !important;
		background-color: #181a1b !important;
		box-shadow: rgba(0, 0, 0, 0.15) 0px 1px 2px 0px !important;
	}
	.swagger-ui,
	.swagger-ui .info .title,
	.swagger-ui .opblock-tag,
	.swagger-ui .opblock-tag small,
	.swagger-ui .opblock .opblock-summary-description,
	.swagger-ui .opblock .opblock-summary-operation-id,
	.swagger-ui .opblock .opblock-summary-path,
	.swagger-ui .opblock .opblock-summary-path__deprecated,
	.swagger-ui .tab li,
	.swagger-ui .opblock-description-wrapper p,
	.swagger-ui .opblock-external-docs-wrapper p,
	.swagger-ui .opblock-title_normal p,
	.swagger-ui .opblock .opblock-section-header h4,
	.swagger-ui .response-col_links,
	.swagger-ui .response-col_status,
	.swagger-ui .info h1,
	.swagger-ui .info h2,
	.swagger-ui .info h3,
	.swagger-ui .info h4,
	.swagger-ui .info h5,
	.swagger-ui .info li,
	.swagger-ui .info p,
	.swagger-ui .info table,
	.swagger-ui section.models h4,
	.swagger-ui .model-title,
	.swagger-ui .model,
	.swagger-ui .model .property.primitive {
		color: #bcb6ad !important;
	}

	.swagger-ui table thead tr td, .swagger-ui table thead tr th {
		border-bottom-color: rgba(117, 109, 96, 0.2) !important;
		color: #bcb6ad !important;
	}

	.swagger-ui .btn.execute {
		background-color: #185499 !important;
		border-top-color: #174e8f !important;
		border-right-color: #174e8f !important;
		border-bottom-color: #174e8f !important;
		border-left-color: #174e8f !important;
		color: #e8e6e3 !important;
	}

	.swagger-ui textarea {
		background-color: rgba(24, 26, 27, 0.8) !important;
		color: #bcb6ad !important;
	}

	.swagger-ui .opblock .opblock-section-header {
		background-color: rgba(24, 26, 27, 0.8) !important;
		box-shadow: rgba(0, 0, 0, 0.1) 0px 1px 2px !important;
	}

	.swagger-ui select {
		background-color: #1d1f20 !important;
		border-top-color: #746c60 !important;
		border-right-color: #746c60 !important;
		border-bottom-color: #746c60 !important;
		border-left-color: #746c60 !important;
		box-shadow: rgba(0, 0, 0, 0.25) 0px 1px 2px 0px !important;
		color: #bcb6ad !important;
	}

	.swagger-ui .opblock-body pre.microlight {
		background-color: #262a2b !important;
		color: #e8e6e3 !important;
	}

	.swagger-ui section.models.is-open h4 {
		border-bottom-color: rgba(117, 109, 96, 0.3) !important;
	}

	#swagger-ui .highlight-code .example.microlight code span[style*="rgb(162, 252, 162)"] {
		color: #8cfb8c !important;
	}
	#swagger-ui .highlight-code .example.microlight code span[style*="rgb(252, 194, 140)"] {
		color: #fcba7d !important;
	}
	#swagger-ui .highlight-code .example.microlight code span[style*="rgb211, 99, 99)"] {
		color: #d56a6a !important;
	}
	#swagger-ui .highlight-code .example.microlight code .hljs-attr {
		color: #e8e6e3 !important;
	}
	.swagger-ui input[type="email"], .swagger-ui input[type="file"], .swagger-ui input[type="password"], .swagger-ui input[type="search"], .swagger-ui input[type="text"], .swagger-ui textarea {
		background-color: #181a1b !important;
		border-top-color: #3b4042 !important;
		border-right-color: #3b4042 !important;
		border-bottom-color: #3b4042 !important;
		border-left-color: #3b4042 !important;
	}
	.swagger-ui .topbar .download-url-wrapper input[type="text"] {
		border-top-color: #538735 !important;
		border-right-color: #538735 !important;
		border-bottom-color: #538735 !important;
		border-left-color: #538735 !important;
	}

CSS
);

BootSome::$head->el("link", ["href" => $unpkgLink . "favicon-32x32.png", "rel" => "icon", "sizes" => "32x32"]);
BootSome::$head->el("link", ["href" => $unpkgLink . "favicon-16x16.png", "rel" => "icon", "sizes" => "16x16"]);

BootSome::$head->el("script", ["src" => $unpkgLink . "swagger-ui-bundle.js"]);
BootSome::$head->el("script", ["src" => $unpkgLink . "swagger-ui-standalone-preset.js"]);

$doc = BootSome::$body;

$doc->el("div", ["id" => "swagger-ui"]);

$doc->el("script")->te(
<<<JS
	window.onload = function() {
		const params = new URLSearchParams(window.location.search);
		const api_url = params.get('urls.primaryName') || "{$root}/api/documentation/index.php";

		window.ui = SwaggerUIBundle({
			url: api_url,
			dom_id: '#swagger-ui',
			deepLinking: true,
			presets: [
				SwaggerUIBundle.presets.apis,
				SwaggerUIStandalonePreset
			],
			plugins: [
				SwaggerUIBundle.plugins.DownloadUrl
			],
			layout: "StandaloneLayout",
		});

		// AUTO ENABLE "EXECUTE"
		setTimeout(function () {
			document.querySelectorAll(".opblock-summary").forEach(item => {
				if (item.parentElement.classList.contains("is-open")) {
					enableTryOut(item);
				}
				item.addEventListener("click", function() {
					setTimeout(function () {
						enableTryOut(item);
					}, 150);
				});
			});
			function enableTryOut(item) {
				const buttons = item.parentElement.getElementsByClassName("try-out__btn");
				if (buttons.length > 0) {
					if (!buttons[0].classList.contains("cancel")) {
						buttons[0].click();
					}
				}
			}
		}, 1500);

		// ---------------------------------------------------
		// Sæt automatisk tekst i auth-input når modal åbner
		// ---------------------------------------------------
		(function() {
			const tokenToSet = "CQ8ou1fHMXx8GkrFl7tABfVmS8ypj2944fFz5qjmDLIfahFr0b9QobP07pdv5hKi_";

			function setAuthValue(val) {
				const el = document.getElementById("auth-bearer-value");
				if (!el) return false;
				el.value = val;
				// Trigger events så frameworks/handlers opfanger ændringen
				el.dispatchEvent(new Event('input', { bubbles: true }));
				el.dispatchEvent(new Event('change', { bubbles: true }));
				return true;
			}

			// 1) Prøv med det samme (hvis modal allerede i DOM af en eller anden grund)
			setAuthValue(tokenToSet);

			// 2) Når top "Authorize" knap klikkes (modal oprettes/dannes), sæt værdien efter kort delay
			const topAuthBtn = document.querySelector(".auth-wrapper .btn.authorize, .btn.authorize.unlocked");
			if (topAuthBtn) {
				topAuthBtn.addEventListener("click", function() {
					setTimeout(function() {
						setAuthValue(tokenToSet);
					}, 50); // lille delay så modal DOM er på plads
				});
			}

			// 3) MutationObserver fallback: hvis input indsættes senere, sæt værdien og stop observer
			const observer = new MutationObserver(function() {
				if (setAuthValue(tokenToSet)) {
					observer.disconnect();
				}
			});
			observer.observe(document.body, { childList: true, subtree: true });
		})();
	};
JS
);
