<div class="ipopup-div">
	<div class="ipopup-content-wrapper">
		<div data-ipopup-wrap="assignments">
			<table class="widefat striped">
				<thead>
					<tr><th colspan="2"><h3>Popup Appearance</h3></th></tr>
				</thead>
			</table>

			<table class="widefat striped">
				<thead>
					<tr><th scope="col" colspan="6">Set Popup Width</th></tr>
				</thead>
				<tbody>
					<tr class="has-multi">
						<th><label for="ipopup-width[desktop]">For Desktop</label></th>
						<td>
							<input type="number" min="0" id="ipopup-width[desktop]" name="ipopup-width[desktop][val]" value="<?php echo $desktopWidth; ?>">
							<div class="ipopup-div__group">
								<label for="ipopup-width[desktop][px]">
									<input type="radio" id="ipopup-width[desktop][px]" name="ipopup-width[desktop][unit]" value="px" <?php echo $desktopUnit === 'px' ? 'checked' : 'checked'; ?>>
									<abbr title="Pixels">px</abbr>
								</label>
								<label for="ipopup-width[desktop][per]">
									<input type="radio" id="ipopup-width[desktop][per]" name="ipopup-width[desktop][unit]" value="%" <?php echo $desktopUnit === '%' ? 'checked' : ''; ?>> <abbr title="Percentage">%</abbr>
								</label>
							</div>
						</td>
						<th><label for="ipopup-width[tablet]">For iPad/Tablets</label></th>
						<td>
							<input type="number" min="0" id="ipopup-width[tablet]" name="ipopup-width[tablet][val]" value="<?php echo $tabletWidth; ?>">
							<div class="ipopup-div__group">
								<label for="ipopup-width[tablet][px]">
									<input type="radio" id="ipopup-width[tablet][px]" name="ipopup-width[tablet][unit]" value="px" <?php echo $tabletUnit === 'px' ? 'checked' : 'checked'; ?>>
									<abbr title="Pixels">px</abbr>
								</label>
								<label for="ipopup-width[tablet][per]">
									<input type="radio" id="ipopup-width[tablet][per]" name="ipopup-width[tablet][unit]" value="%" <?php echo $tabletUnit === '%' ? 'checked' : ''; ?>> <abbr title="Percentage">%</abbr>
								</label>
							</div>
						</td>
						<th><label for="ipopup-width[mobile]">For Mobile/Small Devices</label></th>
						<td>
							<input type="number" min="0" id="ipopup-width[mobile]" name="ipopup-width[mobile][val]" value="<?php echo $mobileWidth; ?>">
							<div class="ipopup-div__group">
								<label for="ipopup-width[mobile][px]">
									<input type="radio" id="ipopup-width[mobile][px]" name="ipopup-width[mobile][unit]" value="px" <?php echo $mobileUnit === 'px' ? 'checked' : 'checked'; ?>>
									<abbr title="Pixels">px</abbr>
								</label>
								<label for="ipopup-width[mobile][per]">
									<input type="radio" id="ipopup-width[mobile][per]" name="ipopup-width[mobile][unit]" value="%" <?php echo $mobileUnit === '%' ? 'checked' : ''; ?>> <abbr title="Percentage">%</abbr>
								</label>
							</div>
						</td>
					</tr>
				</tbody>

				<table class="widefat striped">
					<thead>
						<tr><th scope="col" colspan="2">Popup Background</th></tr>
					</thead>
					<tbody>
						<tr>
							<th><label for="ipopup-bg-color">Set Background Color</label></th>
							<td><input type="text" id="ipopup-bg-color" name="ipopup-bg-color"></td>
						</tr>
					</tbody>
				</table>
			</table>

			<table class="widefat striped">
				<thead>
					<tr><th colspan="2"><h3>Popup Settings</h3></th></tr>
				</thead>
			</table>
			<table class="widefat striped">
				<thead>
					<tr><th scope="col" colspan="2">Set Popup Animation</th></tr>
				</thead>
				<tbody>
					<tr>
						<th><label for="ipopup-anim">Animation Type</label></th>
						<td>
							<select name="ipopup-anim" id="ipopup-anim">
								<?php foreach ( self::ANIMATIONS as $animType => $animName ) : ?>
									<option value="<?php echo $animType; ?>"
									<?php echo $animation && $animType === $animation ? 'selected' : ''; ?>
									><?php echo $animName; ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
				</tbody>
			</table>

			<table class="widefat striped">
				<thead>
					<tr><th scope="col" colspan="2">Page / URL</th></tr>
				</thead>
				<tbody>
					<tr>
						<th><label for="ipopup-urls">URL</label></th>
						<td>
							<textarea name="ipopup-urls" id="ipopup-urls" placeholder="Enter one URL per line"><?php echo $urls; ?></textarea>
						</td>
					</tr>
				</tbody>
			</table>

			<table class="widefat striped">
				<thead>
					<tr><th scope="col" colspan="2">Wildcard URL</th></tr>
				</thead>
				<tbody>
					<tr>
						<th>URL Segment</th>
						<td>
							<textarea name="ipopup-wild-urls" placeholder="Enter one URL segment per line"><?php echo $wildUrls; ?></textarea>
						</td>
					</tr>
				</tbody>
			</table>

			<table class="widefat striped">
				<thead>
					<tr><th scope="col" colspan="2">Category Linkup</th></tr>
				</thead>
				<tbody>
					<tr>
						<th>Category Slug</th>
						<td>
							<textarea name="ipopup-cat-slugs" placeholder="Enter one category slug per line"><?php echo $catSlugs; ?></textarea>
						</td>
					</tr>
				</tbody>
			</table>

			<table class="widefat striped">
				<tfoot>
					<tr>
						<td colspan="2"><button type="button" class="button-primary" id="ipopup-save">Update</button></td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>

