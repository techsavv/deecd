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
 * @package   theme_safeminds
 * @copyright 2013 Julian Ridden
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$settings = null;

defined('MOODLE_INTERNAL') || die;


	$ADMIN->add('themes', new admin_category('theme_safeminds', 'safeminds'));

	// "geneicsettings" settingpage
	$temp = new admin_settingpage('theme_safeminds_generic',  get_string('geneicsettings', 'theme_safeminds'));
	
	// Default Site icon setting.
    $name = 'theme_safeminds/siteicon';
    $title = get_string('siteicon', 'theme_safeminds');
    $description = get_string('siteicondesc', 'theme_safeminds');
    $default = 'laptop';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $temp->add($setting);
    
    // Include Awesome Font from Bootstrapcdn
    $name = 'theme_safeminds/bootstrapcdn';
    $title = get_string('bootstrapcdn', 'theme_safeminds');
    $description = get_string('bootstrapcdndesc', 'theme_safeminds');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
	
    // Logo file setting.
    $name = 'theme_safeminds/logo';
    $title = get_string('logo', 'theme_safeminds');
    $description = get_string('logodesc', 'theme_safeminds');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'logo');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Font Selector.
    $name = 'theme_safeminds/fontselect';
    $title = get_string('fontselect' , 'theme_safeminds');
    $description = get_string('fontselectdesc', 'theme_safeminds');
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
    $name = 'theme_safeminds/headerprofilepic';
    $title = get_string('headerprofilepic', 'theme_safeminds');
    $description = get_string('headerprofilepicdesc', 'theme_safeminds');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Fixed or Variable Width.
    $name = 'theme_safeminds/pagewidth';
    $title = get_string('pagewidth', 'theme_safeminds');
    $description = get_string('pagewidthdesc', 'theme_safeminds');
    $default = 1200;
    $choices = array(1900=>get_string('fixedwidthwide','theme_safeminds'), 1200=>get_string('fixedwidthnarrow','theme_safeminds'), 100=>get_string('variablewidth','theme_safeminds'));
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Custom or standard layout.
    $name = 'theme_safeminds/layout';
    $title = get_string('layout', 'theme_safeminds');
    $description = get_string('layoutdesc', 'theme_safeminds');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //Include the Editicons css rules
    $name = 'theme_safeminds/editicons';
    $title = get_string('editicons', 'theme_safeminds');
    $description = get_string('editiconsdesc', 'theme_safeminds');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $temp->add($setting);
    
    // Performance Information Display.
    $name = 'theme_safeminds/perfinfo';
    $title = get_string('perfinfo' , 'theme_safeminds');
    $description = get_string('perfinfodesc', 'theme_safeminds');
    $perf_max = get_string('perf_max', 'theme_safeminds');
    $perf_min = get_string('perf_min', 'theme_safeminds');
    $default = 'min';
    $choices = array('min'=>$perf_min, 'max'=>$perf_max);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Navbar Seperator.
    $name = 'theme_safeminds/navbarsep';
    $title = get_string('navbarsep' , 'theme_safeminds');
    $description = get_string('navbarsepdesc', 'theme_safeminds');
    $nav_thinbracket = get_string('nav_thinbracket', 'theme_safeminds');
    $nav_doublebracket = get_string('nav_doublebracket', 'theme_safeminds');
    $nav_thickbracket = get_string('nav_thickbracket', 'theme_safeminds');
    $nav_slash = get_string('nav_slash', 'theme_safeminds');
    $nav_pipe = get_string('nav_pipe', 'theme_safeminds');
    $dontdisplay = get_string('dontdisplay', 'theme_safeminds');
    $default = '/';
    $choices = array('/'=>$nav_slash, '\f105'=>$nav_thinbracket, '\f101'=>$nav_doublebracket, '\f054'=>$nav_thickbracket, '|'=>$nav_pipe);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Copyright setting.
    $name = 'theme_safeminds/copyright';
    $title = get_string('copyright', 'theme_safeminds');
    $description = get_string('copyrightdesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $temp->add($setting);
    
    // Footnote setting.
    $name = 'theme_safeminds/footnote';
    $title = get_string('footnote', 'theme_safeminds');
    $description = get_string('footnotedesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Custom CSS file.
    $name = 'theme_safeminds/customcss';
    $title = get_string('customcss', 'theme_safeminds');
    $description = get_string('customcssdesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $ADMIN->add('theme_safeminds', $temp);
    
    /* Custom Menu Settings */
    $temp = new admin_settingpage('theme_safeminds_custommenu', get_string('custommenuheading', 'theme_safeminds'));
	            
    //This is the descriptor for the following Moodle color settings
    $name = 'theme_safeminds/mydashboardinfo';
    $heading = get_string('mydashboardinfo', 'theme_safeminds');
    $information = get_string('mydashboardinfodesc', 'theme_safeminds');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Toggle dashboard display in custommenu.
    $name = 'theme_safeminds/displaymydashboard';
    $title = get_string('displaymydashboard', 'theme_safeminds');
    $description = get_string('displaymydashboarddesc', 'theme_safeminds');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for the following Moodle color settings
    $name = 'theme_safeminds/mycoursesinfo';
    $heading = get_string('mycoursesinfo', 'theme_safeminds');
    $information = get_string('mycoursesinfodesc', 'theme_safeminds');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Toggle courses display in custommenu.
    $name = 'theme_safeminds/displaymycourses';
    $title = get_string('displaymycourses', 'theme_safeminds');
    $description = get_string('displaymycoursesdesc', 'theme_safeminds');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Set terminology for dropdown course list
	$name = 'theme_safeminds/mycoursetitle';
	$title = get_string('mycoursetitle','theme_safeminds');
	$description = get_string('mycoursetitledesc', 'theme_safeminds');
	$default = 'course';
	$choices = array(
		'course' => get_string('mycourses', 'theme_safeminds'),
		'unit' => get_string('myunits', 'theme_safeminds'),
		'class' => get_string('myclasses', 'theme_safeminds'),
		'module' => get_string('mymodules', 'theme_safeminds')
	);
	$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
	$setting->set_updatedcallback('theme_reset_all_caches');
	$temp->add($setting);
    
    $ADMIN->add('theme_safeminds', $temp);
    
	/* Color Settings */
    $temp = new admin_settingpage('theme_safeminds_color', get_string('colorheading', 'theme_safeminds'));
    $temp->add(new admin_setting_heading('theme_safeminds_color', get_string('colorheadingsub', 'theme_safeminds'),
            format_text(get_string('colordesc' , 'theme_safeminds'), FORMAT_MARKDOWN)));

    // Background Image.
    $name = 'theme_safeminds/pagebackground';
    $title = get_string('pagebackground', 'theme_safeminds');
    $description = get_string('pagebackgrounddesc', 'theme_safeminds');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'pagebackground');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Main theme colour setting.
    $name = 'theme_safeminds/themecolor';
    $title = get_string('themecolor', 'theme_safeminds');
    $description = get_string('themecolordesc', 'theme_safeminds');
    $default = '#30add1';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Main theme Hover colour setting.
    $name = 'theme_safeminds/themehovercolor';
    $title = get_string('themehovercolor', 'theme_safeminds');
    $description = get_string('themehovercolordesc', 'theme_safeminds');
    $default = '#29a1c4';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for the Slideshow
    $name = 'theme_safeminds/slidecolorinfo';
    $heading = get_string('slidecolors', 'theme_safeminds');
    $information = get_string('slidecolorsdesc', 'theme_safeminds');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
      // Slide Header colour setting.
    $name = 'theme_safeminds/slideheadercolor';
    $title = get_string('slideheadercolor', 'theme_safeminds');
    $description = get_string('slideheadercolordesc', 'theme_safeminds');
    $default = '#30add1';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Slide Text colour setting.
    $name = 'theme_safeminds/slidecolor';
    $title = get_string('slidecolor', 'theme_safeminds');
    $description = get_string('slidecolordesc', 'theme_safeminds');
    $default = '#888';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Slide Button colour setting.
    $name = 'theme_safeminds/slidebuttoncolor';
    $title = get_string('slidebuttoncolor', 'theme_safeminds');
    $description = get_string('slidebuttoncolordesc', 'theme_safeminds');
    $default = '#30add1';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
        //This is the descriptor for the Slideshow
    $name = 'theme_safeminds/footercolorinfo';
    $heading = get_string('footercolors', 'theme_safeminds');
    $information = get_string('footercolorsdesc', 'theme_safeminds');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Footer background colour setting.
    $name = 'theme_safeminds/footercolor';
    $title = get_string('footercolor', 'theme_safeminds');
    $description = get_string('footercolordesc', 'theme_safeminds');
    $default = '#000000';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer text colour setting.
    $name = 'theme_safeminds/footertextcolor';
    $title = get_string('footertextcolor', 'theme_safeminds');
    $description = get_string('footertextcolordesc', 'theme_safeminds');
    $default = '#DDDDDD';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer Block Heading colour setting.
    $name = 'theme_safeminds/footerheadingcolor';
    $title = get_string('footerheadingcolor', 'theme_safeminds');
    $description = get_string('footerheadingcolordesc', 'theme_safeminds');
    $default = '#CCCCCC';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer Seperator colour setting.
    $name = 'theme_safeminds/footersepcolor';
    $title = get_string('footersepcolor', 'theme_safeminds');
    $description = get_string('footersepcolordesc', 'theme_safeminds');
    $default = '#313131';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer URL colour setting.
    $name = 'theme_safeminds/footerurlcolor';
    $title = get_string('footerurlcolor', 'theme_safeminds');
    $description = get_string('footerurlcolordesc', 'theme_safeminds');
    $default = '#BBBBBB';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer URL hover colour setting.
    $name = 'theme_safeminds/footerhovercolor';
    $title = get_string('footerhovercolor', 'theme_safeminds');
    $description = get_string('footerhovercolordesc', 'theme_safeminds');
    $default = '#FFFFFF';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);



 	$ADMIN->add('theme_safeminds', $temp);
 
 
    /* Slideshow Widget Settings */
    $temp = new admin_settingpage('theme_safeminds_slideshow', get_string('slideshowheading', 'theme_safeminds'));
    $temp->add(new admin_setting_heading('theme_safeminds_slideshow', get_string('slideshowheadingsub', 'theme_safeminds'),
            format_text(get_string('slideshowdesc' , 'theme_safeminds'), FORMAT_MARKDOWN)));
    
    // Toggle Slideshow.
    $name = 'theme_safeminds/toggleslideshow';
    $title = get_string('toggleslideshow' , 'theme_safeminds');
    $description = get_string('toggleslideshowdesc', 'theme_safeminds');
    $alwaysdisplay = get_string('alwaysdisplay', 'theme_safeminds');
    $displaybeforelogin = get_string('displaybeforelogin', 'theme_safeminds');
    $displayafterlogin = get_string('displayafterlogin', 'theme_safeminds');
    $dontdisplay = get_string('dontdisplay', 'theme_safeminds');
    $default = 'alwaysdisplay';
    $choices = array('1'=>$alwaysdisplay, '2'=>$displaybeforelogin, '3'=>$displayafterlogin, '0'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Hide slideshow on phones.
    $name = 'theme_safeminds/hideonphone';
    $title = get_string('hideonphone' , 'theme_safeminds');
    $description = get_string('hideonphonedesc', 'theme_safeminds');
    $display = get_string('alwaysdisplay', 'theme_safeminds');
    $dontdisplay = get_string('dontdisplay', 'theme_safeminds');
    $default = 'display';
    $choices = array(''=>$display, 'hidden-phone'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Slideshow Design Picker.
    $name = 'theme_safeminds/slideshowvariant';
    $title = get_string('slideshowvariant' , 'theme_safeminds');
    $description = get_string('slideshowvariantdesc', 'theme_safeminds');
    $slideshow1 = get_string('slideshow1', 'theme_safeminds');
    $slideshow2 = get_string('slideshow2', 'theme_safeminds');
    $default = 'slideshow1';
    $choices = array('1'=>$slideshow1, '2'=>$slideshow2);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 1
     */
     
    //This is the descriptor for Slide One
    $name = 'theme_safeminds/slide1info';
    $heading = get_string('slide1', 'theme_safeminds');
    $information = get_string('slideinfodesc', 'theme_safeminds');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);

    // Title.
    $name = 'theme_safeminds/slide1';
    $title = get_string('slidetitle', 'theme_safeminds');
    $description = get_string('slidetitledesc', 'theme_safeminds');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_safeminds/slide1image';
    $title = get_string('slideimage', 'theme_safeminds');
    $description = get_string('slideimagedesc', 'theme_safeminds');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide1image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_safeminds/slide1caption';
    $title = get_string('slidecaption', 'theme_safeminds');
    $description = get_string('slidecaptiondesc', 'theme_safeminds');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_safeminds/slide1url';
    $title = get_string('slideurl', 'theme_safeminds');
    $description = get_string('slideurldesc', 'theme_safeminds');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 2
     */
     
    //This is the descriptor for Slide Two
    $name = 'theme_safeminds/slide2info';
    $heading = get_string('slide2', 'theme_safeminds');
    $information = get_string('slideinfodesc', 'theme_safeminds');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);

    // Title.
    $name = 'theme_safeminds/slide2';
    $title = get_string('slidetitle', 'theme_safeminds');
    $description = get_string('slidetitledesc', 'theme_safeminds');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_safeminds/slide2image';
    $title = get_string('slideimage', 'theme_safeminds');
    $description = get_string('slideimagedesc', 'theme_safeminds');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide2image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_safeminds/slide2caption';
    $title = get_string('slidecaption', 'theme_safeminds');
    $description = get_string('slidecaptiondesc', 'theme_safeminds');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_safeminds/slide2url';
    $title = get_string('slideurl', 'theme_safeminds');
    $description = get_string('slideurldesc', 'theme_safeminds');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 3
     */

    //This is the descriptor for Slide Three
    $name = 'theme_safeminds/slide3info';
    $heading = get_string('slide3', 'theme_safeminds');
    $information = get_string('slideinfodesc', 'theme_safeminds');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Title.
    $name = 'theme_safeminds/slide3';
    $title = get_string('slidetitle', 'theme_safeminds');
    $description = get_string('slidetitledesc', 'theme_safeminds');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_safeminds/slide3image';
    $title = get_string('slideimage', 'theme_safeminds');
    $description = get_string('slideimagedesc', 'theme_safeminds');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide3image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_safeminds/slide3caption';
    $title = get_string('slidecaption', 'theme_safeminds');
    $description = get_string('slidecaptiondesc', 'theme_safeminds');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_safeminds/slide3url';
    $title = get_string('slideurl', 'theme_safeminds');
    $description = get_string('slideurldesc', 'theme_safeminds');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 4
     */
     
    //This is the descriptor for Slide Four
    $name = 'theme_safeminds/slide4info';
    $heading = get_string('slide4', 'theme_safeminds');
    $information = get_string('slideinfodesc', 'theme_safeminds');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);

    // Title.
    $name = 'theme_safeminds/slide4';
    $title = get_string('slidetitle', 'theme_safeminds');
    $description = get_string('slidetitledesc', 'theme_safeminds');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_safeminds/slide4image';
    $title = get_string('slideimage', 'theme_safeminds');
    $description = get_string('slideimagedesc', 'theme_safeminds');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide4image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_safeminds/slide4caption';
    $title = get_string('slidecaption', 'theme_safeminds');
    $description = get_string('slidecaptiondesc', 'theme_safeminds');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_safeminds/slide4url';
    $title = get_string('slideurl', 'theme_safeminds');
    $description = get_string('slideurldesc', 'theme_safeminds');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    
    $ADMIN->add('theme_safeminds', $temp);
    
    $temp = new admin_settingpage('theme_safeminds_frontcontent', get_string('frontcontentheading', 'theme_safeminds'));
	$temp->add(new admin_setting_heading('theme_safeminds_frontcontent', get_string('frontcontentheadingsub', 'theme_safeminds'),
            format_text(get_string('frontcontentdesc' , 'theme_safeminds'), FORMAT_MARKDOWN)));
    
    // Enable Frontpage Content
    $name = 'theme_safeminds/usefrontcontent';
    $title = get_string('usefrontcontent', 'theme_safeminds');
    $description = get_string('usefrontcontentdesc', 'theme_safeminds');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Frontpage Content
    $name = 'theme_safeminds/frontcontentarea';
    $title = get_string('frontcontentarea', 'theme_safeminds');
    $description = get_string('frontcontentareadesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Frontpage Block alignment.
    $name = 'theme_safeminds/frontpageblocks';
    $title = get_string('frontpageblocks' , 'theme_safeminds');
    $description = get_string('frontpageblocksdesc', 'theme_safeminds');
    $left = get_string('left', 'theme_safeminds');
    $right = get_string('right', 'theme_safeminds');
    $default = 'left';
    $choices = array('1'=>$left, '0'=>$right);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Toggle Frontpage Middle Blocks
    $name = 'theme_safeminds/frontpagemiddleblocks';
    $title = get_string('frontpagemiddleblocks' , 'theme_safeminds');
    $description = get_string('frontpagemiddleblocksdesc', 'theme_safeminds');
    $alwaysdisplay = get_string('alwaysdisplay', 'theme_safeminds');
    $displaybeforelogin = get_string('displaybeforelogin', 'theme_safeminds');
    $displayafterlogin = get_string('displayafterlogin', 'theme_safeminds');
    $dontdisplay = get_string('dontdisplay', 'theme_safeminds');
    $default = 'display';
    $choices = array('1'=>$alwaysdisplay, '2'=>$displaybeforelogin, '3'=>$displayafterlogin, '0'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
        
    $ADMIN->add('theme_safeminds', $temp);
    

	/* Marketing Spot Settings */
	$temp = new admin_settingpage('theme_safeminds_marketing', get_string('marketingheading', 'theme_safeminds'));
	$temp->add(new admin_setting_heading('theme_safeminds_marketing', get_string('marketingheadingsub', 'theme_safeminds'),
            format_text(get_string('marketingdesc' , 'theme_safeminds'), FORMAT_MARKDOWN)));
	
	// Toggle Marketing Spots.
    $name = 'theme_safeminds/togglemarketing';
    $title = get_string('togglemarketing' , 'theme_safeminds');
    $description = get_string('togglemarketingdesc', 'theme_safeminds');
    $alwaysdisplay = get_string('alwaysdisplay', 'theme_safeminds');
    $displaybeforelogin = get_string('displaybeforelogin', 'theme_safeminds');
    $displayafterlogin = get_string('displayafterlogin', 'theme_safeminds');
    $dontdisplay = get_string('dontdisplay', 'theme_safeminds');
    $default = 'display';
    $choices = array('1'=>$alwaysdisplay, '2'=>$displaybeforelogin, '3'=>$displayafterlogin, '0'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Marketing Spot Image Height
	$name = 'theme_safeminds/marketingheight';
	$title = get_string('marketingheight','theme_safeminds');
	$description = get_string('marketingheightdesc', 'theme_safeminds');
	$default = 100;
	$choices = array(50, 100, 150, 200, 250, 300);
	$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
	$temp->add($setting);
	
	//This is the descriptor for Marketing Spot One
    $name = 'theme_safeminds/marketing1info';
    $heading = get_string('marketing1', 'theme_safeminds');
    $information = get_string('marketinginfodesc', 'theme_safeminds');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
	
	//Marketing Spot One.
	$name = 'theme_safeminds/marketing1';
    $title = get_string('marketingtitle', 'theme_safeminds');
    $description = get_string('marketingtitledesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_safeminds/marketing1icon';
    $title = get_string('marketingicon', 'theme_safeminds');
    $description = get_string('marketingicondesc', 'theme_safeminds');
    $default = 'star';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_safeminds/marketing1image';
    $title = get_string('marketingimage', 'theme_safeminds');
    $description = get_string('marketingimagedesc', 'theme_safeminds');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing1image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_safeminds/marketing1content';
    $title = get_string('marketingcontent', 'theme_safeminds');
    $description = get_string('marketingcontentdesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_safeminds/marketing1buttontext';
    $title = get_string('marketingbuttontext', 'theme_safeminds');
    $description = get_string('marketingbuttontextdesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_safeminds/marketing1buttonurl';
    $title = get_string('marketingbuttonurl', 'theme_safeminds');
    $description = get_string('marketingbuttonurldesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for Marketing Spot Two
    $name = 'theme_safeminds/marketing2info';
    $heading = get_string('marketing2', 'theme_safeminds');
    $information = get_string('marketinginfodesc', 'theme_safeminds');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    //Marketing Spot Two.
	$name = 'theme_safeminds/marketing2';
    $title = get_string('marketingtitle', 'theme_safeminds');
    $description = get_string('marketingtitledesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_safeminds/marketing2icon';
    $title = get_string('marketingicon', 'theme_safeminds');
    $description = get_string('marketingicondesc', 'theme_safeminds');
    $default = 'star';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_safeminds/marketing2image';
    $title = get_string('marketingimage', 'theme_safeminds');
    $description = get_string('marketingimagedesc', 'theme_safeminds');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing2image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_safeminds/marketing2content';
    $title = get_string('marketingcontent', 'theme_safeminds');
    $description = get_string('marketingcontentdesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_safeminds/marketing2buttontext';
    $title = get_string('marketingbuttontext', 'theme_safeminds');
    $description = get_string('marketingbuttontextdesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_safeminds/marketing2buttonurl';
    $title = get_string('marketingbuttonurl', 'theme_safeminds');
    $description = get_string('marketingbuttonurldesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for Marketing Spot Three
    $name = 'theme_safeminds/marketing3info';
    $heading = get_string('marketing3', 'theme_safeminds');
    $information = get_string('marketinginfodesc', 'theme_safeminds');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    //Marketing Spot Three.
	$name = 'theme_safeminds/marketing3';
    $title = get_string('marketingtitle', 'theme_safeminds');
    $description = get_string('marketingtitledesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_safeminds/marketing3icon';
    $title = get_string('marketingicon', 'theme_safeminds');
    $description = get_string('marketingicondesc', 'theme_safeminds');
    $default = 'star';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_safeminds/marketing3image';
    $title = get_string('marketingimage', 'theme_safeminds');
    $description = get_string('marketingimagedesc', 'theme_safeminds');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing3image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_safeminds/marketing3content';
    $title = get_string('marketingcontent', 'theme_safeminds');
    $description = get_string('marketingcontentdesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_safeminds/marketing3buttontext';
    $title = get_string('marketingbuttontext', 'theme_safeminds');
    $description = get_string('marketingbuttontextdesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_safeminds/marketing3buttonurl';
    $title = get_string('marketingbuttonurl', 'theme_safeminds');
    $description = get_string('marketingbuttonurldesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    
    $ADMIN->add('theme_safeminds', $temp);

	
	/* Social Network Settings */
	$temp = new admin_settingpage('theme_safeminds_social', get_string('socialheading', 'theme_safeminds'));
	$temp->add(new admin_setting_heading('theme_safeminds_social', get_string('socialheadingsub', 'theme_safeminds'),
            format_text(get_string('socialdesc' , 'theme_safeminds'), FORMAT_MARKDOWN)));
	
    // Website url setting.
    $name = 'theme_safeminds/website';
    $title = get_string('website', 'theme_safeminds');
    $description = get_string('websitedesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Facebook url setting.
    $name = 'theme_safeminds/facebook';
    $title = get_string('facebook', 'theme_safeminds');
    $description = get_string('facebookdesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Flickr url setting.
    $name = 'theme_safeminds/flickr';
    $title = get_string('flickr', 'theme_safeminds');
    $description = get_string('flickrdesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Twitter url setting.
    $name = 'theme_safeminds/twitter';
    $title = get_string('twitter', 'theme_safeminds');
    $description = get_string('twitterdesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Google+ url setting.
    $name = 'theme_safeminds/googleplus';
    $title = get_string('googleplus', 'theme_safeminds');
    $description = get_string('googleplusdesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // LinkedIn url setting.
    $name = 'theme_safeminds/linkedin';
    $title = get_string('linkedin', 'theme_safeminds');
    $description = get_string('linkedindesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Pinterest url setting.
    $name = 'theme_safeminds/pinterest';
    $title = get_string('pinterest', 'theme_safeminds');
    $description = get_string('pinterestdesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Instagram url setting.
    $name = 'theme_safeminds/instagram';
    $title = get_string('instagram', 'theme_safeminds');
    $description = get_string('instagramdesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // YouTube url setting.
    $name = 'theme_safeminds/youtube';
    $title = get_string('youtube', 'theme_safeminds');
    $description = get_string('youtubedesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Skype url setting.
    $name = 'theme_safeminds/skype';
    $title = get_string('skype', 'theme_safeminds');
    $description = get_string('skypedesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
 
    // VKontakte url setting.
    $name = 'theme_safeminds/vk';
    $title = get_string('vk', 'theme_safeminds');
    $description = get_string('vkdesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting); 
    
    $ADMIN->add('theme_safeminds', $temp);
    
    $temp = new admin_settingpage('theme_safeminds_mobileapps', get_string('mobileappsheading', 'theme_safeminds'));
	$temp->add(new admin_setting_heading('theme_safeminds_mobileapps', get_string('mobileappsheadingsub', 'theme_safeminds'),
            format_text(get_string('mobileappsdesc' , 'theme_safeminds'), FORMAT_MARKDOWN)));
    // Android App url setting.
    $name = 'theme_safeminds/android';
    $title = get_string('android', 'theme_safeminds');
    $description = get_string('androiddesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iOS App url setting.
    $name = 'theme_safeminds/ios';
    $title = get_string('ios', 'theme_safeminds');
    $description = get_string('iosdesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for iOS Icons
    $name = 'theme_safeminds/iosiconinfo';
    $heading = get_string('iosicon', 'theme_safeminds');
    $information = get_string('iosicondesc', 'theme_safeminds');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // iPhone Icon.
    $name = 'theme_safeminds/iphoneicon';
    $title = get_string('iphoneicon', 'theme_safeminds');
    $description = get_string('iphoneicondesc', 'theme_safeminds');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'iphoneicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iPhone Retina Icon.
    $name = 'theme_safeminds/iphoneretinaicon';
    $title = get_string('iphoneretinaicon', 'theme_safeminds');
    $description = get_string('iphoneretinaicondesc', 'theme_safeminds');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'iphoneretinaicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iPad Icon.
    $name = 'theme_safeminds/ipadicon';
    $title = get_string('ipadicon', 'theme_safeminds');
    $description = get_string('ipadicondesc', 'theme_safeminds');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'ipadicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iPad Retina Icon.
    $name = 'theme_safeminds/ipadretinaicon';
    $title = get_string('ipadretinaicon', 'theme_safeminds');
    $description = get_string('ipadretinaicondesc', 'theme_safeminds');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'ipadretinaicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $ADMIN->add('theme_safeminds', $temp);
    
    /* User Alerts */
    $temp = new admin_settingpage('theme_safeminds_alerts', get_string('alertsheading', 'theme_safeminds'));
	$temp->add(new admin_setting_heading('theme_safeminds_alerts', get_string('alertsheadingsub', 'theme_safeminds'),
            format_text(get_string('alertsdesc' , 'theme_safeminds'), FORMAT_MARKDOWN)));
    
    //This is the descriptor for Alert One
    $name = 'theme_safeminds/alert1info';
    $heading = get_string('alert1', 'theme_safeminds');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Enable Alert
    $name = 'theme_safeminds/enable1alert';
    $title = get_string('enablealert', 'theme_safeminds');
    $description = get_string('enablealertdesc', 'theme_safeminds');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Type.
    $name = 'theme_safeminds/alert1type';
    $title = get_string('alerttype' , 'theme_safeminds');
    $description = get_string('alerttypedesc', 'theme_safeminds');
    $alert_info = get_string('alert_info', 'theme_safeminds');
    $alert_warning = get_string('alert_warning', 'theme_safeminds');
    $alert_general = get_string('alert_general', 'theme_safeminds');
    $default = 'info';
    $choices = array('info'=>$alert_info, 'error'=>$alert_warning, 'success'=>$alert_general);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Title.
    $name = 'theme_safeminds/alert1title';
    $title = get_string('alerttitle', 'theme_safeminds');
    $description = get_string('alerttitledesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Text.
    $name = 'theme_safeminds/alert1text';
    $title = get_string('alerttext', 'theme_safeminds');
    $description = get_string('alerttextdesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for Alert Two
    $name = 'theme_safeminds/alert2info';
    $heading = get_string('alert2', 'theme_safeminds');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Enable Alert
    $name = 'theme_safeminds/enable2alert';
    $title = get_string('enablealert', 'theme_safeminds');
    $description = get_string('enablealertdesc', 'theme_safeminds');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Type.
    $name = 'theme_safeminds/alert2type';
    $title = get_string('alerttype' , 'theme_safeminds');
    $description = get_string('alerttypedesc', 'theme_safeminds');
    $alert_info = get_string('alert_info', 'theme_safeminds');
    $alert_warning = get_string('alert_warning', 'theme_safeminds');
    $alert_general = get_string('alert_general', 'theme_safeminds');
    $default = 'info';
    $choices = array('info'=>$alert_info, 'error'=>$alert_warning, 'success'=>$alert_general);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Title.
    $name = 'theme_safeminds/alert2title';
    $title = get_string('alerttitle', 'theme_safeminds');
    $description = get_string('alerttitledesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Text.
    $name = 'theme_safeminds/alert2text';
    $title = get_string('alerttext', 'theme_safeminds');
    $description = get_string('alerttextdesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for Alert Three
    $name = 'theme_safeminds/alert3info';
    $heading = get_string('alert3', 'theme_safeminds');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Enable Alert
    $name = 'theme_safeminds/enable3alert';
    $title = get_string('enablealert', 'theme_safeminds');
    $description = get_string('enablealertdesc', 'theme_safeminds');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Type.
    $name = 'theme_safeminds/alert3type';
    $title = get_string('alerttype' , 'theme_safeminds');
    $description = get_string('alerttypedesc', 'theme_safeminds');
    $alert_info = get_string('alert_info', 'theme_safeminds');
    $alert_warning = get_string('alert_warning', 'theme_safeminds');
    $alert_general = get_string('alert_general', 'theme_safeminds');
    $default = 'info';
    $choices = array('info'=>$alert_info, 'error'=>$alert_warning, 'success'=>$alert_general);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Title.
    $name = 'theme_safeminds/alert3title';
    $title = get_string('alerttitle', 'theme_safeminds');
    $description = get_string('alerttitledesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Text.
    $name = 'theme_safeminds/alert3text';
    $title = get_string('alerttext', 'theme_safeminds');
    $description = get_string('alerttextdesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
            
    
    $ADMIN->add('theme_safeminds', $temp);
    
    /* Analytics Settings */
    $temp = new admin_settingpage('theme_safeminds_analytics', get_string('analyticsheading', 'theme_safeminds'));
	$temp->add(new admin_setting_heading('theme_safeminds_analytics', get_string('analyticsheadingsub', 'theme_safeminds'),
            format_text(get_string('analyticsdesc' , 'theme_safeminds'), FORMAT_MARKDOWN)));
    
    // Enable Analytics
    $name = 'theme_safeminds/useanalytics';
    $title = get_string('useanalytics', 'theme_safeminds');
    $description = get_string('useanalyticsdesc', 'theme_safeminds');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Google Analytics ID
    $name = 'theme_safeminds/analyticsid';
    $title = get_string('analyticsid', 'theme_safeminds');
    $description = get_string('analyticsiddesc', 'theme_safeminds');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Clean Analytics URL
    $name = 'theme_safeminds/analyticsclean';
    $title = get_string('analyticsclean', 'theme_safeminds');
    $description = get_string('analyticscleandesc', 'theme_safeminds');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
        
    $ADMIN->add('theme_safeminds', $temp);

