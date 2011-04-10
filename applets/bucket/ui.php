<div class="vbx-applet">
    <h2>Place this Call in a Bucket</h2>
    <div class="vbx-input-container">
		<label class="field-label">Bucket Name
			<input class="medium" name="bucket" value="<?php echo AppletInstance::getValue('bucket') ?>">
		</label>
	</div>

    <h2>Then Continue the Call</h2>
	<?php echo AppletUI::DropZone('next'); ?>
</div>