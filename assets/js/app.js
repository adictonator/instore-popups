jQuery( function( $ ) {
	$( document ).on( 'click', '.ipopup__close', function() {
		const anim = $(this).parents( '.ipopup__inner' ).attr( 'class' ).split( /\s+/ )[1]
		const reverseAnim = anim + '--close'

		$( this ).parents( 'div.ipopup__inner' ).css( 'animation-direction', 'reverse' )
		$( this ).parents( 'div.ipopup__inner' ).addClass( reverseAnim )
		setTimeout( __ => {
			$( this ).parents( 'div.ipopup__inner' ).removeClass( anim +' '+ reverseAnim )

			$( this ).parents( 'div.ipopup' ).hide()
		}, 1000 )
	} )

	// const display = document.querySelector('[data-popup-counter]');
	// const time = display.getAttribute('data-popup-counter')

	// startTimer(time * 60, display);

	getPopupURLs()
} )

function getPopupURLs()
{
	if ( ! sessionStorage.getItem( 'loadedPopupURLs' ) ) {
		fetch( ipopup.url + '?action=getPopupURLs&postID=' + ipopup.postID )
		.then( resp => resp.json() )
		.then( x => {
			if ( x.success === true ) {
				document.body.innerHTML += x.html
			}
		} )
	}
}

function startTimer(duration, display) {
    var timer = duration, minutes, seconds;
    setInterval(function () {
        minutes = parseInt(timer / 60, 10)
        seconds = parseInt(timer % 60, 10);

        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;

        display.textContent = minutes + ":" + seconds;

        if (--timer < 0) {
            timer = duration;
        }
    }, 1000);
}
