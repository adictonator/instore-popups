<style>
	.ipopup .ipopup__inner {
		width: <?php echo $width['desktop']; ?>;
		background-color: <?php echo $bgColor; ?>;
	}

	@media only screen
	and (min-device-width : 768px)
	and (max-device-width : 1024px)  {
		.ipopup .ipopup__inner {
			width: <?php echo $width['tablet']; ?>
		}
	}

	@media only screen
	and (min-device-width : 375px)
	and (max-device-width : 768px) {
		.ipopup .ipopup__inner {
			width: <?php echo $width['mobile']; ?>
		}
	}
</style>

<div class="ipopup">
	<div class="ipopup__inner<?php echo $animClass; ?>">
		<div class="ipopup__body">
			<?php
			$pattern = get_shortcode_regex();

			if ( preg_match_all( '/'. $pattern .'/s', $popupContent, $matches )
				&& array_key_exists( 2, $matches )
				&& in_array( 'ipop_timer', $matches[2] ) )
			{
				$sC = do_shortcode( '[ipop_timer '. $matches[3][0] .']' );
				echo preg_replace( '/'. $pattern .'/s', $sC, $popupContent );
			}

			?>
		</div>

		<span class="ipopup__close">&times;</span>
	</div>

</div>