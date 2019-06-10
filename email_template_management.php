<?php 
add_action( 'init', 'email_management_admin_init' );
add_action( 'admin_menu', 'email_settings_page_init' );
// 
function email_management_admin_init() {
    $settings = get_option( "email_management_settings_option" );
    if ( empty( $settings ) ) {
        
        $settings = array(
            'profile-updated-content' => '',
            'registration-verification-content' => '',
        );
        add_option( "email_management_settings_option", $settings, '', 'yes' );
    }
}
function email_settings_page_init() {
    $settings_page = add_menu_page("Email template management", "Email template management", "manage_options", "email-template-management", "email_settings_page", null, 28);
    add_action( "load-{$settings_page}", 'ilc_load_settings_page' );
}

function ilc_load_settings_page() {
    if ( isset($_POST["ilc-settings-submit"]) && $_POST["ilc-settings-submit"] == 'Y' ) {
        check_admin_referer( "email-settings-template" );
        ilc_save_theme_settings();
        $url_parameters = isset($_GET['tab'])? 'updated=true&tab='.$_GET['tab'] : 'updated=true';
        wp_redirect(admin_url('admin.php?page=email-template-management&'.$url_parameters));
        exit;
    }
}

function ilc_save_theme_settings() {
    global $pagenow;
    $settings = get_option( "email_management_settings_option" );
    $settings['custom_admin_email'] = $_POST['custom_admin_email']; 
   /* if(isset($_POST['test']) && $_POST['test'] == 'Test'){
        if ( isset ( $_GET['tab'] ) )
            $tab = $_GET['tab'];
             switch ( $tab ){ 
                case 'profile-updated-template' :
                    $emailBody = stripslashes(wp_kses_post($_POST['profile-updated-content']));
                break;
                 case 'registration-verification-template' :
                    $emailBody = stripslashes(wp_kses_post($_POST['registration-verification-content']));
                break;
            }
           //Admin_test_email($settings['custom_admin_email'], $emailBody);
    }*/

    if ( $pagenow == 'admin.php' && $_GET['page'] == 'email-template-management' ){ 
        if ( isset ( $_GET['tab'] ) )
            $tab = $_GET['tab']; 
        else
            $tab = 'profile-updated-template'; 

        switch ( $tab ){ 
            case 'profile-updated-template' :
                $settings['profile-updated-content'] = stripslashes(wp_kses_post($_POST['profile-updated-content']));
            break;
             case 'registration-verification-template' :
                $settings['registration-verification-content'] = stripslashes(wp_kses_post($_POST['registration-verification-content']));
            break;
        }
    }
    
    if( !current_user_can( 'unfiltered_html' ) ){
        if ( $settings['profile-updated-content']  )
            $settings['profile-updated-content'] = stripslashes( esc_textarea( wp_filter_post_kses( $settings['profile-updated-content'] ) ) );
        if ( $settings['registration-verification-content']  )
            $settings['registration-verification-content'] = stripslashes( esc_textarea( wp_filter_post_kses( $settings['registration-verification-content'] ) ) );
    }
    $updated = update_option( "email_management_settings_option", $settings );
}

function ilc_admin_tabs( $current = 'profile-updated-template' ) { 
    $tabs = array( 
                    'profile-updated-template' => 'Profile Updated',
                    'registration-verification-template' => 'Registration Verification',
                 ); 
    echo '<div id="icon-themes" class="icon32"><br></div>';
    echo '<h2 class="nav-tab-wrapper">';
    foreach( $tabs as $tab => $name ){
        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
        echo "<a class='nav-tab$class' href='?page=email-template-management&tab=$tab'>$name</a>";
    }
    echo '</h2>';
}

