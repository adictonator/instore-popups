<?php
$popups = new WP_Query([
	'post_type' => 'ipopups',
]);

$popupMeta = get_post_meta( get_the_ID(), '__instore_meta', true );

?>
<div class="ipopup-div">
	<input id="ipopup-true" type="checkbox" name="ipopup-true"<?php echo isset( $popupMeta['enabled'] ) ? ' checked' : ''; ?>>
	<label for="ipopup-true">Enable Instore Popup</label>
</div>

<div id="ipopup-popup" class="ipopup-div<?php echo ! isset( $popupMeta['enabled'] ) ? ' ipopup-div--hidden' : ''; ?>">
	<label for="ipopup-popup-val" class="ipopup-label">Select Popup to Display:</label>
	<select id="ipopup-popup-val" name="ipopup"<?php echo ! isset( $popupMeta['enabled'] ) ? ' disabled' : ''; ?>>
		<?php if ( $popups->have_posts() ) :
			while ( $popups->have_posts() ) : $popups->the_post(); ?>

				<option value="<?php echo get_the_ID(); ?>"<?php echo isset( $popupMeta['popID'] ) &&
							$popupMeta['popID'] == get_the_ID() ? ' selected' : ''; ?>><?php echo the_title(); ?></option>

			<?php endwhile;
			wp_reset_postdata();
		endif; ?>
	</select>
</div>

<div class="ipopup-div">
	<button type="button" id="ipopup-save" class="button-primary">Update</button>
</div>