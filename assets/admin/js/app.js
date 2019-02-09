jQuery(function( $ ) {
	$( '#ipopup-true' ).on( 'click', function() {
		if ( $( this ).is( ':checked' ) ) {
			$( '#ipopup-popup' ).removeClass( 'ipopup-div--hidden' )
			$( '#ipopup-popup-val' ).prop( 'disabled', false )

		} else {
			$( '#ipopup-popup' ).addClass( 'ipopup-div--hidden' )
			$( '#ipopup-popup-val' ).prop( 'disabled', true )
		}
	} )

	$( '#ipopup-save' ).on( 'click', function() {
		const _this = $( this )
		const theForm = _this.closest( 'form' )
		const formData = new FormData( theForm[0] )
		const postID = $( '#post_ID' ).val()
		_this.prop( 'disabled', true )
		_this.html( 'Updating...' )

		formData.append( 'action', 'popupSaveMeta' )
		formData.append( 'postID', postID )

		fetch( ajaxurl, {
			method: 'POST',
			body: formData
		} )
		.then( __ => {
			_this.prop( 'disabled', false )
			_this.html( 'Update' )
		} )
	} )

	$( 'input[name="ipopup-bg-color"]' ).wpColorPicker()
} )