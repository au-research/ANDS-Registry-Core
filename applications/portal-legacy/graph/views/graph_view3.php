<?php $this->load->view('rda_header');?>
<style>
.link {
  stroke: #ccc;
}

.node {
	cursor:pointer;
}

.node text {
  pointer-events: none;
  font: 10px sans-serif;
}
</style>
<input type="hidden" class="hide" id="registry_object_id" value="<?php echo $id;?>" />
<div id="graph" style="width:100%;height:750px;"></div>
<?php $this->load->view('rda_footer');?>