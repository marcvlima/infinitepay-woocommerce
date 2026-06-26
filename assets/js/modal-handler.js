/* global InfinityPayModal */
var InfinityPayModalHandler = ( function () {
	var overlay = null;

	function open( checkoutUrl ) {
		overlay = document.createElement( 'div' );
		overlay.className = 'infinitepay-modal-overlay';
		overlay.innerHTML =
			'<div class="infinitepay-modal-inner">' +
				'<button class="infinitepay-modal-close" aria-label="Fechar">&times;</button>' +
				'<iframe src="' + checkoutUrl + '" class="infinitepay-modal-iframe" allowfullscreen></iframe>' +
			'</div>';

		document.body.appendChild( overlay );
		document.body.style.overflow = 'hidden';

		var iframe   = overlay.querySelector( 'iframe' );
		var loaded   = false;
		var fallback = setTimeout( function () {
			if ( ! loaded ) {
				close();
				openPopupOrRedirect( checkoutUrl );
			}
		}, 4000 );

		iframe.addEventListener( 'load', function () {
			loaded = true;
			clearTimeout( fallback );
		} );

		iframe.addEventListener( 'error', function () {
			clearTimeout( fallback );
			close();
			openPopupOrRedirect( checkoutUrl );
		} );

		overlay.querySelector( '.infinitepay-modal-close' ).addEventListener( 'click', function () {
			if ( window.confirm( InfinityPayModal.closeWarning ) ) {
				close();
			}
		} );

		window.addEventListener( 'message', onMessage );
	}

	function onMessage( event ) {
		if ( ! event.data || event.data.type !== 'infinitepay:payment_confirmed' ) {
			return;
		}
		close();
		window.location.href = InfinityPayModal.thankyouUrl;
	}

	function close() {
		if ( overlay && overlay.parentNode ) {
			overlay.parentNode.removeChild( overlay );
		}
		overlay = null;
		document.body.style.overflow = '';
		window.removeEventListener( 'message', onMessage );
	}

	function openPopupOrRedirect( url ) {
		var popup = window.open( url, 'infinitepay_checkout', 'width=800,height=600' );
		if ( ! popup ) {
			window.location.href = url;
		}
	}

	return { open: open, close: close };
}() );
