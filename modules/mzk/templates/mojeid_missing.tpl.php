<?php
$this->includeAtTemplateBase('includes/header.php');
?>
<h2><?php echo $this->t('{mzk:login:mojeid_missing_header}'); ?></h2>
<a href="https://www.mzk.cz/registration_mzk_linking"><?php echo $this->t('{mzk:login:mojeid_missing_link_text}'); ?></a>
<?php
$this->includeAtTemplateBase('includes/footer.php');
