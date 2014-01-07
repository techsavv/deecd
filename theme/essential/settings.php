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
 * @package   theme_mcb
 * @copyright 2013 Julian Ridden
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$settings = null;

defined('MOODLE_INTERNAL') || die;


	$ADMIN->add('themes', new admin_category('theme_mcb', 'mcb'));

	// "geneicsettings" settingpage
	$temp = new admin_settingpage('theme_mcb_generic',  get_string('geneicsettings', 'theme_mcb'));
	
	// Default Site icon setting.
    $name = 'theme_mcb/siteicon';
    $title = get_string('siteicon', 'theme_mcb');
    $description = get_string('siteicondesc', 'theme_mcb');
    $default = 'laptop';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $temp->add($setting);
    
    // Include Awesome Font from Bootstrapcdn
    $name = 'theme_mcb/bootstrapcdn';
    $title = get_string('bootstrapcdn', 'theme_mcb');
    $description = get_string('bootstrapcdndesc', 'theme_mcb');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
	
    // Logo file setting.
    $name = 'theme_mcb/logo';
    $title = get_string('logo', 'theme_mcb');
    $description = get_string('logodesc', 'theme_mcb');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'logo');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Font Selector.
    $name = 'theme_mcb/fontselect';
    $title = get_string('fontselect' , 'theme_mcb');
    $description = get_string('fontselectdesc', 'theme_mcb');
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
    $name = 'theme_mcb/headerprofilepic';
    $title = get_string('headerprofilepic', 'theme_mcb');
    $description = get_string('headerprofilepicdesc', 'theme_mcb');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Fixed or Variable Width.
    $name = 'theme_mcb/pagewidth';
    $title = get_string('pagewidth', 'theme_mcb');
    $description = get_string('pagewidthdesc', 'theme_mcb');
    $default = 1200;
    $choices = array(1900=>get_string('fixedwidthwide','theme_mcb'), 1200=>get_string('fixedwidthnarrow','theme_mcb'), 100=>get_string('variablewidth','theme_mcb'));
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Custom or standard layout.
    $name = 'theme_mcb/layout';
    $title = get_string('layout', 'theme_mcb');
    $description = get_string('layoutdesc', 'theme_mcb');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //Include the Editicons css rules
    $name = 'theme_mcb/editicons';
    $title = get_string('editicons', 'theme_mcb');
    $description = get_string('editiconsdesc', 'theme_mcb');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $temp->add($setting);
    
    // Performance Information Display.
    $name = 'theme_mcb/perfinfo';
    $title = get_string('perfinfo' , 'theme_mcb');
    $description = get_string('perfinfodesc', 'theme_mcb');
    $perf_max = get_string('perf_max', 'theme_mcb');
    $perf_min = get_string('perf_min', 'theme_mcb');
    $default = 'min';
    $choices = array('min'=>$perf_min, 'max'=>$perf_max);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Navbar Seperator.
    $name = 'theme_mcb/navbarsep';
    $title = get_string('navbarsep' , 'theme_mcb');
    $description = get_string('navbarsepdesc', 'theme_mcb');
    $nav_thinbracket = get_string('nav_thinbracket', 'theme_mcb');
    $nav_doublebracket = get_string('nav_doublebracket', 'theme_mcb');
    $nav_thickbracket = get_string('nav_thickbracket', 'theme_mcb');
    $nav_slash = get_string('nav_slash', 'theme_mcb');
    $nav_pipe = get_string('nav_pipe', 'theme_mcb');
    $dontdisplay = get_string('dontdisplay', 'theme_mcb');
    $default = '/';
    $choices = array('/'=>$nav_slash, '\f105'=>$nav_thinbracket, '\f101'=>$nav_doublebracket, '\f054'=>$nav_thickbracket, '|'=>$nav_pipe);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Copyright setting.
    $name = 'theme_mcb/copyright';
    $title = get_string('copyright', 'theme_mcb');
    $description = get_string('copyrightdesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $temp->add($setting);
    
    // Footnote setting.
    $name = 'theme_mcb/footnote';
    $title = get_string('footnote', 'theme_mcb');
    $description = get_string('footnotedesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Custom CSS file.
    $name = 'theme_mcb/customcss';
    $title = get_string('customcss', 'theme_mcb');
    $description = get_string('customcssdesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $ADMIN->add('theme_mcb', $temp);
    
    /* Custom Menu Settings */
    $temp = new admin_settingpage('theme_mcb_custommenu', get_string('custommenuheading', 'theme_mcb'));
	            
    //This is the descriptor for the following Moodle color settings
    $name = 'theme_mcb/mydashboardinfo';
    $heading = get_string('mydashboardinfo', 'theme_mcb');
    $information = get_string('mydashboardinfodesc', 'theme_mcb');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Toggle dashboard display in custommenu.
    $name = 'theme_mcb/displaymydashboard';
    $title = get_string('displaymydashboard', 'theme_mcb');
    $description = get_string('displaymydashboarddesc', 'theme_mcb');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for the following Moodle color settings
    $name = 'theme_mcb/mycoursesinfo';
    $heading = get_string('mycoursesinfo', 'theme_mcb');
    $information = get_string('mycoursesinfodesc', 'theme_mcb');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Toggle courses display in custommenu.
    $name = 'theme_mcb/displaymycourses';
    $title = get_string('displaymycourses', 'theme_mcb');
    $description = get_string('displaymycoursesdesc', 'theme_mcb');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Set terminology for dropdown course list
	$name = 'theme_mcb/mycoursetitle';
	$title = get_string('mycoursetitle','theme_mcb');
	$description = get_string('mycoursetitledesc', 'theme_mcb');
	$default = 'course';
	$choices = array(
		'course' => get_string('mycourses', 'theme_mcb'),
		'unit' => get_string('myunits', 'theme_mcb'),
		'class' => get_string('myclasses', 'theme_mcb'),
		'module' => get_string('mymodules', 'theme_mcb')
	);
	$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
	$setting->set_updatedcallback('theme_reset_all_caches');
	$temp->add($setting);
    
    $ADMIN->add('theme_mcb', $temp);
    
	/* Color Settings */
    $temp = new admin_settingpage('theme_mcb_color', get_string('colorheading', 'theme_mcb'));
    $temp->add(new admin_setting_heading('theme_mcb_color', get_string('colorheadingsub', 'theme_mcb'),
            format_text(get_string('colordesc' , 'theme_mcb'), FORMAT_MARKDOWN)));

    // Background Image.
    $name = 'theme_mcb/pagebackground';
    $title = get_string('pagebackground', 'theme_mcb');
    $description = get_string('pagebackgrounddesc', 'theme_mcb');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'pagebackground');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Main theme colour setting.
    $name = 'theme_mcb/themecolor';
    $title = get_string('themecolor', 'theme_mcb');
    $description = get_string('themecolordesc', 'theme_mcb');
    $default = '#30add1';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Main theme Hover colour setting.
    $name = 'theme_mcb/themehovercolor';
    $title = get_string('themehovercolor', 'theme_mcb');
    $description = get_string('themehovercolordesc', 'theme_mcb');
    $default = '#29a1c4';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for the Slideshow
    $name = 'theme_mcb/slidecolorinfo';
    $heading = get_string('slidecolors', 'theme_mcb');
    $information = get_string('slidecolorsdesc', 'theme_mcb');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
      // Slide Header colour setting.
    $name = 'theme_mcb/slideheadercolor';
    $title = get_string('slideheadercolor', 'theme_mcb');
    $description = get_string('slideheadercolordesc', 'theme_mcb');
    $default = '#30add1';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Slide Text colour setting.
    $name = 'theme_mcb/slidecolor';
    $title = get_string('slidecolor', 'theme_mcb');
    $description = get_string('slidecolordesc', 'theme_mcb');
    $default = '#888';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Slide Button colour setting.
    $name = 'theme_mcb/slidebuttoncolor';
    $title = get_string('slidebuttoncolor', 'theme_mcb');
    $description = get_string('slidebuttoncolordesc', 'theme_mcb');
    $default = '#30add1';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
        //This is the descriptor for the Slideshow
    $name = 'theme_mcb/footercolorinfo';
    $heading = get_string('footercolors', 'theme_mcb');
    $information = get_string('footercolorsdesc', 'theme_mcb');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Footer background colour setting.
    $name = 'theme_mcb/footercolor';
    $title = get_string('footercolor', 'theme_mcb');
    $description = get_string('footercolordesc', 'theme_mcb');
    $default = '#000000';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer text colour setting.
    $name = 'theme_mcb/footertextcolor';
    $title = get_string('footertextcolor', 'theme_mcb');
    $description = get_string('footertextcolordesc', 'theme_mcb');
    $default = '#DDDDDD';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer Block Heading colour setting.
    $name = 'theme_mcb/footerheadingcolor';
    $title = get_string('footerheadingcolor', 'theme_mcb');
    $description = get_string('footerheadingcolordesc', 'theme_mcb');
    $default = '#CCCCCC';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer Seperator colour setting.
    $name = 'theme_mcb/footersepcolor';
    $title = get_string('footersepcolor', 'theme_mcb');
    $description = get_string('footersepcolordesc', 'theme_mcb');
    $default = '#313131';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer URL colour setting.
    $name = 'theme_mcb/footerurlcolor';
    $title = get_string('footerurlcolor', 'theme_mcb');
    $description = get_string('footerurlcolordesc', 'theme_mcb');
    $default = '#BBBBBB';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer URL hover colour setting.
    $name = 'theme_mcb/footerhovercolor';
    $title = get_string('footerhovercolor', 'theme_mcb');
    $description = get_string('footerhovercolordesc', 'theme_mcb');
    $default = '#FFFFFF';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);



 	$ADMIN->add('theme_mcb', $temp);
 
 
    /* Slideshow Widget Settings */
    $temp = new admin_settingpage('theme_mcb_slideshow', get_string('slideshowheading', 'theme_mcb'));
    $temp->add(new admin_setting_heading('theme_mcb_slideshow', get_string('slideshowheadingsub', 'theme_mcb'),
            format_text(get_string('slideshowdesc' , 'theme_mcb'), FORMAT_MARKDOWN)));
    
    // Toggle Slideshow.
    $name = 'theme_mcb/toggleslideshow';
    $title = get_string('toggleslideshow' , 'theme_mcb');
    $description = get_string('toggleslideshowdesc', 'theme_mcb');
    $alwaysdisplay = get_string('alwaysdisplay', 'theme_mcb');
    $displaybeforelogin = get_string('displaybeforelogin', 'theme_mcb');
    $displayafterlogin = get_string('displayafterlogin', 'theme_mcb');
    $dontdisplay = get_string('dontdisplay', 'theme_mcb');
    $default = 'alwaysdisplay';
    $choices = array('1'=>$alwaysdisplay, '2'=>$displaybeforelogin, '3'=>$displayafterlogin, '0'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Hide slideshow on phones.
    $name = 'theme_mcb/hideonphone';
    $title = get_string('hideonphone' , 'theme_mcb');
    $description = get_string('hideonphonedesc', 'theme_mcb');
    $display = get_string('alwaysdisplay', 'theme_mcb');
    $dontdisplay = get_string('dontdisplay', 'theme_mcb');
    $default = 'display';
    $choices = array(''=>$display, 'hidden-phone'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Slideshow Design Picker.
    $name = 'theme_mcb/slideshowvariant';
    $title = get_string('slideshowvariant' , 'theme_mcb');
    $description = get_string('slideshowvariantdesc', 'theme_mcb');
    $slideshow1 = get_string('slideshow1', 'theme_mcb');
    $slideshow2 = get_string('slideshow2', 'theme_mcb');
    $default = 'slideshow1';
    $choices = array('1'=>$slideshow1, '2'=>$slideshow2);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 1
     */
     
    //This is the descriptor for Slide One
    $name = 'theme_mcb/slide1info';
    $heading = get_string('slide1', 'theme_mcb');
    $information = get_string('slideinfodesc', 'theme_mcb');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);

    // Title.
    $name = 'theme_mcb/slide1';
    $title = get_string('slidetitle', 'theme_mcb');
    $description = get_string('slidetitledesc', 'theme_mcb');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_mcb/slide1image';
    $title = get_string('slideimage', 'theme_mcb');
    $description = get_string('slideimagedesc', 'theme_mcb');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide1image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_mcb/slide1caption';
    $title = get_string('slidecaption', 'theme_mcb');
    $description = get_string('slidecaptiondesc', 'theme_mcb');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_mcb/slide1url';
    $title = get_string('slideurl', 'theme_mcb');
    $description = get_string('slideurldesc', 'theme_mcb');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 2
     */
     
    //This is the descriptor for Slide Two
    $name = 'theme_mcb/slide2info';
    $heading = get_string('slide2', 'theme_mcb');
    $information = get_string('slideinfodesc', 'theme_mcb');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);

    // Title.
    $name = 'theme_mcb/slide2';
    $title = get_string('slidetitle', 'theme_mcb');
    $description = get_string('slidetitledesc', 'theme_mcb');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_mcb/slide2image';
    $title = get_string('slideimage', 'theme_mcb');
    $description = get_string('slideimagedesc', 'theme_mcb');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide2image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_mcb/slide2caption';
    $title = get_string('slidecaption', 'theme_mcb');
    $description = get_string('slidecaptiondesc', 'theme_mcb');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_mcb/slide2url';
    $title = get_string('slideurl', 'theme_mcb');
    $description = get_string('slideurldesc', 'theme_mcb');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 3
     */

    //This is the descriptor for Slide Three
    $name = 'theme_mcb/slide3info';
    $heading = get_string('slide3', 'theme_mcb');
    $information = get_string('slideinfodesc', 'theme_mcb');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Title.
    $name = 'theme_mcb/slide3';
    $title = get_string('slidetitle', 'theme_mcb');
    $description = get_string('slidetitledesc', 'theme_mcb');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_mcb/slide3image';
    $title = get_string('slideimage', 'theme_mcb');
    $description = get_string('slideimagedesc', 'theme_mcb');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide3image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_mcb/slide3caption';
    $title = get_string('slidecaption', 'theme_mcb');
    $description = get_string('slidecaptiondesc', 'theme_mcb');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_mcb/slide3url';
    $title = get_string('slideurl', 'theme_mcb');
    $description = get_string('slideurldesc', 'theme_mcb');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 4
     */
     
    //This is the descriptor for Slide Four
    $name = 'theme_mcb/slide4info';
    $heading = get_string('slide4', 'theme_mcb');
    $information = get_string('slideinfodesc', 'theme_mcb');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);

    // Title.
    $name = 'theme_mcb/slide4';
    $title = get_string('slidetitle', 'theme_mcb');
    $description = get_string('slidetitledesc', 'theme_mcb');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_mcb/slide4image';
    $title = get_string('slideimage', 'theme_mcb');
    $description = get_string('slideimagedesc', 'theme_mcb');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide4image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_mcb/slide4caption';
    $title = get_string('slidecaption', 'theme_mcb');
    $description = get_string('slidecaptiondesc', 'theme_mcb');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_mcb/slide4url';
    $title = get_string('slideurl', 'theme_mcb');
    $description = get_string('slideurldesc', 'theme_mcb');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    
    $ADMIN->add('theme_mcb', $temp);
    
    $temp = new admin_settingpage('theme_mcb_frontcontent', get_string('frontcontentheading', 'theme_mcb'));
	$temp->add(new admin_setting_heading('theme_mcb_frontcontent', get_string('frontcontentheadingsub', 'theme_mcb'),
            format_text(get_string('frontcontentdesc' , 'theme_mcb'), FORMAT_MARKDOWN)));
    
    // Enable Frontpage Content
    $name = 'theme_mcb/usefrontcontent';
    $title = get_string('usefrontcontent', 'theme_mcb');
    $description = get_string('usefrontcontentdesc', 'theme_mcb');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Frontpage Content
    $name = 'theme_mcb/frontcontentarea';
    $title = get_string('frontcontentarea', 'theme_mcb');
    $description = get_string('frontcontentareadesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Frontpage Block alignment.
    $name = 'theme_mcb/frontpageblocks';
    $title = get_string('frontpageblocks' , 'theme_mcb');
    $description = get_string('frontpageblocksdesc', 'theme_mcb');
    $left = get_string('left', 'theme_mcb');
    $right = get_string('right', 'theme_mcb');
    $default = 'left';
    $choices = array('1'=>$left, '0'=>$right);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Toggle Frontpage Middle Blocks
    $name = 'theme_mcb/frontpagemiddleblocks';
    $title = get_string('frontpagemiddleblocks' , 'theme_mcb');
    $description = get_string('frontpagemiddleblocksdesc', 'theme_mcb');
    $alwaysdisplay = get_string('alwaysdisplay', 'theme_mcb');
    $displaybeforelogin = get_string('displaybeforelogin', 'theme_mcb');
    $displayafterlogin = get_string('displayafterlogin', 'theme_mcb');
    $dontdisplay = get_string('dontdisplay', 'theme_mcb');
    $default = 'display';
    $choices = array('1'=>$alwaysdisplay, '2'=>$displaybeforelogin, '3'=>$displayafterlogin, '0'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
        
    $ADMIN->add('theme_mcb', $temp);
    

	/* Marketing Spot Settings */
	$temp = new admin_settingpage('theme_mcb_marketing', get_string('marketingheading', 'theme_mcb'));
	$temp->add(new admin_setting_heading('theme_mcb_marketing', get_string('marketingheadingsub', 'theme_mcb'),
            format_text(get_string('marketingdesc' , 'theme_mcb'), FORMAT_MARKDOWN)));
	
	// Toggle Marketing Spots.
    $name = 'theme_mcb/togglemarketing';
    $title = get_string('togglemarketing' , 'theme_mcb');
    $description = get_string('togglemarketingdesc', 'theme_mcb');
    $alwaysdisplay = get_string('alwaysdisplay', 'theme_mcb');
    $displaybeforelogin = get_string('displaybeforelogin', 'theme_mcb');
    $displayafterlogin = get_string('displayafterlogin', 'theme_mcb');
    $dontdisplay = get_string('dontdisplay', 'theme_mcb');
    $default = 'display';
    $choices = array('1'=>$alwaysdisplay, '2'=>$displaybeforelogin, '3'=>$displayafterlogin, '0'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Marketing Spot Image Height
	$name = 'theme_mcb/marketingheight';
	$title = get_string('marketingheight','theme_mcb');
	$description = get_string('marketingheightdesc', 'theme_mcb');
	$default = 100;
	$choices = array(50, 100, 150, 200, 250, 300);
	$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
	$temp->add($setting);
	
	//This is the descriptor for Marketing Spot One
    $name = 'theme_mcb/marketing1info';
    $heading = get_string('marketing1', 'theme_mcb');
    $information = get_string('marketinginfodesc', 'theme_mcb');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
	
	//Marketing Spot One.
	$name = 'theme_mcb/marketing1';
    $title = get_string('marketingtitle', 'theme_mcb');
    $description = get_string('marketingtitledesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mcb/marketing1icon';
    $title = get_string('marketingicon', 'theme_mcb');
    $description = get_string('marketingicondesc', 'theme_mcb');
    $default = 'star';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mcb/marketing1image';
    $title = get_string('marketingimage', 'theme_mcb');
    $description = get_string('marketingimagedesc', 'theme_mcb');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing1image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mcb/marketing1content';
    $title = get_string('marketingcontent', 'theme_mcb');
    $description = get_string('marketingcontentdesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mcb/marketing1buttontext';
    $title = get_string('marketingbuttontext', 'theme_mcb');
    $description = get_string('marketingbuttontextdesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mcb/marketing1buttonurl';
    $title = get_string('marketingbuttonurl', 'theme_mcb');
    $description = get_string('marketingbuttonurldesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for Marketing Spot Two
    $name = 'theme_mcb/marketing2info';
    $heading = get_string('marketing2', 'theme_mcb');
    $information = get_string('marketinginfodesc', 'theme_mcb');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    //Marketing Spot Two.
	$name = 'theme_mcb/marketing2';
    $title = get_string('marketingtitle', 'theme_mcb');
    $description = get_string('marketingtitledesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mcb/marketing2icon';
    $title = get_string('marketingicon', 'theme_mcb');
    $description = get_string('marketingicondesc', 'theme_mcb');
    $default = 'star';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mcb/marketing2image';
    $title = get_string('marketingimage', 'theme_mcb');
    $description = get_string('marketingimagedesc', 'theme_mcb');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing2image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mcb/marketing2content';
    $title = get_string('marketingcontent', 'theme_mcb');
    $description = get_string('marketingcontentdesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mcb/marketing2buttontext';
    $title = get_string('marketingbuttontext', 'theme_mcb');
    $description = get_string('marketingbuttontextdesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mcb/marketing2buttonurl';
    $title = get_string('marketingbuttonurl', 'theme_mcb');
    $description = get_string('marketingbuttonurldesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for Marketing Spot Three
    $name = 'theme_mcb/marketing3info';
    $heading = get_string('marketing3', 'theme_mcb');
    $information = get_string('marketinginfodesc', 'theme_mcb');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    //Marketing Spot Three.
	$name = 'theme_mcb/marketing3';
    $title = get_string('marketingtitle', 'theme_mcb');
    $description = get_string('marketingtitledesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mcb/marketing3icon';
    $title = get_string('marketingicon', 'theme_mcb');
    $description = get_string('marketingicondesc', 'theme_mcb');
    $default = 'star';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mcb/marketing3image';
    $title = get_string('marketingimage', 'theme_mcb');
    $description = get_string('marketingimagedesc', 'theme_mcb');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing3image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mcb/marketing3content';
    $title = get_string('marketingcontent', 'theme_mcb');
    $description = get_string('marketingcontentdesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mcb/marketing3buttontext';
    $title = get_string('marketingbuttontext', 'theme_mcb');
    $description = get_string('marketingbuttontextdesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mcb/marketing3buttonurl';
    $title = get_string('marketingbuttonurl', 'theme_mcb');
    $description = get_string('marketingbuttonurldesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    
    $ADMIN->add('theme_mcb', $temp);

	
	/* Social Network Settings */
	$temp = new admin_settingpage('theme_mcb_social', get_string('socialheading', 'theme_mcb'));
	$temp->add(new admin_setting_heading('theme_mcb_social', get_string('socialheadingsub', 'theme_mcb'),
            format_text(get_string('socialdesc' , 'theme_mcb'), FORMAT_MARKDOWN)));
	
    // Website url setting.
    $name = 'theme_mcb/website';
    $title = get_string('website', 'theme_mcb');
    $description = get_string('websitedesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Facebook url setting.
    $name = 'theme_mcb/facebook';
    $title = get_string('facebook', 'theme_mcb');
    $description = get_string('facebookdesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Flickr url setting.
    $name = 'theme_mcb/flickr';
    $title = get_string('flickr', 'theme_mcb');
    $description = get_string('flickrdesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Twitter url setting.
    $name = 'theme_mcb/twitter';
    $title = get_string('twitter', 'theme_mcb');
    $description = get_string('twitterdesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Google+ url setting.
    $name = 'theme_mcb/googleplus';
    $title = get_string('googleplus', 'theme_mcb');
    $description = get_string('googleplusdesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // LinkedIn url setting.
    $name = 'theme_mcb/linkedin';
    $title = get_string('linkedin', 'theme_mcb');
    $description = get_string('linkedindesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Pinterest url setting.
    $name = 'theme_mcb/pinterest';
    $title = get_string('pinterest', 'theme_mcb');
    $description = get_string('pinterestdesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Instagram url setting.
    $name = 'theme_mcb/instagram';
    $title = get_string('instagram', 'theme_mcb');
    $description = get_string('instagramdesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // YouTube url setting.
    $name = 'theme_mcb/youtube';
    $title = get_string('youtube', 'theme_mcb');
    $description = get_string('youtubedesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Skype url setting.
    $name = 'theme_mcb/skype';
    $title = get_string('skype', 'theme_mcb');
    $description = get_string('skypedesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
 
    // VKontakte url setting.
    $name = 'theme_mcb/vk';
    $title = get_string('vk', 'theme_mcb');
    $description = get_string('vkdesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting); 
    
    $ADMIN->add('theme_mcb', $temp);
    
    $temp = new admin_settingpage('theme_mcb_mobileapps', get_string('mobileappsheading', 'theme_mcb'));
	$temp->add(new admin_setting_heading('theme_mcb_mobileapps', get_string('mobileappsheadingsub', 'theme_mcb'),
            format_text(get_string('mobileappsdesc' , 'theme_mcb'), FORMAT_MARKDOWN)));
    // Android App url setting.
    $name = 'theme_mcb/android';
    $title = get_string('android', 'theme_mcb');
    $description = get_string('androiddesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iOS App url setting.
    $name = 'theme_mcb/ios';
    $title = get_string('ios', 'theme_mcb');
    $description = get_string('iosdesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for iOS Icons
    $name = 'theme_mcb/iosiconinfo';
    $heading = get_string('iosicon', 'theme_mcb');
    $information = get_string('iosicondesc', 'theme_mcb');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // iPhone Icon.
    $name = 'theme_mcb/iphoneicon';
    $title = get_string('iphoneicon', 'theme_mcb');
    $description = get_string('iphoneicondesc', 'theme_mcb');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'iphoneicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iPhone Retina Icon.
    $name = 'theme_mcb/iphoneretinaicon';
    $title = get_string('iphoneretinaicon', 'theme_mcb');
    $description = get_string('iphoneretinaicondesc', 'theme_mcb');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'iphoneretinaicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iPad Icon.
    $name = 'theme_mcb/ipadicon';
    $title = get_string('ipadicon', 'theme_mcb');
    $description = get_string('ipadicondesc', 'theme_mcb');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'ipadicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iPad Retina Icon.
    $name = 'theme_mcb/ipadretinaicon';
    $title = get_string('ipadretinaicon', 'theme_mcb');
    $description = get_string('ipadretinaicondesc', 'theme_mcb');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'ipadretinaicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $ADMIN->add('theme_mcb', $temp);
    
    /* User Alerts */
    $temp = new admin_settingpage('theme_mcb_alerts', get_string('alertsheading', 'theme_mcb'));
	$temp->add(new admin_setting_heading('theme_mcb_alerts', get_string('alertsheadingsub', 'theme_mcb'),
            format_text(get_string('alertsdesc' , 'theme_mcb'), FORMAT_MARKDOWN)));
    
    //This is the descriptor for Alert One
    $name = 'theme_mcb/alert1info';
    $heading = get_string('alert1', 'theme_mcb');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Enable Alert
    $name = 'theme_mcb/enable1alert';
    $title = get_string('enablealert', 'theme_mcb');
    $description = get_string('enablealertdesc', 'theme_mcb');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Type.
    $name = 'theme_mcb/alert1type';
    $title = get_string('alerttype' , 'theme_mcb');
    $description = get_string('alerttypedesc', 'theme_mcb');
    $alert_info = get_string('alert_info', 'theme_mcb');
    $alert_warning = get_string('alert_warning', 'theme_mcb');
    $alert_general = get_string('alert_general', 'theme_mcb');
    $default = 'info';
    $choices = array('info'=>$alert_info, 'error'=>$alert_warning, 'success'=>$alert_general);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Title.
    $name = 'theme_mcb/alert1title';
    $title = get_string('alerttitle', 'theme_mcb');
    $description = get_string('alerttitledesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Text.
    $name = 'theme_mcb/alert1text';
    $title = get_string('alerttext', 'theme_mcb');
    $description = get_string('alerttextdesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for Alert Two
    $name = 'theme_mcb/alert2info';
    $heading = get_string('alert2', 'theme_mcb');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Enable Alert
    $name = 'theme_mcb/enable2alert';
    $title = get_string('enablealert', 'theme_mcb');
    $description = get_string('enablealertdesc', 'theme_mcb');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Type.
    $name = 'theme_mcb/alert2type';
    $title = get_string('alerttype' , 'theme_mcb');
    $description = get_string('alerttypedesc', 'theme_mcb');
    $alert_info = get_string('alert_info', 'theme_mcb');
    $alert_warning = get_string('alert_warning', 'theme_mcb');
    $alert_general = get_string('alert_general', 'theme_mcb');
    $default = 'info';
    $choices = array('info'=>$alert_info, 'error'=>$alert_warning, 'success'=>$alert_general);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Title.
    $name = 'theme_mcb/alert2title';
    $title = get_string('alerttitle', 'theme_mcb');
    $description = get_string('alerttitledesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Text.
    $name = 'theme_mcb/alert2text';
    $title = get_string('alerttext', 'theme_mcb');
    $description = get_string('alerttextdesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for Alert Three
    $name = 'theme_mcb/alert3info';
    $heading = get_string('alert3', 'theme_mcb');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Enable Alert
    $name = 'theme_mcb/enable3alert';
    $title = get_string('enablealert', 'theme_mcb');
    $description = get_string('enablealertdesc', 'theme_mcb');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Type.
    $name = 'theme_mcb/alert3type';
    $title = get_string('alerttype' , 'theme_mcb');
    $description = get_string('alerttypedesc', 'theme_mcb');
    $alert_info = get_string('alert_info', 'theme_mcb');
    $alert_warning = get_string('alert_warning', 'theme_mcb');
    $alert_general = get_string('alert_general', 'theme_mcb');
    $default = 'info';
    $choices = array('info'=>$alert_info, 'error'=>$alert_warning, 'success'=>$alert_general);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Title.
    $name = 'theme_mcb/alert3title';
    $title = get_string('alerttitle', 'theme_mcb');
    $description = get_string('alerttitledesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Text.
    $name = 'theme_mcb/alert3text';
    $title = get_string('alerttext', 'theme_mcb');
    $description = get_string('alerttextdesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
            
    
    $ADMIN->add('theme_mcb', $temp);
    
    /* Analytics Settings */
    $temp = new admin_settingpage('theme_mcb_analytics', get_string('analyticsheading', 'theme_mcb'));
	$temp->add(new admin_setting_heading('theme_mcb_analytics', get_string('analyticsheadingsub', 'theme_mcb'),
            format_text(get_string('analyticsdesc' , 'theme_mcb'), FORMAT_MARKDOWN)));
    
    // Enable Analytics
    $name = 'theme_mcb/useanalytics';
    $title = get_string('useanalytics', 'theme_mcb');
    $description = get_string('useanalyticsdesc', 'theme_mcb');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Google Analytics ID
    $name = 'theme_mcb/analyticsid';
    $title = get_string('analyticsid', 'theme_mcb');
    $description = get_string('analyticsiddesc', 'theme_mcb');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Clean Analytics URL
    $name = 'theme_mcb/analyticsclean';
    $title = get_string('analyticsclean', 'theme_mcb');
    $description = get_string('analyticscleandesc', 'theme_mcb');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
        
    $ADMIN->add('theme_mcb', $temp);

