<?php
/**
* The czr_fn__f() function is a wrapper of the WP built-in apply_filters() where the $value param becomes optional.
*
* By convention in Customizr, filter hooks are used as follow :
* 1) declared with add_filters in class constructors (mainly) to hook on WP built-in callbacks or create "getters" used everywhere
* 2) declared with apply_filters in methods to make the code extensible for developers
* 3) accessed with czr_fn__f() to return values (while front end content is handled with action hooks)
*
* Used everywhere in Customizr. Can pass up to five variables to the filter callback.
*
* @since Customizr 3.0
*/
if( ! function_exists( 'czr_fn__f' ) ) :
    function czr_fn__f( $tag , $value = null , $arg_one = null , $arg_two = null , $arg_three = null , $arg_four = null , $arg_five = null) {
       return apply_filters( $tag , $value , $arg_one , $arg_two , $arg_three , $arg_four , $arg_five );
    }
endif;

//This function is the only one with a different prefix.
//It has been kept in the theme for retro-compatibility.
if( ! function_exists( 'tc__f' ) ) :
    function tc__f( $tag , $value = null , $arg_one = null , $arg_two = null , $arg_three = null , $arg_four = null , $arg_five = null) {
       return czr_fn__f( $tag , $value, $arg_one, $arg_two , $arg_three, $arg_four, $arg_five );
    }
endif;

