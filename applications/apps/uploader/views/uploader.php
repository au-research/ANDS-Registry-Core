<?php 

/**
 * Image Upload Interface
 * 
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @package ands/apps
 * 
 */
// Default view variables
$recent_uploads = isset($recent_uploads) ? $recent_uploads : array();

?>
<?php $this->load->view('header');?>

<div id="content" style="margin-left:0px">
<section>
	<div class="span12">

		<h3>Image Uploader</h3>
		<div class="row-fluid">

			<div class="span8">
				<div class="box-header clearfix">
					<h5>Recent Uploads</h5>
				</div>

				<div class="well">
					<table class="table table-striped">
						<thead>
						<tr>
							<th>Date Uploaded</th>
							<th>Optimised File</th>
							<th>Original File</th>
						</tr>
						<?php if (!$recent_uploads):?>
							<tr>
								<td colspan="3"><small><em>No files have been uploaded</em></small></td>
							</tr>
						<?php else: ?>
							<?php foreach ($recent_uploads AS $upload): ?>
								<tr>
									<td><?=date("j F Y", $upload['date_modified']);?></td>
									<td><a href="<?=asset_url('uploads/' . $upload['optimised_filename'],'base');?>" target="_blank"><?=$upload['filename'];?></a></td>
									<td><a href="<?=asset_url('uploads/' . $upload['filename'],'base');?>" target="_blank">(recover)</a></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</table>
				</div>
			</div>

			<div class="span4">
				<div class="widget-box" style="margin-top:40px;">
					<div class="widget-title"><h5>Upload a new Image</h5></div>
					<div class="widget-content">

						<?php if (isset($success_message)): ?>
							<div class="alert alert-info">
								<?=$success_message;?>
							</div>
						<?php endif; ?>

						<?php if (isset($error_message)): ?>
							<div class="alert alert-error">
							 	<?=$error_message;?>
							</div>
						<?php endif; ?>
			
							<p>Select an image to upload (png, jpg or gif).</p>
						 	<p><small><strong>File to upload:</strong></small></p>
							<?php echo form_open_multipart(base_url('uploader/upload'));?>
								<input type="file" name="new_file" />

								<br /><br />

								<input type="submit" value="Upload File" />


						 	<p><br/><small>* The image will be automatically converted and compressed to fit the RDA spotlight rotator. <?=(isset($max_filesize) ? "Max file size: " . $max_filesize . "K." : "");?></small></p>

							</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

</section>

</div>
<br class="clear"/>

<?php $this->load->view('footer');?>