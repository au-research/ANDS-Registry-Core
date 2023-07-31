<html>
<head>
	<title>Research Data Australia</title>
	<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW"/>
	<style>
	body {
  		/* This has to be same as the text-shadows below */
	    text-align:center;
	    background-color:#ccc;
	    font-family: Helvetica, Arial, sans-serif;
	}
	h1 {
	    font-weight: bold;
	    font-size: 6em;
	    line-height: 1em;
	    margin:0;
	}
	p{
		font-size:1.2em;
		font-weight:100;
	}
	.inset-text {
	    /* Shadows are visible under slightly transparent text color */
	    color: rgba(10,60,150, 0.8);
	    text-shadow: 1px 4px 6px #def, 0 0 0 #000, 1px 4px 6px #def;
	}
	/* Don't show shadows when selecting text */
	::-moz-selection { background: #5af; color: #fff; text-shadow: none; }
	::selection { background: #5af; color: #fff; text-shadow: none; }

	.message{
		border:1px solid #ccc;
		background:#ddd;
		padding:20px;
		color: #f24a5b;
	}
</style>
</head>
<body>
	<h1 class="inset-text">500</h1>
	<p>The service is currently down. Please try again in a few minutes</p>
	<?php if(is_dev()):?>
		<div class="message">
			<?php echo $message;?>
		</div>
	<?php endif;?>

</body>
</html>
