/* global wc, infinitepayBlocksData */
( function () {
	var registerPaymentMethod = wc.wcBlocksRegistry.registerPaymentMethod;
	var createElement         = wp.element.createElement;
	var decodeEntities        = wp.htmlEntities.decodeEntities;
	var data                  = window.infinitepayBlocksData || {};

	var InfinitePayLabel = function ( props ) {
		return createElement(
			'span',
			{ style: { display: 'flex', alignItems: 'center', gap: '8px' } },
			data.icon
				? createElement( 'img', { src: data.icon, alt: 'InfinitePay', style: { maxHeight: '24px' } } )
				: null,
			decodeEntities( data.title || 'InfinitePay' )
		);
	};

	var InfinitePayContent = function () {
		return createElement( 'p', null, decodeEntities( data.description || '' ) );
	};

	registerPaymentMethod( {
		name:           'infinitepay_checkout',
		label:          createElement( InfinitePayLabel ),
		content:        createElement( InfinitePayContent ),
		edit:           createElement( InfinitePayContent ),
		canMakePayment: function () { return true; },
		ariaLabel:      data.ariaLabel || 'InfinitePay payment method',
		supports:       { features: data.supports || [ 'products' ] },
	} );
}() );
