<?php 

/**
 * UPDATE DOI
 * 
 * @author LIZ WOODS <liz.woods@ands.org.au>
 * @see ands/mydois/controllers/mydois
 * @package ands/mydois
 * 
 */
?>
<h3>Update DOI <?=$doi_id;?></h3>

	<div class="box-content">
        <p id="update_result">  </p>
		<form id="update_form" class="form-horizontal" method="POST">
			<strong>Enter the new URL and/or the new xml for <?=$doi_id ?></strong>
            <div class="control-group">
                <label class="control-label">URL</label> <div class="controls"><input type="text" name="new_url" value="<?=$url?>" /><br /></div></div>
            <div class="control-group">
                <label class="control-label">XML</label> <div class="controls col-xs-12" ><textarea name="new_xml" style="width:400px;height:275px"><?=$datacite_xml?></textarea></div></div>
			<input type="hidden" name="old_url" value="<?=$url?>"/>
			<input type="hidden" name="doi_id" value="<?=$doi_id?>"/>
			<input type="hidden" name="client_id" value="<?=$client_id?>"/>
            <input type="hidden" name="app_id" value="<?=$app_id?>"/>
			<br/>

	</div>

