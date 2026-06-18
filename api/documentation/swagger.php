<?php
declare(strict_types=1);

require_once __DIR__ . "/../header.inc.php";

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
		const api_url = params.get('urls.primaryName') || "{$root}/api/documentation";

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