/**
* Fires the theme : constants definition, core classes loading
*
*
* @package      Customizr
* @subpackage   classes
* @since        3.0
* @author       Nicolas GUILLAUME <nicolas@presscustomizr.com>
* @copyright    Copyright (c) 2013-2015, Nicolas GUILLAUME
* @link         http://presscustomizr.com/customizr
* @license      http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
if ( ! class_exists( 'CZR___' ) ) :
  class CZR___ {
    //Access any method or var of the class with classname::$instance -> var or method():
    static $instance;
    public $tc_core;
    public $is_customizing;
    public static $theme_name;
    public static $tc_option_group;

    function __construct () {
      self::$instance =& $this;

      /* GETS INFORMATIONS FROM STYLE.CSS */
      // get themedata version wp 3.4+
      if( function_exists( 'wp_get_theme' ) ) {
        //get WP_Theme object of customizr
        $tc_theme                     = wp_get_theme();

        //Get infos from parent theme if using a child theme
        $tc_theme = $tc_theme -> parent() ? $tc_theme -> parent() : $tc_theme;

        $tc_base_data['prefix']       = $tc_base_data['title'] = $tc_theme -> name;
        $tc_base_data['version']      = $tc_theme -> version;
        $tc_base_data['authoruri']    = $tc_theme -> {'Author URI'};
      }

      // get themedata for lower versions (get_stylesheet_directory() points to the current theme root, child or parent)
      else {
           $tc_base_data                = call_user_func('get_' .'theme_data', get_stylesheet_directory().'/style.css' );
           $tc_base_data['prefix']      = $tc_base_data['title'];
      }

      self::$theme_name                 = sanitize_file_name( strtolower($tc_base_data['title']) );

      //CUSTOMIZR_VER is the Version
      if( ! defined( 'CUSTOMIZR_VER' ) )      define( 'CUSTOMIZR_VER' , $tc_base_data['version'] );
      //TC_BASE is the root server path of the parent theme
      if( ! defined( 'TC_BASE' ) )            define( 'TC_BASE' , get_template_directory().'/' );
      //TC_BASE_CHILD is the root server path of the child theme
      if( ! defined( 'TC_BASE_CHILD' ) )      define( 'TC_BASE_CHILD' , get_stylesheet_directory().'/' );
      //TC_BASE_URL http url of the loaded parent theme
      if( ! defined( 'TC_BASE_URL' ) )        define( 'TC_BASE_URL' , get_template_directory_uri() . '/' );
      //TC_BASE_URL_CHILD http url of the loaded child theme
      if( ! defined( 'TC_BASE_URL_CHILD' ) )  define( 'TC_BASE_URL_CHILD' , get_stylesheet_directory_uri() . '/' );
      //THEMENAME contains the Name of the currently loaded theme
      if( ! defined( 'THEMENAME' ) )          define( 'THEMENAME' , $tc_base_data['title'] );
      //CZR_WEBSITE is the home website of Customizr
      if( ! defined( 'CZR_WEBSITE' ) )         define( 'CZR_WEBSITE' , $tc_base_data['authoruri'] );

      //OPTION PREFIX //all customizr theme options start by "tc_" by convention (actually since the theme was created.. tc for Themes & Co...)
      if( ! defined( 'CZR_OPT_PREFIX' ) )           define( 'CZR_OPT_PREFIX' , apply_filters( 'czr_options_prefixes', 'tc_' ) );
      //MAIN OPTIONS NAME
      if( ! defined( 'CZR_THEME_OPTIONS' ) )        define( 'CZR_THEME_OPTIONS', apply_filters( 'czr_options_name', 'tc_theme_options' ) );

      //this is the structure of the Customizr code : groups => ('path' , 'class_suffix')
      $this -> tc_core = apply_filters( 'tc_core',
        array(
            'fire'      =>   array(
              array('inc' , 'init'),//defines default values (layout, socials, default slider...) and theme supports (after_setup_theme)
              array('inc' , 'plugins_compat'),//handles various plugins compatibilty (Jetpack, Bbpress, Qtranslate, Woocommerce, The Event Calendar ...)
              array('inc' , 'utils_settings_map'),//customizer setting map
              array('inc' , 'utils'),//helpers used everywhere
              array('inc' , 'init_retro_compat'),
              array('inc' , 'resources'),//loads front stylesheets (skins) and javascripts
              array('inc' , 'widgets'),//widget factory
              array('inc' , 'placeholders'),//front end placeholders ajax actions for widgets, menus.... Must be fired if is_admin === true to allow ajax actions.
              array('inc/admin' , 'admin_init'),//loads admin style and javascript ressources. Handles various pure admin actions (no customizer actions)
              array('inc/admin' , 'admin_page')//creates the welcome/help panel including changelog and system config
            ),
            'admin'     => array(
              array('inc/admin' , 'customize'),//loads customizer actions and resources
              array('inc/admin' , 'meta_boxes')//loads the meta boxes for pages, posts and attachment : slider and layout settings
            ),
            //the following files/classes define the action hooks for front end rendering : header, main content, footer
            'header'    =>   array(
              array('inc/parts' , 'header_main'),
              array('inc/parts' , 'menu'),
              array('inc/parts' , 'nav_walker')
            ),
            'content'   =>  array(
              array('inc/parts', '404'),
              array('inc/parts', 'attachment'),
              array('inc/parts', 'breadcrumb'),
              array('inc/parts', 'comments'),
              array('inc/parts', 'featured_pages'),
              array('inc/parts', 'gallery'),
              array('inc/parts', 'headings'),
              array('inc/parts', 'no_results'),
              array('inc/parts', 'page'),
              array('inc/parts', 'post_thumbnails'),
              array('inc/parts', 'post'),
              array('inc/parts', 'post_list'),
              array('inc/parts', 'post_list_grid'),
              array('inc/parts', 'post_metas'),
              array('inc/parts', 'post_navigation'),
              array('inc/parts', 'sidebar'),
              array('inc/parts', 'slider')
            ),
            'footer'    => array(
              array('inc/parts', 'footer_main'),
            ),
            'addons'    => apply_filters( 'tc_addons_classes' , array() )
        )//end of array
      );//end of filter

      self::$tc_option_group = 'tc_theme_options';

      //set files to load according to the context : admin / front / customize
      add_filter( 'tc_get_files_to_load' , array( $this , 'czr_fn_set_files_to_load' ) );

      //theme class groups instanciation
      //$this -> czr_fn__();
      add_action('czr_load', array( $this, 'czr_fn__') );
    }//end of __construct()



    /**
    * Class instanciation using a singleton factory :
    * Can be called to instantiate a specific class or group of classes
    * @param  array(). Ex : array ('admin' => array( array( 'inc/admin' , 'meta_boxes') ) )
    * @return  instances array()
    *
    * Thanks to Ben Doherty (https://github.com/bendoh) for the great programming approach
    *
    * @since Customizr 3.0
    */
    function czr_fn__( $_to_load = array(), $_no_filter = false ) {
      static $instances;
      //do we apply a filter ? optional boolean can force no filter
      $_to_load = $_no_filter ? $_to_load : apply_filters( 'tc_get_files_to_load' , $_to_load );

      if ( empty($_to_load) )
        return;

      foreach ( $_to_load as $group => $files ) {
        foreach ($files as $path_suffix ) {
          //checks if a child theme is used and if the required file has to be overriden
          // if ( $this -> czr_fn_is_child() && file_exists( TC_BASE_CHILD . $path_suffix[0] . '/class-' . $group . '-' .$path_suffix[1] .'.php') ) {
          //     require_once ( TC_BASE_CHILD . $path_suffix[0] . '/class-' . $group . '-' .$path_suffix[1] .'.php') ;
          // }
          // else {
          //     require_once ( TC_BASE . $path_suffix[0] . '/class-' . $group . '-' .$path_suffix[1] .'.php') ;
          // }

          $classname = 'CZR_' . $path_suffix[1];
          if( ! isset( $instances[ $classname ] ) )  {
            //check if the classname can be instantiated here
            if ( in_array( $classname, apply_filters( 'tc_dont_instantiate_in_init', array( 'CZR_nav_walker') ) ) )
              continue;
            //instantiates
            $instances[ $classname ] = class_exists($classname)  ? new $classname : '';
          }
        }
      }
      return $instances[ $classname ];
    }





    /***************************
    * HELPERS
    ****************************/
    function czr_fn_req_once( $file_path ) {
        //checks if a child theme is used and if the required file has to be overriden
        if ( $this -> czr_fn_is_child() && file_exists( TC_BASE_CHILD . $file_path ) ) {
            require_once ( TC_BASE_CHILD . $file_path ) ;
        }
        else {
            require_once ( TC_BASE . $file_path ) ;
        }
    }



    /**
    * Check the context and return the modified array of class files to load and instantiate
    * hook : tc_get_files_to_load
    * @return boolean
    *
    * @since  Customizr 3.3+
    */
    function czr_fn_set_files_to_load( $_to_load ) {
      $_to_load = empty($_to_load) ? $this -> tc_core : $_to_load;
      //Not customizing
      //1) IS NOT CUSTOMIZING : czr_fn_is_customize_left_panel() || czr_fn_is_customize_preview_frame() || czr_fn_doing_customizer_ajax()
      //---1.1) IS ADMIN
      //-------1.1.a) Doing AJAX
      //-------1.1.b) Not Doing AJAX
      //---1.2) IS NOT ADMIN
      //2) IS CUSTOMIZING
      //---2.1) IS LEFT PANEL => customizer controls
      //---2.2) IS RIGHT PANEL => preview
      if ( ! $this -> czr_fn_is_customizing() )
        {
          if ( is_admin() ) {
            //load
            $this -> czr_fn_req_once( 'inc/czr-admin.php' );

            //if doing ajax, we must not exclude the placeholders
            //because ajax actions are fired by admin_ajax.php where is_admin===true.
            if ( defined( 'DOING_AJAX' ) )
              $_to_load = $this -> czr_fn_unset_core_classes( $_to_load, array( 'header' , 'content' , 'footer' ), array( 'admin|inc/admin|customize' ) );
            else
              $_to_load = $this -> czr_fn_unset_core_classes( $_to_load, array( 'header' , 'content' , 'footer' ), array( 'admin|inc/admin|customize', 'fire|inc|placeholders' ) );
          }
          else {
            //load
            $this -> czr_fn_req_once( 'inc/czr-front.php' );

            //Skips all admin classes
            $_to_load = $this -> czr_fn_unset_core_classes( $_to_load, array( 'admin' ), array( 'fire|inc/admin|admin_init', 'fire|inc/admin|admin_page') );
          }
        }
      //Customizing
      else
        {
          //load
          $this -> czr_fn_req_once( 'inc/czr-admin.php' );
          $this -> czr_fn_req_once( 'inc/czr-customize.php' );

          //left panel => skip all front end classes
          if ( $this -> czr_fn_is_customize_left_panel() ) {
            $_to_load = $this -> czr_fn_unset_core_classes(
                $_to_load,
                array( 'header' , 'content' , 'footer' ),
                array( 'fire|inc|resources' , 'fire|inc/admin|admin_page' , 'admin|inc/admin|meta_boxes' )
            );
          }
          if ( $this -> czr_fn_is_customize_preview_frame() ) {
            //load
            $this -> czr_fn_req_once( 'inc/czr-front.php' );

            $_to_load = $this -> czr_fn_unset_core_classes(
              $_to_load,
              array(),
              array( 'fire|inc/admin|admin_init', 'fire|inc/admin|admin_page' , 'admin|inc/admin|meta_boxes' )
            );
          }
        }
      return $_to_load;
    }



    /**
    * Helper
    * Alters the original classes tree
    * @param $_groups array() list the group of classes to unset like header, content, admin
    * @param $_files array() list the single file to unset.
    * Specific syntax for single files: ex in fire|inc/admin|admin_page
    * => fire is the group, inc/admin is the path, admin_page is the file suffix.
    * => will unset inc/admin/class-fire-admin_page.php
    *
    * @return array() describing the files to load
    *
    * @since  Customizr 3.0.11
    */
    public function czr_fn_unset_core_classes( $_tree, $_groups = array(), $_files = array() ) {
      if ( empty($_tree) )
        return array();
      if ( ! empty($_groups) ) {
        foreach ( $_groups as $_group_to_remove ) {
          unset($_tree[$_group_to_remove]);
        }
      }
      if ( ! empty($_files) ) {
        foreach ( $_files as $_concat ) {
          //$_concat looks like : fire|inc|resources
          $_exploded = explode( '|', $_concat );
          //each single file entry must be a string like 'admin|inc/admin|customize'
          //=> when exploded by |, the array size must be 3 entries
          if ( count($_exploded) < 3 )
            continue;

          $gname = $_exploded[0];
          $_file_to_remove = $_exploded[2];
          if ( ! isset($_tree[$gname] ) )
            continue;
          foreach ( $_tree[$gname] as $_key => $path_suffix ) {
            if ( false !== strpos($path_suffix[1], $_file_to_remove ) )
              unset($_tree[$gname][$_key]);
          }//end foreach
        }//end foreach
      }//end if
      return $_tree;
    }//end of fn




    /**
    * Checks if we use a child theme. Uses a deprecated WP functions (get _theme_data) for versions <3.4
    * @return boolean
    *
    * @since  Customizr 3.0.11
    */
    function czr_fn_is_child() {
      // get themedata version wp 3.4+
      if ( function_exists( 'wp_get_theme' ) ) {
        //get WP_Theme object of customizr
        $tc_theme       = wp_get_theme();
        //define a boolean if using a child theme
        return $tc_theme -> parent() ? true : false;
      }
      else {
        $tc_theme       = call_user_func('get_' .'theme_data', get_stylesheet_directory().'/style.css' );
        return ! empty($tc_theme['Template']) ? true : false;
      }
    }


    /**
    * Are we in a customization context ? => ||
    * 1) Left panel ?
    * 2) Preview panel ?
    * 3) Ajax action from customizer ?
    * @return  bool
    * @since  3.2.9
    */
    function czr_fn_is_customizing() {
      //checks if is customizing : two contexts, admin and front (preview frame)
      return in_array( 1, array(
        $this -> czr_fn_is_customize_left_panel(),
        $this -> czr_fn_is_customize_preview_frame(),
        $this -> czr_fn_doing_customizer_ajax()
      ) );
    }


    /**
    * Is the customizer left panel being displayed ?
    * @return  boolean
    * @since  3.3+
    */
    function czr_fn_is_customize_left_panel() {
      global $pagenow;
      return is_admin() && isset( $pagenow ) && 'customize.php' == $pagenow;
    }


    /**
    * Is the customizer preview panel being displayed ?
    * @return  boolean
    * @since  3.3+
    */
    function czr_fn_is_customize_preview_frame() {
      return is_customize_preview() || ( ! is_admin() && isset($_REQUEST['customize_messenger_channel']) );
    }


    /**
    * Always include wp_customize or customized in the custom ajax action triggered from the customizer
    * => it will be detected here on server side
    * typical example : the donate button
    *
    * @return boolean
    * @since  3.3.2
    */
    function czr_fn_doing_customizer_ajax() {
      $_is_ajaxing_from_customizer = isset( $_POST['customized'] ) || isset( $_POST['wp_customize'] );
      return $_is_ajaxing_from_customizer && ( defined( 'DOING_AJAX' ) && DOING_AJAX );
    }


    /**
    * @return  boolean
    * @since  3.4+
    */
    static function czr_fn_is_pro() {
      //TC_BASE is the root server path of the parent theme
      if( ! defined( 'TC_BASE' ) )            define( 'TC_BASE' , get_template_directory().'/' );
      return class_exists( 'CZR_init_pro' ) && "customizr-pro" == self::$theme_name;
    }
  }//end of class
