<?php $this->load->view('rda_header');?>
<div class="container less_padding">
		<?php 
		if (isset($previously_valid_title))
		{ ?>

				<h3>Page/Record has been removed from the registry</h3>
				<p>You have reached a URL that is no longer valid. This is likely because the record you are looking for has been removed from the registry.</p>

			<?php
			echo '<p>You may be able to locate similar records by searching for the record by title: ';
		
			echo '<a href="' . base_url() . "search#!/q=" . rawurlencode($previously_valid_title) .'/p=1/tab=all/nq=1/">'.$previously_valid_title.'</a></p>';
		} elseif (isset($message))
		{ ?>
 					<h3>404 Page Not Found.</h3>
 					<hr />
 					<p>The page you requested was not found.</p>
		<?php 

		}elseif (isset($invalid_key))
		{ ?>
 					<h3>404 Page Not Found.</h3>
 					<hr />
 					<p>The page you requested was not found. <?php echo $invalid_key?></p>
		<?php 

		}
		?>
</div>
<?php $this->load->view('rda_footer');?>