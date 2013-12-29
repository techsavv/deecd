<?php

$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hassidepre = false;
$hassidepost = false;

$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));

$bodyclasses = array();
    $bodyclasses[] = 'content-only';

if ($hascustommenu) {
    $bodyclasses[] = 'has-custom-menu';
}

$courseheader = $coursecontentheader = $coursecontentfooter = $coursefooter = '';
if (empty($PAGE->layout_options['nocourseheaderfooter'])) {
    $courseheader = $OUTPUT->course_header();
    $coursecontentheader = $OUTPUT->course_content_header();
    if (empty($PAGE->layout_options['nocoursefooter'])) {
        $coursecontentfooter = $OUTPUT->course_content_footer();
        $coursefooter = $OUTPUT->course_footer();
    }
}


echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
  <title><?php echo $PAGE->title; ?></title>
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

<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>">

<?php echo $OUTPUT->standard_top_of_body_html() ?>

<?php if ($hasheading || $hasnavbar || !empty($courseheader) || !empty($coursefooter)) { ?>
<img src="<?php echo $OUTPUT->pix_url('blocksBg','theme');?>" alt="" class="blocksBg" />
<div id="page-wrapper">
  <div id="page" style='margin-top:200px;border:1px solid black;' class="clearfix">

    

 <div class="myclear"></div>

      

<?php } ?>

    <div id="page-content">
        <div id="region-main-box">
            <div id="region-post-box">

                <div id="region-main-wrap">
                    <div id="region-main">
                        <div class="region-content">
                            <?php echo $coursecontentheader; ?>
                            <div class='courseHeader'>
                              
                            </div>
                            <h2 class='redirect-message'>
                            <?php echo $OUTPUT->main_content() ?>
                        </h2>
                            <?php echo $coursecontentfooter; ?>
                        </div>
                    </div>
                </div>

                <?php if ($hassidepre) { ?>
                <div id="region-pre">
                    <div class="region-content">
                        
                    </div>
                </div>
                <?php } ?>

                <?php if ($hassidepost) { ?>
                <div id="region-post">
                    <div class="region-content">
                        
                    </div>
                </div>
                <?php } ?>

            </div>
        </div>
    </div>

    <div class="myclear"></div>
    
<?php 

if ($hasheading || $hasnavbar || !empty($courseheader) || !empty($coursefooter)) { ?>
   <div class="myclear"></div>
  </div> <!-- END #page -->

</div> <!-- END #page-wrapper -->

<?php } ?>

<div id="page-footer-bottom">

<?php if ($hasfooter) {

  echo $OUTPUT->home_link();
  echo $OUTPUT->standard_footer_html();

} ?>

</div>


<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>