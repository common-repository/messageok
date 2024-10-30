// Tab
jQuery( document ).on( 'click', '.msgok-nav-bar', function( e ) {
	jQuery( '.msgok-nav-bar' ).removeClass( 'nav-tab-active' );
	jQuery( this ).addClass( 'nav-tab-active' );
	jQuery( '.msgok-lang-wrap' ).hide();
	jQuery( jQuery( this ).attr( 'href' ) ).show();
	e.preventDefault();
} );

// Custom metafield
jQuery( document ).on( 'change', '.msgok-integrations-select', function( e ) {
	var val = jQuery( this ).val();
	var type = jQuery( this ).data( 'type' );

	if ( val == '__custom__' ) {
		jQuery( '#msgok_i_' + type ).show();
	} else {
		jQuery( '#msgok_i_' + type ).hide();
	}
} );

// Otevření nastavení
function msgok_openSettings( login_link ) {
	newwindow = window.open( login_link + "&wl=true&show=full", "msgok", "location=no,toolbar=no,menubar=no,scrollbars=yes,resizable=yes,height=" + screen.height + ",width=1000" );
	if ( window.focus ) {
		newwindow.focus();
	}
}

function msgok_openScennary( link, id ) {
	newwindow = window.open( link + id + "&wl=true&show=full", "msgok", "location=no,toolbar=no,menubar=no,scrollbars=yes,resizable=yes,height=" + screen.height + ",width=1000" );
	if ( window.focus ) {
		newwindow.focus();
	}
}

window.addEventListener( "message", ( event ) => {
	if ( event.origin !== "https://app.messageok.com" )
	{
		return;
	}

	console.log( event );

	if ( typeof event.data.reload != "undefined" && event.data.reload == true )
	{
		window.location.reload();
	}
}, false);