endif;

/* HELPERS */
//@return boolean
if ( ! function_exists( 'czr_fn_is_partial_refreshed_on' ) ) {
  function czr_fn_is_partial_refreshed_on() {
    return apply_filters( 'tc_partial_refresh_on', true );
  }
}
/* HELPER FOR CHECKBOX OPTIONS */
//used in the customizer
//replace wp checked() function
if ( ! function_exists( 'czr_fn_checked' ) ) {
  function czr_fn_checked( $val ) {
    echo $val ? 'checked="checked"' : '';
  }
}
/**
* helper
* @return  bool
*/
if ( ! function_exists( 'czr_fn_has_social_links' ) ) {
  function czr_fn_has_social_links() {
    $_socials = czr_fn_get_opt('tc_social_links');
    return ! empty( $_socials );
  }
}

/**
* helper
* Prints the social links
* @return  void
*/
if ( ! function_exists( 'czr_fn_print_social_links' ) ) {
  function czr_fn_print_social_links() {
    echo CZR_utils::$inst->czr_fn_get_social_networks();
  }
}

/**
* helper
* Renders the main header
* @return  void
*/
if ( ! function_exists( 'czr_fn_render_main_header' ) ) {
  function czr_fn_render_main_header() {
    CZR_header_main::$instance->czr_fn_set_header_options();
  ?>
    <header class="<?php echo implode( " ", apply_filters('tc_header_classes', array('tc-header' ,'clearfix', 'row-fluid') ) ) ?>" role="banner">
    <?php
      // The '__header' hook is used with the following callback functions (ordered by priorities) :
      //CZR_header_main::$instance->tc_logo_title_display(), CZR_header_main::$instance->czr_fn_tagline_display(), CZR_header_main::$instance->czr_fn_navbar_display()
      do_action( '__header' );
    ?>
    </header>
  <?php
  }
}
/**
* helper
* Renders or returns the filtered and escaped tagline
* @return  void
*/
if ( ! function_exists( 'czr_fn_get_tagline_text' ) ) {
  function czr_fn_get_tagline_text( $echo = true ) {
    $tagline_text = apply_filters( 'tc_tagline_text', esc_attr__( get_bloginfo( 'description' ) ) );
    if ( ! $echo )
      return $tagline_text;
    echo $tagline_text;
  }
}
?>