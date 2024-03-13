let booksterCPSWDomReady = function(callback) {
	document.readyState === "interactive" || document.readyState === "complete" ? callback() : document.addEventListener("DOMContentLoaded", callback);
};

booksterCPSWDomReady(() => {
	let bookster_shortcode = document.getElementById('bookster-cpsw-shortcode');

	if(bookster_shortcode != null) {
		bookster_shortcode.addEventListener('click', booksterCPSWDomReady(bookster_shortcode));
		bookster_shortcode.addEventListener('focus', booksterCPSWDomReady(bookster_shortcode));
	}

	function booksterCPSWDomReady(item){ 
		navigator.clipboard.writeText(item.value).then(
			() => {
				alert('Shortcode copied to clipboard');
			}
		);
	}
});

