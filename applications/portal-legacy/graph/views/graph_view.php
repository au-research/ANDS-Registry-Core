<?php $this->load->view('rda_header');?>
<style>
.node {
  cursor: pointer;
}

.node circle {
  fill: #fff;
  stroke: steelblue;
  stroke-width: 1.5px;
}

.node text {
  font: 10px sans-serif;
}

.link {
  fill: none;
  stroke: #ccc;
  stroke-width: 1.5px;
  z-index:-999;
}

.rect{
  width:180px;
  height:40px;
  padding-top:10px;
  background:white;
  border:1px solid steelblue;
}

.rect img.icon{
  width:20px;height:20px;margin-left:10px;
  float:left;
}

.rect h1{
  float:left; width:130px; margin:0;padding:0;font:10px sans-serif;margin-left:5px;margin-top:5px;
  white-space:nowrap;overflow:hidden;text-overflow: ellipsis;
}
</style>
<input type="hidden" class="hide" id="registry_object_id" value="<?php echo $id;?>" />
<div id="graph"></div>
<?php $this->load->view('rda_footer');?>