<?php
/*
Plugin Name: Schmap Widget 
Plugin URI: http://www.schmap.com/
Description: A widget for you to add the sports, guides, etc. geospacial content to the sidebar and your blogs.
Author: Schmap Inc.
Version: 1.0
Author URI: http://www.schmap.com/
*/

/*
    Changelog
    1.0     Initial version
*/

// --------------------------------------------------------------------------------
    function schmapplet_act_header() {
    }

// --------------------------------------------------------------------------------
    function schmapplet_flt_content($content) {

        global $table_prefix, $wpdb;
        global $userdata;
        get_currentuserinfo();
        $wpschmapItems = $wpdb->get_results("SELECT widget_name, widget_html FROM " . $table_prefix . "schmap_widget ORDER BY createdtime DESC");
        foreach ($wpschmapItems as $wpschmapItems) {
            $content = preg_replace("/\[" . $wpschmapItems->widget_name . "(.*?)\]/is", $wpschmapItems->widget_html, $content);
        }
                
        return $content;
    }
// --------------------------------------------------------------------------------
    function schmapplet_options_page($content) {
        
        global $table_prefix, $wpdb;
       $preserve_old = 0;

// global $userdata;
// get_currentuserinfo();
// echo "<Script language='javascript'>alert('" . $userdata->user_login . "');</Script>";

        if ( isset($_POST['save']) ) {
            if ($_POST['schmapplet-name'] == "") {            
                ?>
                    <div class="fade updated" id="message">
                        <p><?php echo 'You must specify a tag name before saving!'; ?></p>
                    </div>
                <?php   
                $preserve_old = 1;
            } else {
                $sname = mysql_real_escape_string(str_replace("'", "\"",  $_POST['schmapplet-name']));
                $sname = "schmap_" . stripslashes($sname);
                $ssrc = mysql_real_escape_string(str_replace("'", "\"", $_POST['schmapplet-iframe-code']));
                $ssrc = stripslashes($ssrc);

                $count = $wpdb->get_results("SELECT count(*) AS cnt FROM " . $table_prefix . "schmap_widget WHERE widget_name='" . $sname . "';");
                foreach ($count as $count) {
                    $cnt = $count->cnt;
                }

                if ($cnt == 0) {
                    global $userdata;
                    get_currentuserinfo();
                    $sql = "INSERT INTO " . $table_prefix . "schmap_widget (widget_name, widget_html,authorid) VALUES (" .
                        "'" . $sname . "'" .
                        ", '" . $ssrc . "','" . $userdata->user_login ."')";
                    $wpdb->query($sql);
                } else {
                    ?>
                        <div class="fade updated" id="message">
                            <p><?php echo 'A same tag name is already defined. You must specify a different tag name.'; ?></p>
                        </div>
                    <?php   
                    $preserve_old = 1;
/*
                    $sql = "UPDATE " . $table_prefix . "schmap_widget SET " .
                        "widget_html='" . $ssrc . "'" .
                        " WHERE widget_name='" . $sname . "';";                 
*/
                }
            }       
        }



        if ( isset($_POST['delete']) ) {
            $sql = "DELETE FROM ". $table_prefix . "schmap_widget WHERE widget_id=" . $_POST['wpschmap_id'] . ";";
            $wpdb->query($sql);
        }

        if ( isset($_POST['update']) ) {
            $sname = mysql_real_escape_string(str_replace("'", "\"", $_POST['wpschmap_name']));
            $sname = stripslashes($sname);
            $ssrc = mysql_real_escape_string(str_replace("'", "\"", $_POST['wpschmap_code']));
            $ssrc = stripslashes($ssrc);


            $count = $wpdb->get_results("SELECT count(*) AS cnt FROM " . $table_prefix . "schmap_widget WHERE widget_name='" . $sname . "' AND widget_id<>" . $_POST['wpschmap_id'] . ";");
            foreach ($count as $count) {
                $cnt = $count->cnt;
            }

            if ($cnt == 0) {
                global $userdata;
                get_currentuserinfo();

                $sql = "UPDATE " . $table_prefix . "schmap_widget SET " .
                    "widget_name='" . $sname . "'" .
                    ", widget_html='" . $ssrc . "'" .
                    " WHERE widget_id=" . $_POST['wpschmap_id'] . ";";
                $wpdb->query($sql);

            } else {
                ?>
                    <div class="fade updated" id="message">
                        <p><?php echo 'A same tag name is already defined. You must specify a different tag name.'; ?></p>
                    </div>
                <?php
            }
        }


        ?>
        <div class="wrap">
        <h1>Schmap Widget</h1>
        <br />
        <h2>HTML Code</h2>
        <form action='' method='post' name='schmapplet-code' id='schmapplet-code'>
            <table>
                <tr>
                    <td>
                        <b>Paste code here:&nbsp;&nbsp;</b>
                        Get your preferred <a href="http://www.schmap.com/sports" target="_blank">Schmap Widget</a> and copy it back here
                    </td>
                </tr>
                <tr>
                    <td>
                        <textarea name='schmapplet-iframe-code' rows='5' cols='80'><?php if ($preserve_old) echo str_replace("\\'", "'", str_replace("\\\"", "\"", $_POST['schmapplet-iframe-code']));?></textarea>
                    </td>
                </tr>
            </table>
            <br/>
            <h2>Schmap Widget Tag Name in Blog and Sidebar</h2>
            <table>
                <tr>
                    <td><b>Input tag name:&nbsp;&nbsp; </b>schmap_</td>
                    <?php 
                        if ($preserve_old) 
                            echo "<td><input name='schmapplet-name' value='" .  $_POST['schmapplet-name'] . "' ></td>"; 
                        else
                            echo "<td><input name='schmapplet-name' value='' ></td>";
                    ?>
                </tr>
            </table>
            <br/>
            <table>
                <td class='submit'><input type='submit' name='save' value='Save &raquo;' /></td>
            </table>


            <br/>
            <br/>
            <h2>Edit Your Widgets</h2>
            <table>
                <tr>
                    <td><b>Widget Name</b></td>
                    <td><b>HTML Code</b></td>
                    <td>&nbsp;</td>
                </tr>
                
                <?php
                         global $userdata;
                         get_currentuserinfo();

                $wpschmapItems = $wpdb->get_results("SELECT widget_id, widget_name, widget_html FROM " . $table_prefix . "schmap_widget WHERE authorid='" . $userdata->user_login  . "' ORDER BY createdtime DESC");
                foreach ($wpschmapItems as $wpschmapItems) {
                    echo "<tr><form action='' method='post' id='schmap-wordpress-options'>";
                    
                    echo "<input type='hidden' name='wpschmap_id'  value='" . $wpschmapItems->widget_id . "'>";
                    echo "<td><input type='text' name='wpschmap_name' size='20' maxlength='64'  value='" . $wpschmapItems->widget_name . "' /></td>";
                    echo "<td><input type='text' name='wpschmap_code' size='40' maxlength='640' value='" . $wpschmapItems->widget_html . "' /></td>";
                    echo "<td class='submit'><input type='submit' name='update' value='Update &raquo;' /></td>";
                    echo "<td class='submit'><input type='submit' name='delete' value='Delete &raquo;' /></td>";
        
                    echo "</form></tr>\n";
                }
                echo "</table>";
                ?>

            <br/>
            <br/>
            <h2>The Widgets Made By Others</h2>
            <table>
                <tr>
                    <td><b>Widget Name</b></td>
                    <td><b>HTML Code</b></td>
                    <td><b>Creator</b></td>
                </tr>
                
                <?php

                $wpschmapItems = $wpdb->get_results("SELECT widget_name, widget_html, authorid FROM " . $table_prefix . "schmap_widget WHERE authorid<>'" . $userdata->user_login  . "' ORDER BY createdtime DESC");
                foreach ($wpschmapItems as $wpschmapItems) {
                    echo "<tr><form action='' method='post' id='schmap-wordpress-options1'>";
                    
                    echo "<td><input type='text' name='wpschmap_name' size='20' maxlength='64'  value='" . $wpschmapItems->widget_name . "' disabled='true' /></td>";
                    echo "<td><input type='text' name='wpschmap_code' size='40' maxlength='640' value='" . $wpschmapItems->widget_html . "' disabled='true' /></td>";
                    echo "<td><input type='text' name='wpschmap_user' size='10' maxlength='64' value='" . $wpschmapItems->authorid . "' disabled='true' /></td>";
        
                    echo "</form></tr>\n";
                }
                echo "</table>";
                ?>
        </form>

<?PHP
        echo "</div>";

    }
