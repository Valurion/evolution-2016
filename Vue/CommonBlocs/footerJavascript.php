<?php
// ce fichier est modifié dynamiquement à la release pour ne contenir d'un include unique.
?>
<script>
    window.IS_CAIRN_INT = false;
</script>
<script type="text/javascript" src="./static/js/jquery.min.js"></script>
<script type="text/javascript" src="./static/js/jquery-ui-1.9.2.custom.min.js"></script>
<script type="text/javascript" src="./static/js/jquery.cookie.min.js"></script>
<script type="text/javascript" src="./static/js/modernizr.js"></script>
<script type="text/javascript" src="./static/js/jquery.tipTip.minified.js"></script>
<script type="text/javascript" src="./static/js/cairn.js"></script>
<script type="text/javascript" src="./static/js/ajax.js"></script>
<script type="text/javascript" src="./static/js/verif.js"></script>
<script type="text/javascript" src="./static/js/require.js"></script>
<script type="text/javascript" src="./static/js/autocomplete_research.js"></script>
<script type="text/javascript" src="./static/js/owl.carousel.min.js"></script>
<script type="text/javascript" src="./static/js/jquery.nice-select-custom.js"></script>
<script src="./static/js/clipboard.min.js"></script>

<!-- Wicked Good XPath eneables XPath evaluation for HTML documents in every browser -->
<script type="text/javascript" src="./static/js/wgxpath.install.js"></script>
<script type="text/javascript">wgxpath.install();</script>

<!-- Formulaire Toggle Button -->
<script type="text/javascript">
var autoClose;
$('#search-option-button').click(function() {
	// Ouverture / fermeture du panneau
	$('.so-panel').toggle(function() {
		// Les options sont visibles
		if($('.so-panel').is(':visible')) {
			// Déclenchemenu d'un événement dans X secondes
			// Si on quitte le bouton et/ou la boite
			$('#search-option-button img').mouseleave(function() { autoClose = setTimeout(function() { $(".so-panel").hide() }, 2000); });
			$('.so-panel').mouseleave(function() { autoClose = setTimeout(function() { $(".so-panel").hide() }, 2000); });

			// Si on retourne sur le bouton et/ou la boite
			$('#search-option-button img').mouseenter(function() { clearTimeout(autoClose); autoClose = null; });
			$('.so-panel').mouseenter(function() { clearTimeout(autoClose); autoClose = null; });
		}
	})
})
</script>