function email_settings_page() {
    global $pagenow;
    $settings = get_option( "email_management_settings_option" );
    $content="";
     $editor_settings = array(
                            'editor_height' => '300px',
                            'quicktags' => array( 'buttons' => 'strong,em,del,ul,ol,li,close' ), // note that spaces in this list seem to cause an issue
                        );
    ?>
    <div class="wrap">
        <h2>Mail Template Management</h2>
        <?php
            if (isset( $_GET['updated'] ) && 'true' == esc_attr( $_GET['updated']) ) echo '<div class="updated" ><p>Mail template is updated.</p></div>';
            if ( isset ( $_GET['tab'] ) ) ilc_admin_tabs($_GET['tab']); else ilc_admin_tabs('deposit-request-mail-template');
        ?>
        <div id="poststuff">
            <form method="post" action="<?php admin_url( 'admin.php?page=email-template-management' ); ?>"><?php
                wp_nonce_field( "email-settings-template" ); 
                $email_address = !empty($settings) ? $settings['custom_admin_email'] : '';
                echo '<table><tr>
                               <td width="168"><strong><label>Admin email address:</label></strong></td>
                                <td><input type="text" name="custom_admin_email" id="custom_admin_email" value="'.$email_address.'"/><input type="submit" class="button-primary" name="test" id="test" value="Test"/>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"><span class="description">All emails will be sent to the above mentioned admin email address. </span>
                            </td></tr>
                    </table></br>';
                if ( $pagenow == 'admin.php' && $_GET['page'] == 'email-template-management' ){ 
                    if ( isset ( $_GET['tab'] ) ) $tab = $_GET['tab']; 
                    else $tab = 'profile-updated-template'; 
                    echo '<table class="form-table">';
                  
                    switch ( $tab ){
                        case 'profile-updated-template' : 
                            ?>
                            <tr>
                               <span class="description" style="font-weight: 600;">The below added mail content will be sent to the client when he/she updates his/her profile request from the front end. If you do not add any content, then default mail will be sent to the client.</span>
                                 <td ><?php 
                                        if(!empty($settings) ){
                                            if(isset($settings['profile-updated-content']) && $settings['profile-updated-content'] != "")
                                              $content = $settings['profile-updated-content'];
                                            else
                                                $content = "";
                                        }
                                        wp_editor( $content, 'profile-updated-content', $editor_settings );?>
                                </td>
                            </tr>
                            <tr>
                                <td><span class="description ">Keywords:%YOUR-NAME%, %YOUR-EMAIL%, %DATE%, %TIME%, %IP%, %KYC-TIER%<br/> %YOUR-NAME% = Client's Name <br/> %YOUR-EMAIL% = Client's email address <br/> Use given keywords to display frontend form field values into the mail content.</span>
                                </td>
                            </tr>
                            <?php
                        break;
                        case 'registration-verification-template' : 
                            ?>
                            <tr>
                               <span class="description" style="font-weight: 600;">The below added mail content will be sent verification link to the client when he/she will register to ETHoutlet. If you do not add any content, then default mail will be sent to the client.</span>
                                 <td><?php
                                 if(!empty($settings) ){
                                            if(isset($settings['registration-verification-content']) && $settings['registration-verification-content'] != "")
                                              $content = $settings['registration-verification-content'];
                                            else
                                                $content = "";
                                        }
                                        wp_editor( $content, 'registration-verification-content', $editor_settings );?>
                                </td>
                            </tr>
                            <tr>
                                <td><span class="description ">Keywords:%YOUR-NAME%,%YOUR-EMAIL%,%VERIFY-LINK%,<br/> %YOUR-NAME% = Client's Name <br/> %YOUR-EMAIL% = Client's email address <br/>%VERIFY-LINK% = Dynamically generated link goes here <br/> Use given keywords to display frontend form field values into the mail content.</span>
                                </td>
                            </tr>
                            <?php
                        break;
                    }
                    echo '</table>';
                }
                ?>
                <p class="submit" style="clear: both;">
                    <input type="submit" name="Submit"  class="button-primary" value="Update Settings" />
                    <input type="hidden" name="ilc-settings-submit" value="Y" />
                </p>
            </form>
        </div>
    </div>
<?php }
