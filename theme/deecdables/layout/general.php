<?php



$hasheading = ($PAGE->heading);

$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());

$hasfooter = (empty($PAGE->layout_options['nofooter']));

$hassidepre = $PAGE->blocks->region_has_content('side-pre', $OUTPUT);

$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);



$custommenu = $OUTPUT->custom_menu();

$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));



$bodyclasses = array();

if ($hassidepre && !$hassidepost) {

    $bodyclasses[] = 'side-pre-only';

} else if ($hassidepost && !$hassidepre) {

    $bodyclasses[] = 'side-post-only';

} else if (!$hassidepost && !$hassidepre) {

    $bodyclasses[] = 'content-only';

}



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

  <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />

  <link href='http://fonts.googleapis.com/css?family=Signika:400,600,700' rel='stylesheet' type='text/css'>

  <?php echo $OUTPUT->standard_head_html() ?>

</head>



<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>">



<?php echo $OUTPUT->standard_top_of_body_html() ?>



<?php if ($hasheading || $hasnavbar || !empty($courseheader) || !empty($coursefooter)) { ?>

<img src="<?php echo $OUTPUT->pix_url('blocksBg','theme');?>" alt="" class="blocksBg" />

<div id="page-wrapper">

  <div id="page" class="clearfix">



    <div id="page-header" class="clearfix">

      

      <img src="<?php echo $OUTPUT->pix_url('deecdLogo','theme');?>" alt="" class="deecdLogo" />

      

        <h1 class="headermain">Professional Learning Portal</h1>

        <div class="headermenu">

          <?php echo $OUTPUT->login_info();

          if (!empty($PAGE->layout_options['langmenu'])) {

            echo $OUTPUT->lang_menu();

          }

          echo $PAGE->headingmenu; ?>

        </div>

      

      <?php if ($hascustommenu) { ?>

      <div id="custommenu"><?php echo $custommenu; ?></div>

      <?php } ?>

    </div>



 <div class="myclear"></div>



      <?php if (!empty($courseheader)) { ?>

        <div id="course-header"><?php echo $courseheader; ?></div>

      <?php } ?>



      <?php if ($hasnavbar) { ?>

        <div class="navbar clearfix">

          <div class="breadcrumb"><?php echo $OUTPUT->navbar(); ?></div>

          <div class="navbutton"> <?php echo $PAGE->button; ?></div>

        </div>

      <?php } ?>



<?php } ?>



    <div id="page-content">

        <div id="region-main-box">

            <div id="region-post-box">



                <div id="region-main-wrap">

                    <div id="region-main">

                        <div class="region-content">

                            <?php echo $coursecontentheader; ?>

                            <div class='courseHeader' >

                              <?php echo html_writer::start_tag('img',array('src'=>$OUTPUT->pix_url('courseheader','theme'),

                                                                            'class'=>'courseHeaderImg')); ?>

                            </div>

                            <?php echo $OUTPUT->main_content() ?>

                            <?php echo $coursecontentfooter; ?>

                        </div>

                    </div>

                </div>



                <?php if ($hassidepre) { ?>

                <div id="region-pre">

                    <div class="region-content">

                        <?php echo $OUTPUT->blocks('side-pre') ?>

                    </div>

                </div>

                <?php } ?>



                <?php if ($hassidepost) { ?>

                <div id="region-post">

                    <div class="region-content">

                        <?php echo $OUTPUT->blocks('side-post') ?>

                    </div>

                </div>

                <?php } ?>



            </div>

        </div>

    </div>



    <div class="myclear"></div>

    <?php if (!empty($coursefooter)) { ?>

        <div id="course-footer"><?php echo $coursefooter; ?></div>

    <?php } ?>

<?php if ($hasfooter) { ?>



    <div id="page-footer" class="clearfix">

      <div class='footer-left'>Developed and hosted by <a href="http://tech-savvy.com.au">Tech-Savvy Solutions</a></div>

      <p class="helplink"><?php echo page_doc_link(get_string('moodledocslink')) ?></p>

      <?php echo $OUTPUT->login_info(); ?>

    </div>



<?php }



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