// --------------------------------------------------------------------------------
    function schmapplet_act_pages($content) {
        add_options_page("Schmap Widget", "Schmap Widget", 6, "schmapplet-wordpress", "schmapplet_options_page");
    }
// --------------------------------------------------------------------------------
    function searchDir($ddir) {
        $handle=opendir($ddir);

        while ($file = readdir($handle)) {
            $bdir=$ddir."/".$file;
            if ($file<>'.' && $file<>'..' && filetype($bdir)=='dir') {
                $ret = searchDir($ddir."/".$file);
                if ($ret) return $ret;
            }
            elseif ($file == 'schmap-widget.php') {
                closedir($handle);
                return $ddir;
            }
        }
        closedir($handle);
        return '';
    }


    function schmapplet_install() {

        global $table_prefix, $wpdb;
        $init_option_name = "schmap_widget";

        if ($wpdb->get_var("show tables like '" . $table_prefix . "schmap_widget'") != $table_prefix . "schmap_widget") {
            $sql = "CREATE TABLE " . $table_prefix . "schmap_widget (
                    widget_id int(11) NOT NULL auto_increment,
                        widget_html text(3096) NOT NULL default '',
                    widget_name text(256) NOT NULL default '',
                                        authorid text(256) NOT NULL default '',
                                        createdtime timestamp default now() not null,
                    PRIMARY KEY  (widget_id)
            );";
            require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
            dbDelta($sql);

            // reset options anyway if DB is not there
            $options = array('wp-schmap-widget-content' => '', 'title' => __('Schmap Widget', 'wp-schmap-widget-title'));

            $options['wp-schmap-widget-content'] = '[' . $init_option_name . ']';
            $options['title'] = '';
            update_option('widget_schmap', $options);
        }

        $count = $wpdb->get_results("SELECT count(*) AS cnt FROM " . $table_prefix . "schmap_widget WHERE widget_name='" . $init_option_name . "';");
        foreach ($count as $count) {
            $cnt = $count->cnt;
        }

        if ($cnt == 0) {
            global $userdata;
            get_currentuserinfo();
            
            $path = $_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'] ;
            $path = str_replace("wp-admin/plugins.php", "", $path);
            $path = $path . "wp-content/plugins";

                
            $init_iframe_src = '';
            $path = searchDir($path);
             
            $file_handle = fopen($path .  "/init", "r");
            if ($file_handle && !feof($file_handle)) {
                $init_iframe_src = fgets($file_handle);
                $init_iframe_src = str_replace("'", '"', $init_iframe_src);
            }
            fclose($file_handle);
                

            if ($init_iframe_src) {
                $sql = "INSERT INTO " . $table_prefix . "schmap_widget (widget_name, widget_html,authorid) VALUES (" .
                          "'" . $init_option_name . "'" .
                          ", '" . $init_iframe_src . "','" . $userdata->user_login . "')";
                $wpdb->query($sql);
           }
        }


        $options = get_option('widget_schmap');

        if (!is_array($options)) {
            $options = array('wp-schmap-widget-content' => '', 'title' => __('Schmap Widget', 'wp-schmap-widget-title'));
            $options['wp-schmap-widget-content'] = '[' . $init_option_name . ']';
            $options['title'] = '';
            update_option('widget_schmap', $options);
        } 
    }

