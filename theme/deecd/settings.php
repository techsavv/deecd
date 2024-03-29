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
 * @package   theme_deecd
 * @copyright 2013 Julian Ridden
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$settings = null;

defined('MOODLE_INTERNAL') || die;


	$ADMIN->add('themes', new admin_category('theme_deecd', 'deecd'));

	// "geneicsettings" settingpage
	$temp = new admin_settingpage('theme_deecd_generic',  get_string('geneicsettings', 'theme_deecd'));
	
	// Default Site icon setting.
    $name = 'theme_deecd/siteicon';
    $title = get_string('siteicon', 'theme_deecd');
    $description = get_string('siteicondesc', 'theme_deecd');
    $default = 'laptop';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $temp->add($setting);
    
    // Include Awesome Font from Bootstrapcdn
    $name = 'theme_deecd/bootstrapcdn';
    $title = get_string('bootstrapcdn', 'theme_deecd');
    $description = get_string('bootstrapcdndesc', 'theme_deecd');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
	
    // Logo file setting.
    $name = 'theme_deecd/logo';
    $title = get_string('logo', 'theme_deecd');
    $description = get_string('logodesc', 'theme_deecd');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'logo');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Font Selector.
    $name = 'theme_deecd/fontselect';
    $title = get_string('fontselect' , 'theme_deecd');
    $description = get_string('fontselectdesc', 'theme_deecd');
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
    $name = 'theme_deecd/headerprofilepic';
    $title = get_string('headerprofilepic', 'theme_deecd');
    $description = get_string('headerprofilepicdesc', 'theme_deecd');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Fixed or Variable Width.
    $name = 'theme_deecd/pagewidth';
    $title = get_string('pagewidth', 'theme_deecd');
    $description = get_string('pagewidthdesc', 'theme_deecd');
    $default = 1200;
    $choices = array(1900=>get_string('fixedwidthwide','theme_deecd'), 1200=>get_string('fixedwidthnarrow','theme_deecd'), 100=>get_string('variablewidth','theme_deecd'));
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Custom or standard layout.
    $name = 'theme_deecd/layout';
    $title = get_string('layout', 'theme_deecd');
    $description = get_string('layoutdesc', 'theme_deecd');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //Include the Editicons css rules
    $name = 'theme_deecd/editicons';
    $title = get_string('editicons', 'theme_deecd');
    $description = get_string('editiconsdesc', 'theme_deecd');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $temp->add($setting);
    
    // Performance Information Display.
    $name = 'theme_deecd/perfinfo';
    $title = get_string('perfinfo' , 'theme_deecd');
    $description = get_string('perfinfodesc', 'theme_deecd');
    $perf_max = get_string('perf_max', 'theme_deecd');
    $perf_min = get_string('perf_min', 'theme_deecd');
    $default = 'min';
    $choices = array('min'=>$perf_min, 'max'=>$perf_max);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Navbar Seperator.
    $name = 'theme_deecd/navbarsep';
    $title = get_string('navbarsep' , 'theme_deecd');
    $description = get_string('navbarsepdesc', 'theme_deecd');
    $nav_thinbracket = get_string('nav_thinbracket', 'theme_deecd');
    $nav_doublebracket = get_string('nav_doublebracket', 'theme_deecd');
    $nav_thickbracket = get_string('nav_thickbracket', 'theme_deecd');
    $nav_slash = get_string('nav_slash', 'theme_deecd');
    $nav_pipe = get_string('nav_pipe', 'theme_deecd');
    $dontdisplay = get_string('dontdisplay', 'theme_deecd');
    $default = '/';
    $choices = array('/'=>$nav_slash, '\f105'=>$nav_thinbracket, '\f101'=>$nav_doublebracket, '\f054'=>$nav_thickbracket, '|'=>$nav_pipe);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Copyright setting.
    $name = 'theme_deecd/copyright';
    $title = get_string('copyright', 'theme_deecd');
    $description = get_string('copyrightdesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $temp->add($setting);
    
    // Footnote setting.
    $name = 'theme_deecd/footnote';
    $title = get_string('footnote', 'theme_deecd');
    $description = get_string('footnotedesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Custom CSS file.
    $name = 'theme_deecd/customcss';
    $title = get_string('customcss', 'theme_deecd');
    $description = get_string('customcssdesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $ADMIN->add('theme_deecd', $temp);
    
    /* Custom Menu Settings */
    $temp = new admin_settingpage('theme_deecd_custommenu', get_string('custommenuheading', 'theme_deecd'));
	            
    //This is the descriptor for the following Moodle color settings
    $name = 'theme_deecd/mydashboardinfo';
    $heading = get_string('mydashboardinfo', 'theme_deecd');
    $information = get_string('mydashboardinfodesc', 'theme_deecd');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Toggle dashboard display in custommenu.
    $name = 'theme_deecd/displaymydashboard';
    $title = get_string('displaymydashboard', 'theme_deecd');
    $description = get_string('displaymydashboarddesc', 'theme_deecd');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for the following Moodle color settings
    $name = 'theme_deecd/mycoursesinfo';
    $heading = get_string('mycoursesinfo', 'theme_deecd');
    $information = get_string('mycoursesinfodesc', 'theme_deecd');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Toggle courses display in custommenu.
    $name = 'theme_deecd/displaymycourses';
    $title = get_string('displaymycourses', 'theme_deecd');
    $description = get_string('displaymycoursesdesc', 'theme_deecd');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Set terminology for dropdown course list
	$name = 'theme_deecd/mycoursetitle';
	$title = get_string('mycoursetitle','theme_deecd');
	$description = get_string('mycoursetitledesc', 'theme_deecd');
	$default = 'course';
	$choices = array(
		'course' => get_string('mycourses', 'theme_deecd'),
		'unit' => get_string('myunits', 'theme_deecd'),
		'class' => get_string('myclasses', 'theme_deecd'),
		'module' => get_string('mymodules', 'theme_deecd')
	);
	$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
	$setting->set_updatedcallback('theme_reset_all_caches');
	$temp->add($setting);
    
    $ADMIN->add('theme_deecd', $temp);
    
	/* Color Settings */
    $temp = new admin_settingpage('theme_deecd_color', get_string('colorheading', 'theme_deecd'));
    $temp->add(new admin_setting_heading('theme_deecd_color', get_string('colorheadingsub', 'theme_deecd'),
            format_text(get_string('colordesc' , 'theme_deecd'), FORMAT_MARKDOWN)));

    // Background Image.
    $name = 'theme_deecd/pagebackground';
    $title = get_string('pagebackground', 'theme_deecd');
    $description = get_string('pagebackgrounddesc', 'theme_deecd');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'pagebackground');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Main theme colour setting.
    $name = 'theme_deecd/themecolor';
    $title = get_string('themecolor', 'theme_deecd');
    $description = get_string('themecolordesc', 'theme_deecd');
    $default = '#30add1';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Main theme Hover colour setting.
    $name = 'theme_deecd/themehovercolor';
    $title = get_string('themehovercolor', 'theme_deecd');
    $description = get_string('themehovercolordesc', 'theme_deecd');
    $default = '#29a1c4';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for the Slideshow
    $name = 'theme_deecd/slidecolorinfo';
    $heading = get_string('slidecolors', 'theme_deecd');
    $information = get_string('slidecolorsdesc', 'theme_deecd');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
      // Slide Header colour setting.
    $name = 'theme_deecd/slideheadercolor';
    $title = get_string('slideheadercolor', 'theme_deecd');
    $description = get_string('slideheadercolordesc', 'theme_deecd');
    $default = '#30add1';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Slide Text colour setting.
    $name = 'theme_deecd/slidecolor';
    $title = get_string('slidecolor', 'theme_deecd');
    $description = get_string('slidecolordesc', 'theme_deecd');
    $default = '#888';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Slide Button colour setting.
    $name = 'theme_deecd/slidebuttoncolor';
    $title = get_string('slidebuttoncolor', 'theme_deecd');
    $description = get_string('slidebuttoncolordesc', 'theme_deecd');
    $default = '#30add1';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
        //This is the descriptor for the Slideshow
    $name = 'theme_deecd/footercolorinfo';
    $heading = get_string('footercolors', 'theme_deecd');
    $information = get_string('footercolorsdesc', 'theme_deecd');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Footer background colour setting.
    $name = 'theme_deecd/footercolor';
    $title = get_string('footercolor', 'theme_deecd');
    $description = get_string('footercolordesc', 'theme_deecd');
    $default = '#000000';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer text colour setting.
    $name = 'theme_deecd/footertextcolor';
    $title = get_string('footertextcolor', 'theme_deecd');
    $description = get_string('footertextcolordesc', 'theme_deecd');
    $default = '#DDDDDD';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer Block Heading colour setting.
    $name = 'theme_deecd/footerheadingcolor';
    $title = get_string('footerheadingcolor', 'theme_deecd');
    $description = get_string('footerheadingcolordesc', 'theme_deecd');
    $default = '#CCCCCC';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer Seperator colour setting.
    $name = 'theme_deecd/footersepcolor';
    $title = get_string('footersepcolor', 'theme_deecd');
    $description = get_string('footersepcolordesc', 'theme_deecd');
    $default = '#313131';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer URL colour setting.
    $name = 'theme_deecd/footerurlcolor';
    $title = get_string('footerurlcolor', 'theme_deecd');
    $description = get_string('footerurlcolordesc', 'theme_deecd');
    $default = '#BBBBBB';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer URL hover colour setting.
    $name = 'theme_deecd/footerhovercolor';
    $title = get_string('footerhovercolor', 'theme_deecd');
    $description = get_string('footerhovercolordesc', 'theme_deecd');
    $default = '#FFFFFF';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);



 	$ADMIN->add('theme_deecd', $temp);
 
 
    /* Slideshow Widget Settings */
    $temp = new admin_settingpage('theme_deecd_slideshow', get_string('slideshowheading', 'theme_deecd'));
    $temp->add(new admin_setting_heading('theme_deecd_slideshow', get_string('slideshowheadingsub', 'theme_deecd'),
            format_text(get_string('slideshowdesc' , 'theme_deecd'), FORMAT_MARKDOWN)));
    
    // Toggle Slideshow.
    $name = 'theme_deecd/toggleslideshow';
    $title = get_string('toggleslideshow' , 'theme_deecd');
    $description = get_string('toggleslideshowdesc', 'theme_deecd');
    $alwaysdisplay = get_string('alwaysdisplay', 'theme_deecd');
    $displaybeforelogin = get_string('displaybeforelogin', 'theme_deecd');
    $displayafterlogin = get_string('displayafterlogin', 'theme_deecd');
    $dontdisplay = get_string('dontdisplay', 'theme_deecd');
    $default = 'alwaysdisplay';
    $choices = array('1'=>$alwaysdisplay, '2'=>$displaybeforelogin, '3'=>$displayafterlogin, '0'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Hide slideshow on phones.
    $name = 'theme_deecd/hideonphone';
    $title = get_string('hideonphone' , 'theme_deecd');
    $description = get_string('hideonphonedesc', 'theme_deecd');
    $display = get_string('alwaysdisplay', 'theme_deecd');
    $dontdisplay = get_string('dontdisplay', 'theme_deecd');
    $default = 'display';
    $choices = array(''=>$display, 'hidden-phone'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Slideshow Design Picker.
    $name = 'theme_deecd/slideshowvariant';
    $title = get_string('slideshowvariant' , 'theme_deecd');
    $description = get_string('slideshowvariantdesc', 'theme_deecd');
    $slideshow1 = get_string('slideshow1', 'theme_deecd');
    $slideshow2 = get_string('slideshow2', 'theme_deecd');
    $default = 'slideshow1';
    $choices = array('1'=>$slideshow1, '2'=>$slideshow2);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 1
     */
     
    //This is the descriptor for Slide One
    $name = 'theme_deecd/slide1info';
    $heading = get_string('slide1', 'theme_deecd');
    $information = get_string('slideinfodesc', 'theme_deecd');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);

    // Title.
    $name = 'theme_deecd/slide1';
    $title = get_string('slidetitle', 'theme_deecd');
    $description = get_string('slidetitledesc', 'theme_deecd');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_deecd/slide1image';
    $title = get_string('slideimage', 'theme_deecd');
    $description = get_string('slideimagedesc', 'theme_deecd');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide1image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_deecd/slide1caption';
    $title = get_string('slidecaption', 'theme_deecd');
    $description = get_string('slidecaptiondesc', 'theme_deecd');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_deecd/slide1url';
    $title = get_string('slideurl', 'theme_deecd');
    $description = get_string('slideurldesc', 'theme_deecd');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 2
     */
     
    //This is the descriptor for Slide Two
    $name = 'theme_deecd/slide2info';
    $heading = get_string('slide2', 'theme_deecd');
    $information = get_string('slideinfodesc', 'theme_deecd');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);

    // Title.
    $name = 'theme_deecd/slide2';
    $title = get_string('slidetitle', 'theme_deecd');
    $description = get_string('slidetitledesc', 'theme_deecd');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_deecd/slide2image';
    $title = get_string('slideimage', 'theme_deecd');
    $description = get_string('slideimagedesc', 'theme_deecd');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide2image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_deecd/slide2caption';
    $title = get_string('slidecaption', 'theme_deecd');
    $description = get_string('slidecaptiondesc', 'theme_deecd');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_deecd/slide2url';
    $title = get_string('slideurl', 'theme_deecd');
    $description = get_string('slideurldesc', 'theme_deecd');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 3
     */

    //This is the descriptor for Slide Three
    $name = 'theme_deecd/slide3info';
    $heading = get_string('slide3', 'theme_deecd');
    $information = get_string('slideinfodesc', 'theme_deecd');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Title.
    $name = 'theme_deecd/slide3';
    $title = get_string('slidetitle', 'theme_deecd');
    $description = get_string('slidetitledesc', 'theme_deecd');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_deecd/slide3image';
    $title = get_string('slideimage', 'theme_deecd');
    $description = get_string('slideimagedesc', 'theme_deecd');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide3image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_deecd/slide3caption';
    $title = get_string('slidecaption', 'theme_deecd');
    $description = get_string('slidecaptiondesc', 'theme_deecd');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_deecd/slide3url';
    $title = get_string('slideurl', 'theme_deecd');
    $description = get_string('slideurldesc', 'theme_deecd');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 4
     */
     
    //This is the descriptor for Slide Four
    $name = 'theme_deecd/slide4info';
    $heading = get_string('slide4', 'theme_deecd');
    $information = get_string('slideinfodesc', 'theme_deecd');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);

    // Title.
    $name = 'theme_deecd/slide4';
    $title = get_string('slidetitle', 'theme_deecd');
    $description = get_string('slidetitledesc', 'theme_deecd');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_deecd/slide4image';
    $title = get_string('slideimage', 'theme_deecd');
    $description = get_string('slideimagedesc', 'theme_deecd');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide4image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_deecd/slide4caption';
    $title = get_string('slidecaption', 'theme_deecd');
    $description = get_string('slidecaptiondesc', 'theme_deecd');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_deecd/slide4url';
    $title = get_string('slideurl', 'theme_deecd');
    $description = get_string('slideurldesc', 'theme_deecd');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    
    $ADMIN->add('theme_deecd', $temp);
    
    $temp = new admin_settingpage('theme_deecd_frontcontent', get_string('frontcontentheading', 'theme_deecd'));
	$temp->add(new admin_setting_heading('theme_deecd_frontcontent', get_string('frontcontentheadingsub', 'theme_deecd'),
            format_text(get_string('frontcontentdesc' , 'theme_deecd'), FORMAT_MARKDOWN)));
    
    // Enable Frontpage Content
    $name = 'theme_deecd/usefrontcontent';
    $title = get_string('usefrontcontent', 'theme_deecd');
    $description = get_string('usefrontcontentdesc', 'theme_deecd');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Frontpage Content
    $name = 'theme_deecd/frontcontentarea';
    $title = get_string('frontcontentarea', 'theme_deecd');
    $description = get_string('frontcontentareadesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Frontpage Block alignment.
    $name = 'theme_deecd/frontpageblocks';
    $title = get_string('frontpageblocks' , 'theme_deecd');
    $description = get_string('frontpageblocksdesc', 'theme_deecd');
    $left = get_string('left', 'theme_deecd');
    $right = get_string('right', 'theme_deecd');
    $default = 'left';
    $choices = array('1'=>$left, '0'=>$right);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Toggle Frontpage Middle Blocks
    $name = 'theme_deecd/frontpagemiddleblocks';
    $title = get_string('frontpagemiddleblocks' , 'theme_deecd');
    $description = get_string('frontpagemiddleblocksdesc', 'theme_deecd');
    $alwaysdisplay = get_string('alwaysdisplay', 'theme_deecd');
    $displaybeforelogin = get_string('displaybeforelogin', 'theme_deecd');
    $displayafterlogin = get_string('displayafterlogin', 'theme_deecd');
    $dontdisplay = get_string('dontdisplay', 'theme_deecd');
    $default = 'display';
    $choices = array('1'=>$alwaysdisplay, '2'=>$displaybeforelogin, '3'=>$displayafterlogin, '0'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
        
    $ADMIN->add('theme_deecd', $temp);
    

	/* Marketing Spot Settings */
	$temp = new admin_settingpage('theme_deecd_marketing', get_string('marketingheading', 'theme_deecd'));
	$temp->add(new admin_setting_heading('theme_deecd_marketing', get_string('marketingheadingsub', 'theme_deecd'),
            format_text(get_string('marketingdesc' , 'theme_deecd'), FORMAT_MARKDOWN)));
	
	// Toggle Marketing Spots.
    $name = 'theme_deecd/togglemarketing';
    $title = get_string('togglemarketing' , 'theme_deecd');
    $description = get_string('togglemarketingdesc', 'theme_deecd');
    $alwaysdisplay = get_string('alwaysdisplay', 'theme_deecd');
    $displaybeforelogin = get_string('displaybeforelogin', 'theme_deecd');
    $displayafterlogin = get_string('displayafterlogin', 'theme_deecd');
    $dontdisplay = get_string('dontdisplay', 'theme_deecd');
    $default = 'display';
    $choices = array('1'=>$alwaysdisplay, '2'=>$displaybeforelogin, '3'=>$displayafterlogin, '0'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Marketing Spot Image Height
	$name = 'theme_deecd/marketingheight';
	$title = get_string('marketingheight','theme_deecd');
	$description = get_string('marketingheightdesc', 'theme_deecd');
	$default = 100;
	$choices = array(50, 100, 150, 200, 250, 300);
	$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
	$temp->add($setting);
	
	//This is the descriptor for Marketing Spot One
    $name = 'theme_deecd/marketing1info';
    $heading = get_string('marketing1', 'theme_deecd');
    $information = get_string('marketinginfodesc', 'theme_deecd');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
	
	//Marketing Spot One.
	$name = 'theme_deecd/marketing1';
    $title = get_string('marketingtitle', 'theme_deecd');
    $description = get_string('marketingtitledesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_deecd/marketing1icon';
    $title = get_string('marketingicon', 'theme_deecd');
    $description = get_string('marketingicondesc', 'theme_deecd');
    $default = 'star';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_deecd/marketing1image';
    $title = get_string('marketingimage', 'theme_deecd');
    $description = get_string('marketingimagedesc', 'theme_deecd');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing1image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_deecd/marketing1content';
    $title = get_string('marketingcontent', 'theme_deecd');
    $description = get_string('marketingcontentdesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_deecd/marketing1buttontext';
    $title = get_string('marketingbuttontext', 'theme_deecd');
    $description = get_string('marketingbuttontextdesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_deecd/marketing1buttonurl';
    $title = get_string('marketingbuttonurl', 'theme_deecd');
    $description = get_string('marketingbuttonurldesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for Marketing Spot Two
    $name = 'theme_deecd/marketing2info';
    $heading = get_string('marketing2', 'theme_deecd');
    $information = get_string('marketinginfodesc', 'theme_deecd');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    //Marketing Spot Two.
	$name = 'theme_deecd/marketing2';
    $title = get_string('marketingtitle', 'theme_deecd');
    $description = get_string('marketingtitledesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_deecd/marketing2icon';
    $title = get_string('marketingicon', 'theme_deecd');
    $description = get_string('marketingicondesc', 'theme_deecd');
    $default = 'star';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_deecd/marketing2image';
    $title = get_string('marketingimage', 'theme_deecd');
    $description = get_string('marketingimagedesc', 'theme_deecd');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing2image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_deecd/marketing2content';
    $title = get_string('marketingcontent', 'theme_deecd');
    $description = get_string('marketingcontentdesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_deecd/marketing2buttontext';
    $title = get_string('marketingbuttontext', 'theme_deecd');
    $description = get_string('marketingbuttontextdesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_deecd/marketing2buttonurl';
    $title = get_string('marketingbuttonurl', 'theme_deecd');
    $description = get_string('marketingbuttonurldesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for Marketing Spot Three
    $name = 'theme_deecd/marketing3info';
    $heading = get_string('marketing3', 'theme_deecd');
    $information = get_string('marketinginfodesc', 'theme_deecd');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    //Marketing Spot Three.
	$name = 'theme_deecd/marketing3';
    $title = get_string('marketingtitle', 'theme_deecd');
    $description = get_string('marketingtitledesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_deecd/marketing3icon';
    $title = get_string('marketingicon', 'theme_deecd');
    $description = get_string('marketingicondesc', 'theme_deecd');
    $default = 'star';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_deecd/marketing3image';
    $title = get_string('marketingimage', 'theme_deecd');
    $description = get_string('marketingimagedesc', 'theme_deecd');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing3image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_deecd/marketing3content';
    $title = get_string('marketingcontent', 'theme_deecd');
    $description = get_string('marketingcontentdesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_deecd/marketing3buttontext';
    $title = get_string('marketingbuttontext', 'theme_deecd');
    $description = get_string('marketingbuttontextdesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_deecd/marketing3buttonurl';
    $title = get_string('marketingbuttonurl', 'theme_deecd');
    $description = get_string('marketingbuttonurldesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    
    $ADMIN->add('theme_deecd', $temp);

	
	/* Social Network Settings */
	$temp = new admin_settingpage('theme_deecd_social', get_string('socialheading', 'theme_deecd'));
	$temp->add(new admin_setting_heading('theme_deecd_social', get_string('socialheadingsub', 'theme_deecd'),
            format_text(get_string('socialdesc' , 'theme_deecd'), FORMAT_MARKDOWN)));
	
    // Website url setting.
    $name = 'theme_deecd/website';
    $title = get_string('website', 'theme_deecd');
    $description = get_string('websitedesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Facebook url setting.
    $name = 'theme_deecd/facebook';
    $title = get_string('facebook', 'theme_deecd');
    $description = get_string('facebookdesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Flickr url setting.
    $name = 'theme_deecd/flickr';
    $title = get_string('flickr', 'theme_deecd');
    $description = get_string('flickrdesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Twitter url setting.
    $name = 'theme_deecd/twitter';
    $title = get_string('twitter', 'theme_deecd');
    $description = get_string('twitterdesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Google+ url setting.
    $name = 'theme_deecd/googleplus';
    $title = get_string('googleplus', 'theme_deecd');
    $description = get_string('googleplusdesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // LinkedIn url setting.
    $name = 'theme_deecd/linkedin';
    $title = get_string('linkedin', 'theme_deecd');
    $description = get_string('linkedindesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Pinterest url setting.
    $name = 'theme_deecd/pinterest';
    $title = get_string('pinterest', 'theme_deecd');
    $description = get_string('pinterestdesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Instagram url setting.
    $name = 'theme_deecd/instagram';
    $title = get_string('instagram', 'theme_deecd');
    $description = get_string('instagramdesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // YouTube url setting.
    $name = 'theme_deecd/youtube';
    $title = get_string('youtube', 'theme_deecd');
    $description = get_string('youtubedesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Skype url setting.
    $name = 'theme_deecd/skype';
    $title = get_string('skype', 'theme_deecd');
    $description = get_string('skypedesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
 
    // VKontakte url setting.
    $name = 'theme_deecd/vk';
    $title = get_string('vk', 'theme_deecd');
    $description = get_string('vkdesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting); 
    
    $ADMIN->add('theme_deecd', $temp);
    
    $temp = new admin_settingpage('theme_deecd_mobileapps', get_string('mobileappsheading', 'theme_deecd'));
	$temp->add(new admin_setting_heading('theme_deecd_mobileapps', get_string('mobileappsheadingsub', 'theme_deecd'),
            format_text(get_string('mobileappsdesc' , 'theme_deecd'), FORMAT_MARKDOWN)));
    // Android App url setting.
    $name = 'theme_deecd/android';
    $title = get_string('android', 'theme_deecd');
    $description = get_string('androiddesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iOS App url setting.
    $name = 'theme_deecd/ios';
    $title = get_string('ios', 'theme_deecd');
    $description = get_string('iosdesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for iOS Icons
    $name = 'theme_deecd/iosiconinfo';
    $heading = get_string('iosicon', 'theme_deecd');
    $information = get_string('iosicondesc', 'theme_deecd');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // iPhone Icon.
    $name = 'theme_deecd/iphoneicon';
    $title = get_string('iphoneicon', 'theme_deecd');
    $description = get_string('iphoneicondesc', 'theme_deecd');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'iphoneicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iPhone Retina Icon.
    $name = 'theme_deecd/iphoneretinaicon';
    $title = get_string('iphoneretinaicon', 'theme_deecd');
    $description = get_string('iphoneretinaicondesc', 'theme_deecd');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'iphoneretinaicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iPad Icon.
    $name = 'theme_deecd/ipadicon';
    $title = get_string('ipadicon', 'theme_deecd');
    $description = get_string('ipadicondesc', 'theme_deecd');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'ipadicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iPad Retina Icon.
    $name = 'theme_deecd/ipadretinaicon';
    $title = get_string('ipadretinaicon', 'theme_deecd');
    $description = get_string('ipadretinaicondesc', 'theme_deecd');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'ipadretinaicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $ADMIN->add('theme_deecd', $temp);
    
    /* User Alerts */
    $temp = new admin_settingpage('theme_deecd_alerts', get_string('alertsheading', 'theme_deecd'));
	$temp->add(new admin_setting_heading('theme_deecd_alerts', get_string('alertsheadingsub', 'theme_deecd'),
            format_text(get_string('alertsdesc' , 'theme_deecd'), FORMAT_MARKDOWN)));
    
    //This is the descriptor for Alert One
    $name = 'theme_deecd/alert1info';
    $heading = get_string('alert1', 'theme_deecd');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Enable Alert
    $name = 'theme_deecd/enable1alert';
    $title = get_string('enablealert', 'theme_deecd');
    $description = get_string('enablealertdesc', 'theme_deecd');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Type.
    $name = 'theme_deecd/alert1type';
    $title = get_string('alerttype' , 'theme_deecd');
    $description = get_string('alerttypedesc', 'theme_deecd');
    $alert_info = get_string('alert_info', 'theme_deecd');
    $alert_warning = get_string('alert_warning', 'theme_deecd');
    $alert_general = get_string('alert_general', 'theme_deecd');
    $default = 'info';
    $choices = array('info'=>$alert_info, 'error'=>$alert_warning, 'success'=>$alert_general);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Title.
    $name = 'theme_deecd/alert1title';
    $title = get_string('alerttitle', 'theme_deecd');
    $description = get_string('alerttitledesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Text.
    $name = 'theme_deecd/alert1text';
    $title = get_string('alerttext', 'theme_deecd');
    $description = get_string('alerttextdesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for Alert Two
    $name = 'theme_deecd/alert2info';
    $heading = get_string('alert2', 'theme_deecd');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Enable Alert
    $name = 'theme_deecd/enable2alert';
    $title = get_string('enablealert', 'theme_deecd');
    $description = get_string('enablealertdesc', 'theme_deecd');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Type.
    $name = 'theme_deecd/alert2type';
    $title = get_string('alerttype' , 'theme_deecd');
    $description = get_string('alerttypedesc', 'theme_deecd');
    $alert_info = get_string('alert_info', 'theme_deecd');
    $alert_warning = get_string('alert_warning', 'theme_deecd');
    $alert_general = get_string('alert_general', 'theme_deecd');
    $default = 'info';
    $choices = array('info'=>$alert_info, 'error'=>$alert_warning, 'success'=>$alert_general);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Title.
    $name = 'theme_deecd/alert2title';
    $title = get_string('alerttitle', 'theme_deecd');
    $description = get_string('alerttitledesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Text.
    $name = 'theme_deecd/alert2text';
    $title = get_string('alerttext', 'theme_deecd');
    $description = get_string('alerttextdesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for Alert Three
    $name = 'theme_deecd/alert3info';
    $heading = get_string('alert3', 'theme_deecd');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Enable Alert
    $name = 'theme_deecd/enable3alert';
    $title = get_string('enablealert', 'theme_deecd');
    $description = get_string('enablealertdesc', 'theme_deecd');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Type.
    $name = 'theme_deecd/alert3type';
    $title = get_string('alerttype' , 'theme_deecd');
    $description = get_string('alerttypedesc', 'theme_deecd');
    $alert_info = get_string('alert_info', 'theme_deecd');
    $alert_warning = get_string('alert_warning', 'theme_deecd');
    $alert_general = get_string('alert_general', 'theme_deecd');
    $default = 'info';
    $choices = array('info'=>$alert_info, 'error'=>$alert_warning, 'success'=>$alert_general);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Title.
    $name = 'theme_deecd/alert3title';
    $title = get_string('alerttitle', 'theme_deecd');
    $description = get_string('alerttitledesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Text.
    $name = 'theme_deecd/alert3text';
    $title = get_string('alerttext', 'theme_deecd');
    $description = get_string('alerttextdesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
            
    
    $ADMIN->add('theme_deecd', $temp);
    
    /* Analytics Settings */
    $temp = new admin_settingpage('theme_deecd_analytics', get_string('analyticsheading', 'theme_deecd'));
	$temp->add(new admin_setting_heading('theme_deecd_analytics', get_string('analyticsheadingsub', 'theme_deecd'),
            format_text(get_string('analyticsdesc' , 'theme_deecd'), FORMAT_MARKDOWN)));
    
    // Enable Analytics
    $name = 'theme_deecd/useanalytics';
    $title = get_string('useanalytics', 'theme_deecd');
    $description = get_string('useanalyticsdesc', 'theme_deecd');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Google Analytics ID
    $name = 'theme_deecd/analyticsid';
    $title = get_string('analyticsid', 'theme_deecd');
    $description = get_string('analyticsiddesc', 'theme_deecd');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Clean Analytics URL
    $name = 'theme_deecd/analyticsclean';
    $title = get_string('analyticsclean', 'theme_deecd');
    $description = get_string('analyticscleandesc', 'theme_deecd');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
        
    $ADMIN->add('theme_deecd', $temp);

