<?php $this->load->view('header');?>
<div class="container-fluid" id="main-content">
	<div class="widget-box">
		<div class="widget-title">
			<h5>Spotlight CMS</h5>
		</div>
		<div class="widget-content nopadding">
			<div class="row">
				<div id="item_list" class="span3">
					<ul>
					<?php if(isset($items)): ?>
					<?php foreach($items as $i):?>
						<li id="<?php echo $i['id'];?>"><a href="javascript:;"><?php echo $i['title'];?></a></li>
					<?php endforeach;?>
					<?php endif; ?>
					<li id="new"><a href="javascript:;"><i class="icon-plus"></i> <b>Add New</b></a>
					</ul>
				</div>
				<div id="item_detail" class="span5">
					<?php if(isset($items)): ?>
					<?php foreach($items as $i):?>
						<div id="<?php echo $i['id'];?>-content" class="item-content hide">
							<form _id="<?php echo $i['id'];?>">
							<fieldset>
								<legend><?php echo $i['title'];?></legend>
								<input type="hidden" value="<?php echo $i['id'];?>" name="id">
									<label>Title:</label>
									<input type="text" placeholder="Title" value="<?php echo $i['title'];?>" name="title">
									<label>URL:</label>
									<input type="text" placeholder="URL" value="<?php echo $i['url'];?>" name="url">
									<label>Link Text: <small class="muted">(will display instead of the URL, if specified)</small></label>
									<input type="text" placeholder="Link Display Text" value="<?php echo (isset($i['url_text']) ? $i['url_text'] : "");?>" name="url_text">
									<label>Image URL: <small>(<?=anchor(apps_url('uploader/'), "upload a new image", array("target"=>"_blank"));?>)</small></label>
									<input type="text" placeholder="Image URL" value="<?php echo $i['img_url'];?>" name="img_url">
									<label>Image Attribution <small class="muted">(will display on mouse over on image)</small></label>
									<input type="text" placeholder="Image Attribution" value="<?php if(isset($i['img_attr'])) echo $i['img_attr'];?>" name="img_attr">
									<label>Open In:</label>
									<select name="new_window" required>
										<option value="no">Same Window</option>
										<option value="yes" <?php if(isset($i['new_window'])) echo $i['new_window']=='yes' ? 'selected=selected': '';?>>New Window</option>
									</select>
									<label>Visible:</label>
									<select name="visible" required>
										<option value="no">Hidden</option>
										<option value="yes" <?php echo $i['visible']=='yes' ? 'selected=selected': '';?>>Visible</option>
									</select>
									<label>Content:</label>
									<textarea name="content" class="editor"><?php echo $i['content'];?></textarea>
									<p/>
								<button type="button" class="btn btn-primary save" _id="<?php echo $i['id'];?>">Save</button>
								<button type="button" class="btn btn-link delete" _id="<?php echo $i['id'];?>">Delete</button>
							</fieldset>
							</form>
						</div>
					<?php endforeach;?>
					<?php endif; ?>
						<div id="new-content" class="item-content hide">
							<form _id="new">
							<fieldset>
								<legend>Add New</legend>
									<label>Title:</label>
									<input type="text" placeholder="Title" value="" name="title" required>
									<label>URL:</label>
									<input type="text" placeholder="URL" value="" name="url" required>
									<label>Link Text: <small class="muted">(will display instead of the URL, if specified)</small></label>
									<input type="text" placeholder="Link Display Text" value="" name="url_text">
									<label>Image URL:</label>
									<input type="text" placeholder="Image URL" value="" name="img_url" required>
									<label>Image Attribution</label>
									<input type="text" placeholder="Image Attribution" value="" name="img_attr">
									<label>Open In:</label>
									<select name="new_window" required>
										<option value="no">Same Window</option>
										<option value="yes">New Window</option>
									</select>
									<label>Visible:</label>
									<select name="visible" required>
										<option value="no">Hidden</option>
										<option value="yes">Visible</option>										
									</select>
									<label>Content:</label>
									<textarea name="content" class="editor" required></textarea>
									<p/>
								<button type="button" class="btn btn-primary add">Add New</button>
							</fieldset>
							</form>
						</div>
				</div>
				<div id="item_preview" class="span3">
					<?php if(isset($items)): ?>
					<?php foreach($items as $i):?>
					<div class="flexslider hide" id="<?php echo $i['id'];?>-preview">
						<img src="<?php echo $i['img_url'];?>" alt="" />
						<a href="#" class="title"><?php echo $i['title'];?></a>
						<div class="excerpt">
							<?php echo $i['content'];?>
						</div>
						<a target="_blank" href="<?php echo $i['url'];?>"><strong><?php echo (isset($i['url_text']) && $i['url_text'] ? $i['url_text'] : $i['url']);?></strong></a>
					</div>
					<?php endforeach;?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php $this->load->view('footer');?>
