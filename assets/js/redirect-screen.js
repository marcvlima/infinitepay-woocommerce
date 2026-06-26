/* global InfinityPayRedirect */
document.addEventListener( 'DOMContentLoaded', function () {
	if ( typeof InfinityPayRedirect === 'undefined' ) {
		return;
	}

	var delay = ( InfinityPayRedirect.delay || 3 ) * 1000;
	var fill  = document.querySelector( '.infinitepay-progress-fill' );

	if ( fill ) {
		fill.style.animationDuration = ( InfinityPayRedirect.delay || 3 ) + 's';
	}

	setTimeout( function () {
		window.location.href = InfinityPayRedirect.url;
	}, delay );
} );
