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
 * @package   theme_mhpl
 * @copyright 2013 Julian Ridden
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$settings = null;

defined('MOODLE_INTERNAL') || die;


	$ADMIN->add('themes', new admin_category('theme_mhpl', 'mhpl'));

	// "geneicsettings" settingpage
	$temp = new admin_settingpage('theme_mhpl_generic',  get_string('geneicsettings', 'theme_mhpl'));
	
	// Default Site icon setting.
    $name = 'theme_mhpl/siteicon';
    $title = get_string('siteicon', 'theme_mhpl');
    $description = get_string('siteicondesc', 'theme_mhpl');
    $default = 'laptop';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $temp->add($setting);
    
    // Include Awesome Font from Bootstrapcdn
    $name = 'theme_mhpl/bootstrapcdn';
    $title = get_string('bootstrapcdn', 'theme_mhpl');
    $description = get_string('bootstrapcdndesc', 'theme_mhpl');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
	
    // Logo file setting.
    $name = 'theme_mhpl/logo';
    $title = get_string('logo', 'theme_mhpl');
    $description = get_string('logodesc', 'theme_mhpl');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'logo');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Font Selector.
    $name = 'theme_mhpl/fontselect';
    $title = get_string('fontselect' , 'theme_mhpl');
    $description = get_string('fontselectdesc', 'theme_mhpl');
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
    $name = 'theme_mhpl/headerprofilepic';
    $title = get_string('headerprofilepic', 'theme_mhpl');
    $description = get_string('headerprofilepicdesc', 'theme_mhpl');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Fixed or Variable Width.
    $name = 'theme_mhpl/pagewidth';
    $title = get_string('pagewidth', 'theme_mhpl');
    $description = get_string('pagewidthdesc', 'theme_mhpl');
    $default = 1200;
    $choices = array(1900=>get_string('fixedwidthwide','theme_mhpl'), 1200=>get_string('fixedwidthnarrow','theme_mhpl'), 100=>get_string('variablewidth','theme_mhpl'));
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Custom or standard layout.
    $name = 'theme_mhpl/layout';
    $title = get_string('layout', 'theme_mhpl');
    $description = get_string('layoutdesc', 'theme_mhpl');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //Include the Editicons css rules
    $name = 'theme_mhpl/editicons';
    $title = get_string('editicons', 'theme_mhpl');
    $description = get_string('editiconsdesc', 'theme_mhpl');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $temp->add($setting);
    
    // Performance Information Display.
    $name = 'theme_mhpl/perfinfo';
    $title = get_string('perfinfo' , 'theme_mhpl');
    $description = get_string('perfinfodesc', 'theme_mhpl');
    $perf_max = get_string('perf_max', 'theme_mhpl');
    $perf_min = get_string('perf_min', 'theme_mhpl');
    $default = 'min';
    $choices = array('min'=>$perf_min, 'max'=>$perf_max);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Navbar Seperator.
    $name = 'theme_mhpl/navbarsep';
    $title = get_string('navbarsep' , 'theme_mhpl');
    $description = get_string('navbarsepdesc', 'theme_mhpl');
    $nav_thinbracket = get_string('nav_thinbracket', 'theme_mhpl');
    $nav_doublebracket = get_string('nav_doublebracket', 'theme_mhpl');
    $nav_thickbracket = get_string('nav_thickbracket', 'theme_mhpl');
    $nav_slash = get_string('nav_slash', 'theme_mhpl');
    $nav_pipe = get_string('nav_pipe', 'theme_mhpl');
    $dontdisplay = get_string('dontdisplay', 'theme_mhpl');
    $default = '/';
    $choices = array('/'=>$nav_slash, '\f105'=>$nav_thinbracket, '\f101'=>$nav_doublebracket, '\f054'=>$nav_thickbracket, '|'=>$nav_pipe);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Copyright setting.
    $name = 'theme_mhpl/copyright';
    $title = get_string('copyright', 'theme_mhpl');
    $description = get_string('copyrightdesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $temp->add($setting);
    
    // Footnote setting.
    $name = 'theme_mhpl/footnote';
    $title = get_string('footnote', 'theme_mhpl');
    $description = get_string('footnotedesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Custom CSS file.
    $name = 'theme_mhpl/customcss';
    $title = get_string('customcss', 'theme_mhpl');
    $description = get_string('customcssdesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $ADMIN->add('theme_mhpl', $temp);
    
    /* Custom Menu Settings */
    $temp = new admin_settingpage('theme_mhpl_custommenu', get_string('custommenuheading', 'theme_mhpl'));
	            
    //This is the descriptor for the following Moodle color settings
    $name = 'theme_mhpl/mydashboardinfo';
    $heading = get_string('mydashboardinfo', 'theme_mhpl');
    $information = get_string('mydashboardinfodesc', 'theme_mhpl');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Toggle dashboard display in custommenu.
    $name = 'theme_mhpl/displaymydashboard';
    $title = get_string('displaymydashboard', 'theme_mhpl');
    $description = get_string('displaymydashboarddesc', 'theme_mhpl');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for the following Moodle color settings
    $name = 'theme_mhpl/mycoursesinfo';
    $heading = get_string('mycoursesinfo', 'theme_mhpl');
    $information = get_string('mycoursesinfodesc', 'theme_mhpl');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Toggle courses display in custommenu.
    $name = 'theme_mhpl/displaymycourses';
    $title = get_string('displaymycourses', 'theme_mhpl');
    $description = get_string('displaymycoursesdesc', 'theme_mhpl');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Set terminology for dropdown course list
	$name = 'theme_mhpl/mycoursetitle';
	$title = get_string('mycoursetitle','theme_mhpl');
	$description = get_string('mycoursetitledesc', 'theme_mhpl');
	$default = 'course';
	$choices = array(
		'course' => get_string('mycourses', 'theme_mhpl'),
		'unit' => get_string('myunits', 'theme_mhpl'),
		'class' => get_string('myclasses', 'theme_mhpl'),
		'module' => get_string('mymodules', 'theme_mhpl')
	);
	$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
	$setting->set_updatedcallback('theme_reset_all_caches');
	$temp->add($setting);
    
    $ADMIN->add('theme_mhpl', $temp);
    
	/* Color Settings */
    $temp = new admin_settingpage('theme_mhpl_color', get_string('colorheading', 'theme_mhpl'));
    $temp->add(new admin_setting_heading('theme_mhpl_color', get_string('colorheadingsub', 'theme_mhpl'),
            format_text(get_string('colordesc' , 'theme_mhpl'), FORMAT_MARKDOWN)));

    // Background Image.
    $name = 'theme_mhpl/pagebackground';
    $title = get_string('pagebackground', 'theme_mhpl');
    $description = get_string('pagebackgrounddesc', 'theme_mhpl');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'pagebackground');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Main theme colour setting.
    $name = 'theme_mhpl/themecolor';
    $title = get_string('themecolor', 'theme_mhpl');
    $description = get_string('themecolordesc', 'theme_mhpl');
    $default = '#30add1';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Main theme Hover colour setting.
    $name = 'theme_mhpl/themehovercolor';
    $title = get_string('themehovercolor', 'theme_mhpl');
    $description = get_string('themehovercolordesc', 'theme_mhpl');
    $default = '#29a1c4';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for the Slideshow
    $name = 'theme_mhpl/slidecolorinfo';
    $heading = get_string('slidecolors', 'theme_mhpl');
    $information = get_string('slidecolorsdesc', 'theme_mhpl');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
      // Slide Header colour setting.
    $name = 'theme_mhpl/slideheadercolor';
    $title = get_string('slideheadercolor', 'theme_mhpl');
    $description = get_string('slideheadercolordesc', 'theme_mhpl');
    $default = '#30add1';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Slide Text colour setting.
    $name = 'theme_mhpl/slidecolor';
    $title = get_string('slidecolor', 'theme_mhpl');
    $description = get_string('slidecolordesc', 'theme_mhpl');
    $default = '#888';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Slide Button colour setting.
    $name = 'theme_mhpl/slidebuttoncolor';
    $title = get_string('slidebuttoncolor', 'theme_mhpl');
    $description = get_string('slidebuttoncolordesc', 'theme_mhpl');
    $default = '#30add1';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
        //This is the descriptor for the Slideshow
    $name = 'theme_mhpl/footercolorinfo';
    $heading = get_string('footercolors', 'theme_mhpl');
    $information = get_string('footercolorsdesc', 'theme_mhpl');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Footer background colour setting.
    $name = 'theme_mhpl/footercolor';
    $title = get_string('footercolor', 'theme_mhpl');
    $description = get_string('footercolordesc', 'theme_mhpl');
    $default = '#000000';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer text colour setting.
    $name = 'theme_mhpl/footertextcolor';
    $title = get_string('footertextcolor', 'theme_mhpl');
    $description = get_string('footertextcolordesc', 'theme_mhpl');
    $default = '#DDDDDD';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer Block Heading colour setting.
    $name = 'theme_mhpl/footerheadingcolor';
    $title = get_string('footerheadingcolor', 'theme_mhpl');
    $description = get_string('footerheadingcolordesc', 'theme_mhpl');
    $default = '#CCCCCC';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer Seperator colour setting.
    $name = 'theme_mhpl/footersepcolor';
    $title = get_string('footersepcolor', 'theme_mhpl');
    $description = get_string('footersepcolordesc', 'theme_mhpl');
    $default = '#313131';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer URL colour setting.
    $name = 'theme_mhpl/footerurlcolor';
    $title = get_string('footerurlcolor', 'theme_mhpl');
    $description = get_string('footerurlcolordesc', 'theme_mhpl');
    $default = '#BBBBBB';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Footer URL hover colour setting.
    $name = 'theme_mhpl/footerhovercolor';
    $title = get_string('footerhovercolor', 'theme_mhpl');
    $description = get_string('footerhovercolordesc', 'theme_mhpl');
    $default = '#FFFFFF';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);



 	$ADMIN->add('theme_mhpl', $temp);
 
 
    /* Slideshow Widget Settings */
    $temp = new admin_settingpage('theme_mhpl_slideshow', get_string('slideshowheading', 'theme_mhpl'));
    $temp->add(new admin_setting_heading('theme_mhpl_slideshow', get_string('slideshowheadingsub', 'theme_mhpl'),
            format_text(get_string('slideshowdesc' , 'theme_mhpl'), FORMAT_MARKDOWN)));
    
    // Toggle Slideshow.
    $name = 'theme_mhpl/toggleslideshow';
    $title = get_string('toggleslideshow' , 'theme_mhpl');
    $description = get_string('toggleslideshowdesc', 'theme_mhpl');
    $alwaysdisplay = get_string('alwaysdisplay', 'theme_mhpl');
    $displaybeforelogin = get_string('displaybeforelogin', 'theme_mhpl');
    $displayafterlogin = get_string('displayafterlogin', 'theme_mhpl');
    $dontdisplay = get_string('dontdisplay', 'theme_mhpl');
    $default = 'alwaysdisplay';
    $choices = array('1'=>$alwaysdisplay, '2'=>$displaybeforelogin, '3'=>$displayafterlogin, '0'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Hide slideshow on phones.
    $name = 'theme_mhpl/hideonphone';
    $title = get_string('hideonphone' , 'theme_mhpl');
    $description = get_string('hideonphonedesc', 'theme_mhpl');
    $display = get_string('alwaysdisplay', 'theme_mhpl');
    $dontdisplay = get_string('dontdisplay', 'theme_mhpl');
    $default = 'display';
    $choices = array(''=>$display, 'hidden-phone'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Slideshow Design Picker.
    $name = 'theme_mhpl/slideshowvariant';
    $title = get_string('slideshowvariant' , 'theme_mhpl');
    $description = get_string('slideshowvariantdesc', 'theme_mhpl');
    $slideshow1 = get_string('slideshow1', 'theme_mhpl');
    $slideshow2 = get_string('slideshow2', 'theme_mhpl');
    $default = 'slideshow1';
    $choices = array('1'=>$slideshow1, '2'=>$slideshow2);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 1
     */
     
    //This is the descriptor for Slide One
    $name = 'theme_mhpl/slide1info';
    $heading = get_string('slide1', 'theme_mhpl');
    $information = get_string('slideinfodesc', 'theme_mhpl');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);

    // Title.
    $name = 'theme_mhpl/slide1';
    $title = get_string('slidetitle', 'theme_mhpl');
    $description = get_string('slidetitledesc', 'theme_mhpl');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_mhpl/slide1image';
    $title = get_string('slideimage', 'theme_mhpl');
    $description = get_string('slideimagedesc', 'theme_mhpl');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide1image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_mhpl/slide1caption';
    $title = get_string('slidecaption', 'theme_mhpl');
    $description = get_string('slidecaptiondesc', 'theme_mhpl');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_mhpl/slide1url';
    $title = get_string('slideurl', 'theme_mhpl');
    $description = get_string('slideurldesc', 'theme_mhpl');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 2
     */
     
    //This is the descriptor for Slide Two
    $name = 'theme_mhpl/slide2info';
    $heading = get_string('slide2', 'theme_mhpl');
    $information = get_string('slideinfodesc', 'theme_mhpl');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);

    // Title.
    $name = 'theme_mhpl/slide2';
    $title = get_string('slidetitle', 'theme_mhpl');
    $description = get_string('slidetitledesc', 'theme_mhpl');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_mhpl/slide2image';
    $title = get_string('slideimage', 'theme_mhpl');
    $description = get_string('slideimagedesc', 'theme_mhpl');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide2image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_mhpl/slide2caption';
    $title = get_string('slidecaption', 'theme_mhpl');
    $description = get_string('slidecaptiondesc', 'theme_mhpl');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_mhpl/slide2url';
    $title = get_string('slideurl', 'theme_mhpl');
    $description = get_string('slideurldesc', 'theme_mhpl');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 3
     */

    //This is the descriptor for Slide Three
    $name = 'theme_mhpl/slide3info';
    $heading = get_string('slide3', 'theme_mhpl');
    $information = get_string('slideinfodesc', 'theme_mhpl');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Title.
    $name = 'theme_mhpl/slide3';
    $title = get_string('slidetitle', 'theme_mhpl');
    $description = get_string('slidetitledesc', 'theme_mhpl');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_mhpl/slide3image';
    $title = get_string('slideimage', 'theme_mhpl');
    $description = get_string('slideimagedesc', 'theme_mhpl');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide3image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_mhpl/slide3caption';
    $title = get_string('slidecaption', 'theme_mhpl');
    $description = get_string('slidecaptiondesc', 'theme_mhpl');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_mhpl/slide3url';
    $title = get_string('slideurl', 'theme_mhpl');
    $description = get_string('slideurldesc', 'theme_mhpl');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 4
     */
     
    //This is the descriptor for Slide Four
    $name = 'theme_mhpl/slide4info';
    $heading = get_string('slide4', 'theme_mhpl');
    $information = get_string('slideinfodesc', 'theme_mhpl');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);

    // Title.
    $name = 'theme_mhpl/slide4';
    $title = get_string('slidetitle', 'theme_mhpl');
    $description = get_string('slidetitledesc', 'theme_mhpl');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_mhpl/slide4image';
    $title = get_string('slideimage', 'theme_mhpl');
    $description = get_string('slideimagedesc', 'theme_mhpl');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide4image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_mhpl/slide4caption';
    $title = get_string('slidecaption', 'theme_mhpl');
    $description = get_string('slidecaptiondesc', 'theme_mhpl');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_mhpl/slide4url';
    $title = get_string('slideurl', 'theme_mhpl');
    $description = get_string('slideurldesc', 'theme_mhpl');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    
    $ADMIN->add('theme_mhpl', $temp);
    
    $temp = new admin_settingpage('theme_mhpl_frontcontent', get_string('frontcontentheading', 'theme_mhpl'));
	$temp->add(new admin_setting_heading('theme_mhpl_frontcontent', get_string('frontcontentheadingsub', 'theme_mhpl'),
            format_text(get_string('frontcontentdesc' , 'theme_mhpl'), FORMAT_MARKDOWN)));
    
    // Enable Frontpage Content
    $name = 'theme_mhpl/usefrontcontent';
    $title = get_string('usefrontcontent', 'theme_mhpl');
    $description = get_string('usefrontcontentdesc', 'theme_mhpl');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Frontpage Content
    $name = 'theme_mhpl/frontcontentarea';
    $title = get_string('frontcontentarea', 'theme_mhpl');
    $description = get_string('frontcontentareadesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Frontpage Block alignment.
    $name = 'theme_mhpl/frontpageblocks';
    $title = get_string('frontpageblocks' , 'theme_mhpl');
    $description = get_string('frontpageblocksdesc', 'theme_mhpl');
    $left = get_string('left', 'theme_mhpl');
    $right = get_string('right', 'theme_mhpl');
    $default = 'left';
    $choices = array('1'=>$left, '0'=>$right);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Toggle Frontpage Middle Blocks
    $name = 'theme_mhpl/frontpagemiddleblocks';
    $title = get_string('frontpagemiddleblocks' , 'theme_mhpl');
    $description = get_string('frontpagemiddleblocksdesc', 'theme_mhpl');
    $alwaysdisplay = get_string('alwaysdisplay', 'theme_mhpl');
    $displaybeforelogin = get_string('displaybeforelogin', 'theme_mhpl');
    $displayafterlogin = get_string('displayafterlogin', 'theme_mhpl');
    $dontdisplay = get_string('dontdisplay', 'theme_mhpl');
    $default = 'display';
    $choices = array('1'=>$alwaysdisplay, '2'=>$displaybeforelogin, '3'=>$displayafterlogin, '0'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
        
    $ADMIN->add('theme_mhpl', $temp);
    

	/* Marketing Spot Settings */
	$temp = new admin_settingpage('theme_mhpl_marketing', get_string('marketingheading', 'theme_mhpl'));
	$temp->add(new admin_setting_heading('theme_mhpl_marketing', get_string('marketingheadingsub', 'theme_mhpl'),
            format_text(get_string('marketingdesc' , 'theme_mhpl'), FORMAT_MARKDOWN)));
	
	// Toggle Marketing Spots.
    $name = 'theme_mhpl/togglemarketing';
    $title = get_string('togglemarketing' , 'theme_mhpl');
    $description = get_string('togglemarketingdesc', 'theme_mhpl');
    $alwaysdisplay = get_string('alwaysdisplay', 'theme_mhpl');
    $displaybeforelogin = get_string('displaybeforelogin', 'theme_mhpl');
    $displayafterlogin = get_string('displayafterlogin', 'theme_mhpl');
    $dontdisplay = get_string('dontdisplay', 'theme_mhpl');
    $default = 'display';
    $choices = array('1'=>$alwaysdisplay, '2'=>$displaybeforelogin, '3'=>$displayafterlogin, '0'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Marketing Spot Image Height
	$name = 'theme_mhpl/marketingheight';
	$title = get_string('marketingheight','theme_mhpl');
	$description = get_string('marketingheightdesc', 'theme_mhpl');
	$default = 100;
	$choices = array(50, 100, 150, 200, 250, 300);
	$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
	$temp->add($setting);
	
	//This is the descriptor for Marketing Spot One
    $name = 'theme_mhpl/marketing1info';
    $heading = get_string('marketing1', 'theme_mhpl');
    $information = get_string('marketinginfodesc', 'theme_mhpl');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
	
	//Marketing Spot One.
	$name = 'theme_mhpl/marketing1';
    $title = get_string('marketingtitle', 'theme_mhpl');
    $description = get_string('marketingtitledesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mhpl/marketing1icon';
    $title = get_string('marketingicon', 'theme_mhpl');
    $description = get_string('marketingicondesc', 'theme_mhpl');
    $default = 'star';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mhpl/marketing1image';
    $title = get_string('marketingimage', 'theme_mhpl');
    $description = get_string('marketingimagedesc', 'theme_mhpl');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing1image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mhpl/marketing1content';
    $title = get_string('marketingcontent', 'theme_mhpl');
    $description = get_string('marketingcontentdesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mhpl/marketing1buttontext';
    $title = get_string('marketingbuttontext', 'theme_mhpl');
    $description = get_string('marketingbuttontextdesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mhpl/marketing1buttonurl';
    $title = get_string('marketingbuttonurl', 'theme_mhpl');
    $description = get_string('marketingbuttonurldesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for Marketing Spot Two
    $name = 'theme_mhpl/marketing2info';
    $heading = get_string('marketing2', 'theme_mhpl');
    $information = get_string('marketinginfodesc', 'theme_mhpl');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    //Marketing Spot Two.
	$name = 'theme_mhpl/marketing2';
    $title = get_string('marketingtitle', 'theme_mhpl');
    $description = get_string('marketingtitledesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mhpl/marketing2icon';
    $title = get_string('marketingicon', 'theme_mhpl');
    $description = get_string('marketingicondesc', 'theme_mhpl');
    $default = 'star';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mhpl/marketing2image';
    $title = get_string('marketingimage', 'theme_mhpl');
    $description = get_string('marketingimagedesc', 'theme_mhpl');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing2image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mhpl/marketing2content';
    $title = get_string('marketingcontent', 'theme_mhpl');
    $description = get_string('marketingcontentdesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mhpl/marketing2buttontext';
    $title = get_string('marketingbuttontext', 'theme_mhpl');
    $description = get_string('marketingbuttontextdesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mhpl/marketing2buttonurl';
    $title = get_string('marketingbuttonurl', 'theme_mhpl');
    $description = get_string('marketingbuttonurldesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for Marketing Spot Three
    $name = 'theme_mhpl/marketing3info';
    $heading = get_string('marketing3', 'theme_mhpl');
    $information = get_string('marketinginfodesc', 'theme_mhpl');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    //Marketing Spot Three.
	$name = 'theme_mhpl/marketing3';
    $title = get_string('marketingtitle', 'theme_mhpl');
    $description = get_string('marketingtitledesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mhpl/marketing3icon';
    $title = get_string('marketingicon', 'theme_mhpl');
    $description = get_string('marketingicondesc', 'theme_mhpl');
    $default = 'star';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mhpl/marketing3image';
    $title = get_string('marketingimage', 'theme_mhpl');
    $description = get_string('marketingimagedesc', 'theme_mhpl');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing3image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mhpl/marketing3content';
    $title = get_string('marketingcontent', 'theme_mhpl');
    $description = get_string('marketingcontentdesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mhpl/marketing3buttontext';
    $title = get_string('marketingbuttontext', 'theme_mhpl');
    $description = get_string('marketingbuttontextdesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_mhpl/marketing3buttonurl';
    $title = get_string('marketingbuttonurl', 'theme_mhpl');
    $description = get_string('marketingbuttonurldesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    
    $ADMIN->add('theme_mhpl', $temp);

	
	/* Social Network Settings */
	$temp = new admin_settingpage('theme_mhpl_social', get_string('socialheading', 'theme_mhpl'));
	$temp->add(new admin_setting_heading('theme_mhpl_social', get_string('socialheadingsub', 'theme_mhpl'),
            format_text(get_string('socialdesc' , 'theme_mhpl'), FORMAT_MARKDOWN)));
	
    // Website url setting.
    $name = 'theme_mhpl/website';
    $title = get_string('website', 'theme_mhpl');
    $description = get_string('websitedesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Facebook url setting.
    $name = 'theme_mhpl/facebook';
    $title = get_string('facebook', 'theme_mhpl');
    $description = get_string('facebookdesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Flickr url setting.
    $name = 'theme_mhpl/flickr';
    $title = get_string('flickr', 'theme_mhpl');
    $description = get_string('flickrdesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Twitter url setting.
    $name = 'theme_mhpl/twitter';
    $title = get_string('twitter', 'theme_mhpl');
    $description = get_string('twitterdesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Google+ url setting.
    $name = 'theme_mhpl/googleplus';
    $title = get_string('googleplus', 'theme_mhpl');
    $description = get_string('googleplusdesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // LinkedIn url setting.
    $name = 'theme_mhpl/linkedin';
    $title = get_string('linkedin', 'theme_mhpl');
    $description = get_string('linkedindesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Pinterest url setting.
    $name = 'theme_mhpl/pinterest';
    $title = get_string('pinterest', 'theme_mhpl');
    $description = get_string('pinterestdesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Instagram url setting.
    $name = 'theme_mhpl/instagram';
    $title = get_string('instagram', 'theme_mhpl');
    $description = get_string('instagramdesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // YouTube url setting.
    $name = 'theme_mhpl/youtube';
    $title = get_string('youtube', 'theme_mhpl');
    $description = get_string('youtubedesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Skype url setting.
    $name = 'theme_mhpl/skype';
    $title = get_string('skype', 'theme_mhpl');
    $description = get_string('skypedesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
 
    // VKontakte url setting.
    $name = 'theme_mhpl/vk';
    $title = get_string('vk', 'theme_mhpl');
    $description = get_string('vkdesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting); 
    
    $ADMIN->add('theme_mhpl', $temp);
    
    $temp = new admin_settingpage('theme_mhpl_mobileapps', get_string('mobileappsheading', 'theme_mhpl'));
	$temp->add(new admin_setting_heading('theme_mhpl_mobileapps', get_string('mobileappsheadingsub', 'theme_mhpl'),
            format_text(get_string('mobileappsdesc' , 'theme_mhpl'), FORMAT_MARKDOWN)));
    // Android App url setting.
    $name = 'theme_mhpl/android';
    $title = get_string('android', 'theme_mhpl');
    $description = get_string('androiddesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iOS App url setting.
    $name = 'theme_mhpl/ios';
    $title = get_string('ios', 'theme_mhpl');
    $description = get_string('iosdesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for iOS Icons
    $name = 'theme_mhpl/iosiconinfo';
    $heading = get_string('iosicon', 'theme_mhpl');
    $information = get_string('iosicondesc', 'theme_mhpl');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // iPhone Icon.
    $name = 'theme_mhpl/iphoneicon';
    $title = get_string('iphoneicon', 'theme_mhpl');
    $description = get_string('iphoneicondesc', 'theme_mhpl');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'iphoneicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iPhone Retina Icon.
    $name = 'theme_mhpl/iphoneretinaicon';
    $title = get_string('iphoneretinaicon', 'theme_mhpl');
    $description = get_string('iphoneretinaicondesc', 'theme_mhpl');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'iphoneretinaicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iPad Icon.
    $name = 'theme_mhpl/ipadicon';
    $title = get_string('ipadicon', 'theme_mhpl');
    $description = get_string('ipadicondesc', 'theme_mhpl');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'ipadicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // iPad Retina Icon.
    $name = 'theme_mhpl/ipadretinaicon';
    $title = get_string('ipadretinaicon', 'theme_mhpl');
    $description = get_string('ipadretinaicondesc', 'theme_mhpl');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'ipadretinaicon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $ADMIN->add('theme_mhpl', $temp);
    
    /* User Alerts */
    $temp = new admin_settingpage('theme_mhpl_alerts', get_string('alertsheading', 'theme_mhpl'));
	$temp->add(new admin_setting_heading('theme_mhpl_alerts', get_string('alertsheadingsub', 'theme_mhpl'),
            format_text(get_string('alertsdesc' , 'theme_mhpl'), FORMAT_MARKDOWN)));
    
    //This is the descriptor for Alert One
    $name = 'theme_mhpl/alert1info';
    $heading = get_string('alert1', 'theme_mhpl');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Enable Alert
    $name = 'theme_mhpl/enable1alert';
    $title = get_string('enablealert', 'theme_mhpl');
    $description = get_string('enablealertdesc', 'theme_mhpl');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Type.
    $name = 'theme_mhpl/alert1type';
    $title = get_string('alerttype' , 'theme_mhpl');
    $description = get_string('alerttypedesc', 'theme_mhpl');
    $alert_info = get_string('alert_info', 'theme_mhpl');
    $alert_warning = get_string('alert_warning', 'theme_mhpl');
    $alert_general = get_string('alert_general', 'theme_mhpl');
    $default = 'info';
    $choices = array('info'=>$alert_info, 'error'=>$alert_warning, 'success'=>$alert_general);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Title.
    $name = 'theme_mhpl/alert1title';
    $title = get_string('alerttitle', 'theme_mhpl');
    $description = get_string('alerttitledesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Text.
    $name = 'theme_mhpl/alert1text';
    $title = get_string('alerttext', 'theme_mhpl');
    $description = get_string('alerttextdesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for Alert Two
    $name = 'theme_mhpl/alert2info';
    $heading = get_string('alert2', 'theme_mhpl');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Enable Alert
    $name = 'theme_mhpl/enable2alert';
    $title = get_string('enablealert', 'theme_mhpl');
    $description = get_string('enablealertdesc', 'theme_mhpl');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Type.
    $name = 'theme_mhpl/alert2type';
    $title = get_string('alerttype' , 'theme_mhpl');
    $description = get_string('alerttypedesc', 'theme_mhpl');
    $alert_info = get_string('alert_info', 'theme_mhpl');
    $alert_warning = get_string('alert_warning', 'theme_mhpl');
    $alert_general = get_string('alert_general', 'theme_mhpl');
    $default = 'info';
    $choices = array('info'=>$alert_info, 'error'=>$alert_warning, 'success'=>$alert_general);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Title.
    $name = 'theme_mhpl/alert2title';
    $title = get_string('alerttitle', 'theme_mhpl');
    $description = get_string('alerttitledesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Text.
    $name = 'theme_mhpl/alert2text';
    $title = get_string('alerttext', 'theme_mhpl');
    $description = get_string('alerttextdesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //This is the descriptor for Alert Three
    $name = 'theme_mhpl/alert3info';
    $heading = get_string('alert3', 'theme_mhpl');
    $setting = new admin_setting_heading($name, $heading, $information);
    $temp->add($setting);
    
    // Enable Alert
    $name = 'theme_mhpl/enable3alert';
    $title = get_string('enablealert', 'theme_mhpl');
    $description = get_string('enablealertdesc', 'theme_mhpl');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Type.
    $name = 'theme_mhpl/alert3type';
    $title = get_string('alerttype' , 'theme_mhpl');
    $description = get_string('alerttypedesc', 'theme_mhpl');
    $alert_info = get_string('alert_info', 'theme_mhpl');
    $alert_warning = get_string('alert_warning', 'theme_mhpl');
    $alert_general = get_string('alert_general', 'theme_mhpl');
    $default = 'info';
    $choices = array('info'=>$alert_info, 'error'=>$alert_warning, 'success'=>$alert_general);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Title.
    $name = 'theme_mhpl/alert3title';
    $title = get_string('alerttitle', 'theme_mhpl');
    $description = get_string('alerttitledesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Alert Text.
    $name = 'theme_mhpl/alert3text';
    $title = get_string('alerttext', 'theme_mhpl');
    $description = get_string('alerttextdesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
            
    
    $ADMIN->add('theme_mhpl', $temp);
    
    /* Analytics Settings */
    $temp = new admin_settingpage('theme_mhpl_analytics', get_string('analyticsheading', 'theme_mhpl'));
	$temp->add(new admin_setting_heading('theme_mhpl_analytics', get_string('analyticsheadingsub', 'theme_mhpl'),
            format_text(get_string('analyticsdesc' , 'theme_mhpl'), FORMAT_MARKDOWN)));
    
    // Enable Analytics
    $name = 'theme_mhpl/useanalytics';
    $title = get_string('useanalytics', 'theme_mhpl');
    $description = get_string('useanalyticsdesc', 'theme_mhpl');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Google Analytics ID
    $name = 'theme_mhpl/analyticsid';
    $title = get_string('analyticsid', 'theme_mhpl');
    $description = get_string('analyticsiddesc', 'theme_mhpl');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Clean Analytics URL
    $name = 'theme_mhpl/analyticsclean';
    $title = get_string('analyticsclean', 'theme_mhpl');
    $description = get_string('analyticscleandesc', 'theme_mhpl');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
        
    $ADMIN->add('theme_mhpl', $temp);

