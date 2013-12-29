<?php echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <title><?php echo $PAGE->title ?></title>
    <script src="//ajax.googleapis.com/ajax/libs/webfont/1.4.7/webfont.js"></script>
  <script>
  WebFont.load({
    google: {
      families: ['Signika:400,600,700']
    }
  });
</script>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
</head>
<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses) ?>">
<?php echo $OUTPUT->standard_top_of_body_html() ?>

<div id="page-wrapper" class="embedded">
	<div id="page">

<!-- END OF HEADER -->

    <div id="content" class="clearfix">
        <?php echo $OUTPUT->main_content() ?>
    </div>

<!-- START OF FOOTER -->
	</div>
</div>
<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>