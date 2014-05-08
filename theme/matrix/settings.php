<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This is built using the Clean template to allow for new theme's using
 * Moodle's new Bootstrap theme engine
 *
 *
 * @package   theme_matrix
 * @copyright 2013 Julian Ridden
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$settings = null;

defined('MOODLE_INTERNAL') || die;


	$ADMIN->add('themes', new admin_category('theme_matrix', 'matrix'));

	// "geneicsettings" settingpage
	$temp = new admin_settingpage('theme_matrix_generic',  get_string('geneicsettings', 'theme_matrix'));
	
	// Default Site icon setting.
    $name = 'theme_matrix/siteicon';
    $title = get_string('siteicon', 'theme_matrix');
    $description = get_string('siteicondesc', 'theme_matrix');
    $default = 'laptop';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $temp->add($setting);
    
    // Include Awesome Font from Bootstrapcdn
    $name = 'theme_matrix/bootstrapcdn';
    $title = get_string('bootstrapcdn', 'theme_matrix');
    $description = get_string('bootstrapcdndesc', 'theme_matrix');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
	
    // Logo file setting.
    $name = 'theme_matrix/logo';
    $title = get_string('logo', 'theme_matrix');
    $description = get_string('logodesc', 'theme_matrix');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'logo');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Font Selector.
    $name = 'theme_matrix/fontselect';
    $title = get_string('fontselect' , 'theme_matrix');
    $description = get_string('fontselectdesc', 'theme_matrix');
    $default = '1';
    $choices = array(
    	'1'=>'Oswald & PT Sans', 
    	'2'=>'Lobster & Cabin', 
    	'3'=>'Raleway & Goudy', 
    	'4'=>'Allerta & Crimson Text', 
    	'5'=>'Arvo & PT Sans',
    	'6'=>'Dancing Script & Josefin Sans',
    	'7'=>'Allan & Cardo',
    	'8'=>'Molengo & Lekton',
    	'9'=>'Droid Serif & Droid Sans',
    	'10'=>'Corbin & Nobile',
    	'11'=>'Ubuntu & Vollkorn',
    	'12'=>'Bree Serif & Open Sans', 
    	'13'=>'Bevan & Pontano Sans', 
    	'14'=>'Abril Fatface & Average', 
    	'15'=>'Playfair Display and Muli', 
    	'16'=>'Sansita One & Kameron',
    	'17'=>'Istok Web & Lora',
    	'18'=>'Pacifico & Arimo',
    	'19'=>'Nixie One & Ledger',
    	'20'=>'Cantata One & Imprima',
    	'21'=>'Signika Negative & Arimo',
    	'22'=>'DISABLE Google Fonts');
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // User picture in header setting.
    $name = 'theme_matrix/headerprofilepic';
    $title = get_string('headerprofilepic', 'theme_matrix');
    $description = get_string('headerprofilepicdesc', 'theme_matrix');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Fixed or Variable Width.
    $name = 'theme_matrix/pagewidth';
    $title = get_string('pagewidth', 'theme_matrix');
    $description = get_string('pagewidthdesc', 'theme_matrix');
    $default = 1200;
    $choices = array(1900=>get_string('fixedwidthwide','theme_matrix'), 1200=>get_string('fixedwidthnarrow','theme_matrix'), 100=>get_string('variablewidth','theme_matrix'));
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Custom or standard layout.
    $name = 'theme_matrix/layout';
    $title = get_string('layout', 'theme_matrix');
    $description = get_string('layoutdesc', 'theme_matrix');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //Include the Editicons css rules
    $name = 'theme_matrix/editicons';
    $title = get_string('editicons', 'theme_matrix');
    $description = get_string('editiconsdesc', 'theme_matrix');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $temp->add($setting);
    
    // Performance Information Display.
    $name = 'theme_matrix/perfinfo';
    $title = get_string('perfinfo' , 'theme_matrix');
    $description = get_string('perfinfodesc', 'theme_matrix');
    $perf_max = get_string('perf_max', 'theme_matrix');
    $perf_min = get_string('perf_min', 'theme_matrix');
    $default = 'min';
    $choices = array('min'=>$perf_min, 'max'=>$perf_max);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Navbar Seperator.
    $name = 'theme_matrix/navbarsep';
    $title = get_string('navbarsep' , 'theme_matrix');
    $description = get_string('navbarsepdesc', 'theme_matrix');
    $nav_thinbracket = get_string('nav_thinbracket', 'theme_matrix');
    $nav_doublebracket = get_string('nav_doublebracket', 'theme_matrix');
    $nav_thickbracket = get_string('nav_thickbracket', 'theme_matrix');
    $nav_slash = get_string('nav_slash', 'theme_matrix');
    $nav_pipe = get_string('nav_pipe', 'theme_matrix');
    $dontdisplay = get_string('dontdisplay', 'theme_matrix');
    $default = '/';
    $choices = array('/'=>$nav_slash, '\f105'=>$nav_thinbracket, '\f101'=>$nav_doublebracket, '\f054'=>$nav_thickbracket, '|'=>$nav_pipe);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Copyright setting.
    $name = 'theme_matrix/copyright';
    $title = get_string('copyright', 'theme_matrix');
    $description = get_string('copyrightdesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $temp->add($setting);
    
    // Footnote setting.
    $name = 'theme_matrix/footnote';
    $title = get_string('footnote', 'theme_matrix');
    $description = get_string('footnotedesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Custom CSS file.
    $name = 'theme_matrix/customcss';
    $title = get_string('customcss', 'theme_matrix');
    $description = get_string('customcssdesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $ADMIN->add('theme_matrix', $temp);
    
    /* Custom Menu Settings */
    $temp = new admin_settingpage('theme_matrix_custommenu', get_string('custommenuheading', 'theme_matrix'));
	            
    //This is the descriptor for the following Moodle color settings
    $name = 'theme_matrix/mydashboardinfo';
    $heading = get_string('mydashboardinfo', 'theme_matrix');
    $information = get_string('mydashboardinfodesc', 'theme_matrix');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Toggle dashboard display in custommenu.
    $name = 'theme_matrix/displaymydashboard';
    $title = get_string('displaymydashboard', 'theme_matrix');
    $description = get_string('displaymydashboarddesc', 'theme_matrix');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for the following Moodle color settings
    $name = 'theme_matrix/mycoursesinfo';
    $heading = get_string('mycoursesinfo', 'theme_matrix');
    $information = get_string('mycoursesinfodesc', 'theme_matrix');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Toggle courses display in custommenu.
    $name = 'theme_matrix/displaymycourses';
    $title = get_string('displaymycourses', 'theme_matrix');
    $description = get_string('displaymycoursesdesc', 'theme_matrix');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Set terminology for dropdown course list
	$name = 'theme_matrix/mycoursetitle';
	$title = get_string('mycoursetitle','theme_matrix');
	$description = get_string('mycoursetitledesc', 'theme_matrix');
	$default = 'course';
	$choices = array(
		'course' => get_string('mycourses', 'theme_matrix'),
		'unit' => get_string('myunits', 'theme_matrix'),
		'class' => get_string('myclasses', 'theme_matrix'),
		'module' => get_string('mymodules', 'theme_matrix')
	);
	$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
	$setting->set_updatedcallback('theme_reset_all_caches');
	$temp->add($setting);
    
    $ADMIN->add('theme_matrix', $temp);
    
	/* Color Settings */
    $temp = new admin_settingpage('theme_matrix_color', get_string('colorheading', 'theme_matrix'));
    $temp->add(new admin_setting_heading('theme_matrix_color', get_string('colorheadingsub', 'theme_matrix'),
            format_text(get_string('colordesc' , 'theme_matrix'), FORMAT_MARKDOWN)));

    // Background Image.
    $name = 'theme_matrix/pagebackground';
    $title = get_string('pagebackground', 'theme_matrix');
    $description = get_string('pagebackgrounddesc', 'theme_matrix');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'pagebackground');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Main theme colour setting.
    $name = 'theme_matrix/themecolor';
    $title = get_string('themecolor', 'theme_matrix');
    $description = get_string('themecolordesc', 'theme_matrix');
    $default = '#30add1';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Main theme Hover colour setting.
    $name = 'theme_matrix/themehovercolor';
    $title = get_string('themehovercolor', 'theme_matrix');
    $description = get_string('themehovercolordesc', 'theme_matrix');
    $default = '#29a1c4';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for the Slideshow
    $name = 'theme_matrix/slidecolorinfo';
    $heading = get_string('slidecolors', 'theme_matrix');
    $information = get_string('slidecolorsdesc', 'theme_matrix');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
      // Slide Header colour setting.
    $name = 'theme_matrix/slideheadercolor';
    $title = get_string('slideheadercolor', 'theme_matrix');
    $description = get_string('slideheadercolordesc', 'theme_matrix');
    $default = '#30add1';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Slide Text colour setting.
    $name = 'theme_matrix/slidecolor';
    $title = get_string('slidecolor', 'theme_matrix');
    $description = get_string('slidecolordesc', 'theme_matrix');
    $default = '#888';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Slide Button colour setting.
    $name = 'theme_matrix/slidebuttoncolor';
    $title = get_string('slidebuttoncolor', 'theme_matrix');
    $description = get_string('slidebuttoncolordesc', 'theme_matrix');
    $default = '#30add1';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
        //This is the descriptor for the Slideshow
    $name = 'theme_matrix/footercolorinfo';
    $heading = get_string('footercolors', 'theme_matrix');
    $information = get_string('footercolorsdesc', 'theme_matrix');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Footer background colour setting.
    $name = 'theme_matrix/footercolor';
    $title = get_string('footercolor', 'theme_matrix');
    $description = get_string('footercolordesc', 'theme_matrix');
    $default = '#000000';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer text colour setting.
    $name = 'theme_matrix/footertextcolor';
    $title = get_string('footertextcolor', 'theme_matrix');
    $description = get_string('footertextcolordesc', 'theme_matrix');
    $default = '#DDDDDD';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer Block Heading colour setting.
    $name = 'theme_matrix/footerheadingcolor';
    $title = get_string('footerheadingcolor', 'theme_matrix');
    $description = get_string('footerheadingcolordesc', 'theme_matrix');
    $default = '#CCCCCC';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer Seperator colour setting.
    $name = 'theme_matrix/footersepcolor';
    $title = get_string('footersepcolor', 'theme_matrix');
    $description = get_string('footersepcolordesc', 'theme_matrix');
    $default = '#313131';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer URL colour setting.
    $name = 'theme_matrix/footerurlcolor';
    $title = get_string('footerurlcolor', 'theme_matrix');
    $description = get_string('footerurlcolordesc', 'theme_matrix');
    $default = '#BBBBBB';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer URL hover colour setting.
    $name = 'theme_matrix/footerhovercolor';
    $title = get_string('footerhovercolor', 'theme_matrix');
    $description = get_string('footerhovercolordesc', 'theme_matrix');
    $default = '#FFFFFF';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);



 	$ADMIN->add('theme_matrix', $temp);
 
 
    /* Slideshow Widget Settings */
    $temp = new admin_settingpage('theme_matrix_slideshow', get_string('slideshowheading', 'theme_matrix'));
    $temp->add(new admin_setting_heading('theme_matrix_slideshow', get_string('slideshowheadingsub', 'theme_matrix'),
            format_text(get_string('slideshowdesc' , 'theme_matrix'), FORMAT_MARKDOWN)));
    
    // Toggle Slideshow.
    $name = 'theme_matrix/toggleslideshow';
    $title = get_string('toggleslideshow' , 'theme_matrix');
    $description = get_string('toggleslideshowdesc', 'theme_matrix');
    $alwaysdisplay = get_string('alwaysdisplay', 'theme_matrix');
    $displaybeforelogin = get_string('displaybeforelogin', 'theme_matrix');
    $displayafterlogin = get_string('displayafterlogin', 'theme_matrix');
    $dontdisplay = get_string('dontdisplay', 'theme_matrix');
    $default = 'alwaysdisplay';
    $choices = array('1'=>$alwaysdisplay, '2'=>$displaybeforelogin, '3'=>$displayafterlogin, '0'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Hide slideshow on phones.
    $name = 'theme_matrix/hideonphone';
    $title = get_string('hideonphone' , 'theme_matrix');
    $description = get_string('hideonphonedesc', 'theme_matrix');
    $display = get_string('alwaysdisplay', 'theme_matrix');
    $dontdisplay = get_string('dontdisplay', 'theme_matrix');
    $default = 'display';
    $choices = array(''=>$display, 'hidden-phone'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Slideshow Design Picker.
    $name = 'theme_matrix/slideshowvariant';
    $title = get_string('slideshowvariant' , 'theme_matrix');
    $description = get_string('slideshowvariantdesc', 'theme_matrix');
    $slideshow1 = get_string('slideshow1', 'theme_matrix');
    $slideshow2 = get_string('slideshow2', 'theme_matrix');
    $default = 'slideshow1';
    $choices = array('1'=>$slideshow1, '2'=>$slideshow2);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 1
     */
     
    //This is the descriptor for Slide One
    $name = 'theme_matrix/slide1info';
    $heading = get_string('slide1', 'theme_matrix');
    $information = get_string('slideinfodesc', 'theme_matrix');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);

    // Title.
    $name = 'theme_matrix/slide1';
    $title = get_string('slidetitle', 'theme_matrix');
    $description = get_string('slidetitledesc', 'theme_matrix');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_matrix/slide1image';
    $title = get_string('slideimage', 'theme_matrix');
    $description = get_string('slideimagedesc', 'theme_matrix');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide1image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_matrix/slide1caption';
    $title = get_string('slidecaption', 'theme_matrix');
    $description = get_string('slidecaptiondesc', 'theme_matrix');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_matrix/slide1url';
    $title = get_string('slideurl', 'theme_matrix');
    $description = get_string('slideurldesc', 'theme_matrix');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 2
     */
     
    //This is the descriptor for Slide Two
    $name = 'theme_matrix/slide2info';
    $heading = get_string('slide2', 'theme_matrix');
    $information = get_string('slideinfodesc', 'theme_matrix');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);

    // Title.
    $name = 'theme_matrix/slide2';
    $title = get_string('slidetitle', 'theme_matrix');
    $description = get_string('slidetitledesc', 'theme_matrix');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_matrix/slide2image';
    $title = get_string('slideimage', 'theme_matrix');
    $description = get_string('slideimagedesc', 'theme_matrix');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide2image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_matrix/slide2caption';
    $title = get_string('slidecaption', 'theme_matrix');
    $description = get_string('slidecaptiondesc', 'theme_matrix');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_matrix/slide2url';
    $title = get_string('slideurl', 'theme_matrix');
    $description = get_string('slideurldesc', 'theme_matrix');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 3
     */

    //This is the descriptor for Slide Three
    $name = 'theme_matrix/slide3info';
    $heading = get_string('slide3', 'theme_matrix');
    $information = get_string('slideinfodesc', 'theme_matrix');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Title.
    $name = 'theme_matrix/slide3';
    $title = get_string('slidetitle', 'theme_matrix');
    $description = get_string('slidetitledesc', 'theme_matrix');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_matrix/slide3image';
    $title = get_string('slideimage', 'theme_matrix');
    $description = get_string('slideimagedesc', 'theme_matrix');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide3image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_matrix/slide3caption';
    $title = get_string('slidecaption', 'theme_matrix');
    $description = get_string('slidecaptiondesc', 'theme_matrix');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_matrix/slide3url';
    $title = get_string('slideurl', 'theme_matrix');
    $description = get_string('slideurldesc', 'theme_matrix');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 4
     */
     
    //This is the descriptor for Slide Four
    $name = 'theme_matrix/slide4info';
    $heading = get_string('slide4', 'theme_matrix');
    $information = get_string('slideinfodesc', 'theme_matrix');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);

    // Title.
    $name = 'theme_matrix/slide4';
    $title = get_string('slidetitle', 'theme_matrix');
    $description = get_string('slidetitledesc', 'theme_matrix');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_matrix/slide4image';
    $title = get_string('slideimage', 'theme_matrix');
    $description = get_string('slideimagedesc', 'theme_matrix');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide4image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_matrix/slide4caption';
    $title = get_string('slidecaption', 'theme_matrix');
    $description = get_string('slidecaptiondesc', 'theme_matrix');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_matrix/slide4url';
    $title = get_string('slideurl', 'theme_matrix');
    $description = get_string('slideurldesc', 'theme_matrix');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    
    $ADMIN->add('theme_matrix', $temp);
    
    $temp = new admin_settingpage('theme_matrix_frontcontent', get_string('frontcontentheading', 'theme_matrix'));
	$temp->add(new admin_setting_heading('theme_matrix_frontcontent', get_string('frontcontentheadingsub', 'theme_matrix'),
            format_text(get_string('frontcontentdesc' , 'theme_matrix'), FORMAT_MARKDOWN)));
    
    // Enable Frontpage Content
    $name = 'theme_matrix/usefrontcontent';
    $title = get_string('usefrontcontent', 'theme_matrix');
    $description = get_string('usefrontcontentdesc', 'theme_matrix');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Frontpage Content
    $name = 'theme_matrix/frontcontentarea';
    $title = get_string('frontcontentarea', 'theme_matrix');
    $description = get_string('frontcontentareadesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Frontpage Block alignment.
    $name = 'theme_matrix/frontpageblocks';
    $title = get_string('frontpageblocks' , 'theme_matrix');
    $description = get_string('frontpageblocksdesc', 'theme_matrix');
    $left = get_string('left', 'theme_matrix');
    $right = get_string('right', 'theme_matrix');
    $default = 'left';
    $choices = array('1'=>$left, '0'=>$right);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Toggle Frontpage Middle Blocks
    $name = 'theme_matrix/frontpagemiddleblocks';
    $title = get_string('frontpagemiddleblocks' , 'theme_matrix');
    $description = get_string('frontpagemiddleblocksdesc', 'theme_matrix');
    $alwaysdisplay = get_string('alwaysdisplay', 'theme_matrix');
    $displaybeforelogin = get_string('displaybeforelogin', 'theme_matrix');
    $displayafterlogin = get_string('displayafterlogin', 'theme_matrix');
    $dontdisplay = get_string('dontdisplay', 'theme_matrix');
    $default = 'display';
    $choices = array('1'=>$alwaysdisplay, '2'=>$displaybeforelogin, '3'=>$displayafterlogin, '0'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
        
    $ADMIN->add('theme_matrix', $temp);
    

	/* Marketing Spot Settings */
	$temp = new admin_settingpage('theme_matrix_marketing', get_string('marketingheading', 'theme_matrix'));
	$temp->add(new admin_setting_heading('theme_matrix_marketing', get_string('marketingheadingsub', 'theme_matrix'),
            format_text(get_string('marketingdesc' , 'theme_matrix'), FORMAT_MARKDOWN)));
	
	// Toggle Marketing Spots.
    $name = 'theme_matrix/togglemarketing';
    $title = get_string('togglemarketing' , 'theme_matrix');
    $description = get_string('togglemarketingdesc', 'theme_matrix');
    $alwaysdisplay = get_string('alwaysdisplay', 'theme_matrix');
    $displaybeforelogin = get_string('displaybeforelogin', 'theme_matrix');
    $displayafterlogin = get_string('displayafterlogin', 'theme_matrix');
    $dontdisplay = get_string('dontdisplay', 'theme_matrix');
    $default = 'display';
    $choices = array('1'=>$alwaysdisplay, '2'=>$displaybeforelogin, '3'=>$displayafterlogin, '0'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Marketing Spot Image Height
	$name = 'theme_matrix/marketingheight';
	$title = get_string('marketingheight','theme_matrix');
	$description = get_string('marketingheightdesc', 'theme_matrix');
	$default = 100;
	$choices = array(50, 100, 150, 200, 250, 300);
	$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
	$temp->add($setting);
	
	//This is the descriptor for Marketing Spot One
    $name = 'theme_matrix/marketing1info';
    $heading = get_string('marketing1', 'theme_matrix');
    $information = get_string('marketinginfodesc', 'theme_matrix');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
	
	//Marketing Spot One.
	$name = 'theme_matrix/marketing1';
    $title = get_string('marketingtitle', 'theme_matrix');
    $description = get_string('marketingtitledesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_matrix/marketing1icon';
    $title = get_string('marketingicon', 'theme_matrix');
    $description = get_string('marketingicondesc', 'theme_matrix');
    $default = 'star';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_matrix/marketing1image';
    $title = get_string('marketingimage', 'theme_matrix');
    $description = get_string('marketingimagedesc', 'theme_matrix');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing1image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_matrix/marketing1content';
    $title = get_string('marketingcontent', 'theme_matrix');
    $description = get_string('marketingcontentdesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_matrix/marketing1buttontext';
    $title = get_string('marketingbuttontext', 'theme_matrix');
    $description = get_string('marketingbuttontextdesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_matrix/marketing1buttonurl';
    $title = get_string('marketingbuttonurl', 'theme_matrix');
    $description = get_string('marketingbuttonurldesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for Marketing Spot Two
    $name = 'theme_matrix/marketing2info';
    $heading = get_string('marketing2', 'theme_matrix');
    $information = get_string('marketinginfodesc', 'theme_matrix');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    //Marketing Spot Two.
	$name = 'theme_matrix/marketing2';
    $title = get_string('marketingtitle', 'theme_matrix');
    $description = get_string('marketingtitledesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_matrix/marketing2icon';
    $title = get_string('marketingicon', 'theme_matrix');
    $description = get_string('marketingicondesc', 'theme_matrix');
    $default = 'star';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_matrix/marketing2image';
    $title = get_string('marketingimage', 'theme_matrix');
    $description = get_string('marketingimagedesc', 'theme_matrix');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing2image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_matrix/marketing2content';
    $title = get_string('marketingcontent', 'theme_matrix');
    $description = get_string('marketingcontentdesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_matrix/marketing2buttontext';
    $title = get_string('marketingbuttontext', 'theme_matrix');
    $description = get_string('marketingbuttontextdesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_matrix/marketing2buttonurl';
    $title = get_string('marketingbuttonurl', 'theme_matrix');
    $description = get_string('marketingbuttonurldesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for Marketing Spot Three
    $name = 'theme_matrix/marketing3info';
    $heading = get_string('marketing3', 'theme_matrix');
    $information = get_string('marketinginfodesc', 'theme_matrix');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    //Marketing Spot Three.
	$name = 'theme_matrix/marketing3';
    $title = get_string('marketingtitle', 'theme_matrix');
    $description = get_string('marketingtitledesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_matrix/marketing3icon';
    $title = get_string('marketingicon', 'theme_matrix');
    $description = get_string('marketingicondesc', 'theme_matrix');
    $default = 'star';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_matrix/marketing3image';
    $title = get_string('marketingimage', 'theme_matrix');
    $description = get_string('marketingimagedesc', 'theme_matrix');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing3image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_matrix/marketing3content';
    $title = get_string('marketingcontent', 'theme_matrix');
    $description = get_string('marketingcontentdesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_matrix/marketing3buttontext';
    $title = get_string('marketingbuttontext', 'theme_matrix');
    $description = get_string('marketingbuttontextdesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_matrix/marketing3buttonurl';
    $title = get_string('marketingbuttonurl', 'theme_matrix');
    $description = get_string('marketingbuttonurldesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    
    $ADMIN->add('theme_matrix', $temp);

	
	/* Social Network Settings */
	$temp = new admin_settingpage('theme_matrix_social', get_string('socialheading', 'theme_matrix'));
	$temp->add(new admin_setting_heading('theme_matrix_social', get_string('socialheadingsub', 'theme_matrix'),
            format_text(get_string('socialdesc' , 'theme_matrix'), FORMAT_MARKDOWN)));
	
    // Website url setting.
    $name = 'theme_matrix/website';
    $title = get_string('website', 'theme_matrix');
    $description = get_string('websitedesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Facebook url setting.
    $name = 'theme_matrix/facebook';
    $title = get_string('facebook', 'theme_matrix');
    $description = get_string('facebookdesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Flickr url setting.
    $name = 'theme_matrix/flickr';
    $title = get_string('flickr', 'theme_matrix');
    $description = get_string('flickrdesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Twitter url setting.
    $name = 'theme_matrix/twitter';
    $title = get_string('twitter', 'theme_matrix');
    $description = get_string('twitterdesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Google+ url setting.
    $name = 'theme_matrix/googleplus';
    $title = get_string('googleplus', 'theme_matrix');
    $description = get_string('googleplusdesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // LinkedIn url setting.
    $name = 'theme_matrix/linkedin';
    $title = get_string('linkedin', 'theme_matrix');
    $description = get_string('linkedindesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Pinterest url setting.
    $name = 'theme_matrix/pinterest';
    $title = get_string('pinterest', 'theme_matrix');
    $description = get_string('pinterestdesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Instagram url setting.
    $name = 'theme_matrix/instagram';
    $title = get_string('instagram', 'theme_matrix');
    $description = get_string('instagramdesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // YouTube url setting.
    $name = 'theme_matrix/youtube';
    $title = get_string('youtube', 'theme_matrix');
    $description = get_string('youtubedesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Skype url setting.
    $name = 'theme_matrix/skype';
    $title = get_string('skype', 'theme_matrix');
    $description = get_string('skypedesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
 
    // VKontakte url setting.
    $name = 'theme_matrix/vk';
    $title = get_string('vk', 'theme_matrix');
    $description = get_string('vkdesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting); 
    
    $ADMIN->add('theme_matrix', $temp);
    
    $temp = new admin_settingpage('theme_matrix_mobileapps', get_string('mobileappsheading', 'theme_matrix'));
	$temp->add(new admin_setting_heading('theme_matrix_mobileapps', get_string('mobileappsheadingsub', 'theme_matrix'),
            format_text(get_string('mobileappsdesc' , 'theme_matrix'), FORMAT_MARKDOWN)));
    // Android App url setting.
    $name = 'theme_matrix/android';
    $title = get_string('android', 'theme_matrix');
    $description = get_string('androiddesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iOS App url setting.
    $name = 'theme_matrix/ios';
    $title = get_string('ios', 'theme_matrix');
    $description = get_string('iosdesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for iOS Icons
    $name = 'theme_matrix/iosiconinfo';
    $heading = get_string('iosicon', 'theme_matrix');
    $information = get_string('iosicondesc', 'theme_matrix');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // iPhone Icon.
    $name = 'theme_matrix/iphoneicon';
    $title = get_string('iphoneicon', 'theme_matrix');
    $description = get_string('iphoneicondesc', 'theme_matrix');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'iphoneicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iPhone Retina Icon.
    $name = 'theme_matrix/iphoneretinaicon';
    $title = get_string('iphoneretinaicon', 'theme_matrix');
    $description = get_string('iphoneretinaicondesc', 'theme_matrix');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'iphoneretinaicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iPad Icon.
    $name = 'theme_matrix/ipadicon';
    $title = get_string('ipadicon', 'theme_matrix');
    $description = get_string('ipadicondesc', 'theme_matrix');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'ipadicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iPad Retina Icon.
    $name = 'theme_matrix/ipadretinaicon';
    $title = get_string('ipadretinaicon', 'theme_matrix');
    $description = get_string('ipadretinaicondesc', 'theme_matrix');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'ipadretinaicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $ADMIN->add('theme_matrix', $temp);
    
    /* User Alerts */
    $temp = new admin_settingpage('theme_matrix_alerts', get_string('alertsheading', 'theme_matrix'));
	$temp->add(new admin_setting_heading('theme_matrix_alerts', get_string('alertsheadingsub', 'theme_matrix'),
            format_text(get_string('alertsdesc' , 'theme_matrix'), FORMAT_MARKDOWN)));
    
    //This is the descriptor for Alert One
    $name = 'theme_matrix/alert1info';
    $heading = get_string('alert1', 'theme_matrix');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Enable Alert
    $name = 'theme_matrix/enable1alert';
    $title = get_string('enablealert', 'theme_matrix');
    $description = get_string('enablealertdesc', 'theme_matrix');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Type.
    $name = 'theme_matrix/alert1type';
    $title = get_string('alerttype' , 'theme_matrix');
    $description = get_string('alerttypedesc', 'theme_matrix');
    $alert_info = get_string('alert_info', 'theme_matrix');
    $alert_warning = get_string('alert_warning', 'theme_matrix');
    $alert_general = get_string('alert_general', 'theme_matrix');
    $default = 'info';
    $choices = array('info'=>$alert_info, 'error'=>$alert_warning, 'success'=>$alert_general);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Title.
    $name = 'theme_matrix/alert1title';
    $title = get_string('alerttitle', 'theme_matrix');
    $description = get_string('alerttitledesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Text.
    $name = 'theme_matrix/alert1text';
    $title = get_string('alerttext', 'theme_matrix');
    $description = get_string('alerttextdesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for Alert Two
    $name = 'theme_matrix/alert2info';
    $heading = get_string('alert2', 'theme_matrix');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Enable Alert
    $name = 'theme_matrix/enable2alert';
    $title = get_string('enablealert', 'theme_matrix');
    $description = get_string('enablealertdesc', 'theme_matrix');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Type.
    $name = 'theme_matrix/alert2type';
    $title = get_string('alerttype' , 'theme_matrix');
    $description = get_string('alerttypedesc', 'theme_matrix');
    $alert_info = get_string('alert_info', 'theme_matrix');
    $alert_warning = get_string('alert_warning', 'theme_matrix');
    $alert_general = get_string('alert_general', 'theme_matrix');
    $default = 'info';
    $choices = array('info'=>$alert_info, 'error'=>$alert_warning, 'success'=>$alert_general);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Title.
    $name = 'theme_matrix/alert2title';
    $title = get_string('alerttitle', 'theme_matrix');
    $description = get_string('alerttitledesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Text.
    $name = 'theme_matrix/alert2text';
    $title = get_string('alerttext', 'theme_matrix');
    $description = get_string('alerttextdesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for Alert Three
    $name = 'theme_matrix/alert3info';
    $heading = get_string('alert3', 'theme_matrix');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Enable Alert
    $name = 'theme_matrix/enable3alert';
    $title = get_string('enablealert', 'theme_matrix');
    $description = get_string('enablealertdesc', 'theme_matrix');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Type.
    $name = 'theme_matrix/alert3type';
    $title = get_string('alerttype' , 'theme_matrix');
    $description = get_string('alerttypedesc', 'theme_matrix');
    $alert_info = get_string('alert_info', 'theme_matrix');
    $alert_warning = get_string('alert_warning', 'theme_matrix');
    $alert_general = get_string('alert_general', 'theme_matrix');
    $default = 'info';
    $choices = array('info'=>$alert_info, 'error'=>$alert_warning, 'success'=>$alert_general);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Title.
    $name = 'theme_matrix/alert3title';
    $title = get_string('alerttitle', 'theme_matrix');
    $description = get_string('alerttitledesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Text.
    $name = 'theme_matrix/alert3text';
    $title = get_string('alerttext', 'theme_matrix');
    $description = get_string('alerttextdesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
            
    
    $ADMIN->add('theme_matrix', $temp);
    
    /* Analytics Settings */
    $temp = new admin_settingpage('theme_matrix_analytics', get_string('analyticsheading', 'theme_matrix'));
	$temp->add(new admin_setting_heading('theme_matrix_analytics', get_string('analyticsheadingsub', 'theme_matrix'),
            format_text(get_string('analyticsdesc' , 'theme_matrix'), FORMAT_MARKDOWN)));
    
    // Enable Analytics
    $name = 'theme_matrix/useanalytics';
    $title = get_string('useanalytics', 'theme_matrix');
    $description = get_string('useanalyticsdesc', 'theme_matrix');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Google Analytics ID
    $name = 'theme_matrix/analyticsid';
    $title = get_string('analyticsid', 'theme_matrix');
    $description = get_string('analyticsiddesc', 'theme_matrix');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Clean Analytics URL
    $name = 'theme_matrix/analyticsclean';
    $title = get_string('analyticsclean', 'theme_matrix');
    $description = get_string('analyticscleandesc', 'theme_matrix');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
        
    $ADMIN->add('theme_matrix', $temp);

