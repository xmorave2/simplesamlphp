<?php
	$current_lang = "en";
	$languages = $this->getLanguageList();
	foreach ($languages AS $lang => $current) {
		if ($current) {
			$current_lang = $lang;
		}
	}
	$switch_lang = ($current_lang == 'en') ? 'cs' : 'en';
	$params = array('language' => $switch_lang);
	if (array_key_exists('stateparams', $this->data)) {
		foreach ($this->data['stateparams'] as $name => $value) {
			$params[$name] = $value;
		}
	}
	$href = htmlspecialchars(SimpleSAML\Utilities::addURLparameter(SimpleSAML\Utilities::selfURL(), $params));
	$img = SimpleSAML\Module::getModuleURL('mzk/'.$switch_lang.'.gif');
	$lang = "<a href='$href'> <img align='right' src='$img'/> </a>";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
	<title><?php echo $this->t('{login:user_pass_header}'); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="x-xrds-location" content="<?php echo SimpleSAML\Module::getModuleURL('mzk/xrds.xml'); ?>" />
	<link rel='stylesheet' href="<?php echo SimpleSAML\Module::getModuleURL('mzk/mzk.css?v9'); ?>" type='text/css' />
	<!--[if IE]>
		<style type="text/css">#login h1 a { margin-top: 35px; } #login #login_error { margin-bottom: 10px; }</style>
	<![endif]-->
	<script type="text/javascript">
		function focusit() {
			document.getElementById('username').focus();
		}
		window.onload = focusit;
	</script>
</head>
<body class="login">
	<!-- Google Tag Manager -->
	<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-MBZGH4"height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','GTM-MBZGH4');</script>
	<!-- End Google Tag Manager -->
	<div class="container">
		<div class="login-header"></div>
		<div id="login">
			<p>
				<a href="https://www.mzk.cz/">
					<img src="https://www.mzk.cz/sites/mzk.cz/themes/mzk/logo.png" height="40" align="bottom" alt="logo"/>
				</a>
				<span align='right'><?php echo $lang; ?></span>
			</p>
			<p>
				<h1><?php echo $this->t('{mzk:login:institution_login}'); ?></h1>
			</p>