// --------------------------------------------------------------------------------
    function schmapplet_button() {
      // Add javascript to the following pages
      if (strpos($_SERVER['REQUEST_URI'], 'post.php') ||
          strpos($_SERVER['REQUEST_URI'], 'post-new.php') ||
          strpos($_SERVER['REQUEST_URI'], 'page.php') ||
          strpos($_SERVER['REQUEST_URI'], 'page-new.php')) {
    
        // Print out HTML/Javascript to add the button
    ?>
     
    <div id="schmapplet_link" style="margin-bottom:10px; display:none;">
        <a href="#" onclick="return schmapplet_button('tinymce=true')">Get a Schmap Widget</a>
    </div>
    
    <script type="text/javascript">
    //<![CDATA[
    var schmap_toolbar = document.getElementById("ed_toolbar");
    
    if (schmap_toolbar) {
      var theButton = document.createElement('input');
      theButton.type = 'button';
      theButton.value = 'Get a Schmap Widget';
      theButton.onclick = schmapplet_button;
      theButton.className = 'schmap_wordpress_button';
      theButton.title = 'Get a Schmap Widget';
      theButton.id = 'schmap_wordpress';
      schmap_toolbar.appendChild(theButton);
    }
    
    function schmapplet_button(querystr) {
      // Change the URL to the actual page you wish to open if necessary
      // Optionally you can add width=x, height=x, to the variables to
      // open the window to an exact size
      myRef = window.open('http://www.schmap.com/sports','Schmapplet','toolbar=yes,location=yes,scrollbars=yes,resizable=yes');
      myRef.focus();
      return false;
    }
    
    //]]>
    </script>
    <?php
        }
    }

    if(!function_exists('get_schmap_widget_src')) {
        function get_schmap_widget_src() {
            $options = get_option('widget_schmap');
            $wp_schmap_widget_content = htmlspecialchars($options['wp-schmap-widget-content']);

            global $table_prefix, $wpdb;
            $echo_content = '';

            global $userdata;
            get_currentuserinfo();
            $wpschmapItems = $wpdb->get_results("SELECT widget_name, widget_html FROM " . $table_prefix . "schmap_widget ORDER BY createdtime DESC");
            foreach ($wpschmapItems as $wpschmapItems) {
                $name = $wpschmapItems->widget_name;
                if ($wp_schmap_widget_content == $name) {
                    $echo_content = $wpschmapItems->widget_html;
                    break;
                }
            }

            echo $echo_content;
        }
    }

    function widget_schmap_init() {
        if (!function_exists('register_sidebar_widget')) {
            return;
        }
    
        function widget_schmap($args) {
            extract($args);
            $options = get_option('widget_schmap');
            $title = htmlspecialchars($options['title']);
            $wp_schmap_widget_content = htmlspecialchars($options['wp-schmap-widget-content']);
//            echo $before_widget.$before_title.$title.$after_title;
//            echo "<h1>".$before_widget.$before_title.$title.$after_title."</h1>";
//                echo '<ul>'."\n";
                echo '<li id="schmapwidget" class="widget widget_schmap">';
                echo '<h2 class="widgettitle">' . $title . '</h2>';
                echo '<div id="schmap-widget">';

                get_schmap_widget_src();
                
                echo '</div></li>'."\n";
//                echo '</ul>'."\n";
//            echo $after_widget;
        }
    
        function widget_schmap_options() {
            $options = get_option('widget_schmap');

            if (!is_array($options)) {
                $options = array('wp-schmap-widget-content' => '', 'title' => __('Schmap Widget', 'wp-schmap-widget-title'));
            }

            if ($_POST['schmap-widget-submit']) {
                $options['wp-schmap-widget-content'] = $_POST['schmap-widget-content'];
                $options['title'] = strip_tags(stripslashes($_POST['schmap-widget-title']));
                update_option('widget_schmap', $options);
            }
            echo '<p style="text-align: left;"><label for="schmap-widget-title">'.__('Title', 'wp-schmap-widget-title').':</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" id="schmap-widget-title" name="schmap-widget-title" value="'.htmlspecialchars($options['title']).'" />';

            echo '<p style="text-align: left;"><label for="schmap-widget-content">'.__('Content', 'wp-schmap-widget-content').':</label>&nbsp;&nbsp;&nbsp;<select id="schmap-widget-content" name="schmap-widget-content" style="width:200px">';

   
            global $table_prefix, $wpdb;
            $wpschmapItems = $wpdb->get_results("SELECT widget_name FROM " . $table_prefix . "schmap_widget ORDER BY createdtime DESC");
            foreach ($wpschmapItems as $wpschmapItems) {
                $name = $wpschmapItems->widget_name;
                echo "<option value='" . $name . "'";
                if ($options['wp-schmap-widget-content'] == $name)
                {
                    echo " selected='selected'";
                }
                echo ">" . $name . "</option>";
            }
     

            echo '</select>';
//            echo '<p style="text-align: left;"><label for="schmap-widget-content">'.__('Content', 'wp-schmap-widget-content').':</label>&nbsp;&nbsp;&nbsp;<input type="text" id="schmap-widget-content" name="schmap-widget-content" value="'.htmlspecialchars($options['wp-schmap-widget-content']).'" />';

            echo '<input type="hidden" id="schmap-widget-submit" name="schmap-widget-submit" value="1" />'."\n";
        }
    
        // Register Widgets
        register_sidebar_widget('Schmap Widget', 'widget_schmap');
        register_widget_control('Schmap Widget', 'widget_schmap_options', 350, 120);
    }


    add_action('plugins_loaded', 'widget_schmap_init');
    add_action('activate_schmap-widget/schmap-widget.php', 'schmapplet_install');
    add_action('admin_menu', 'schmapplet_act_pages');
    add_action('wp_head', 'schmapplet_act_header');
    add_filter('the_content', 'schmapplet_flt_content');
    add_filter('admin_footer', 'schmapplet_button');
?>
