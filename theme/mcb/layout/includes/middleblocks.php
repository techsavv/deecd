<?php
$hashomeleft = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('home-left', $OUTPUT));
$hashomemiddle = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('home-middle', $OUTPUT));
$hashomeright = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('home-right', $OUTPUT));
$homel = 'home-left';
$homem = 'home-middle';
$homer = 'home-right';
?>

<div class="row-fluid" id="middle-blocks">
    <?php
    echo $OUTPUT->mcbblocks($homel, 'span4');
    echo $OUTPUT->mcbblocks($homem, 'span4');
    echo $OUTPUT->mcbblocks($homer, 'span4');
    ?>
</div>