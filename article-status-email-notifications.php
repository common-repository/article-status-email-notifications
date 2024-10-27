<?php
/**
 * @package Article-Status-Email-Notifications
 * @version 1.0
 */
/*
Plugin Name: Article Status Email Notifications
Plugin URI: https://github.com/JavaDevVictoria/Article-Status-Email-Notifications
Description: Send email notifications to the admin whenever a new article is submitted for review by a contributor and whenever a draft article is saved or an article is published
Author: Victoria Holland
Version: 1.0
Author URI: http://victoria-holland.info
*/



if ( is_admin() ){
add_action( 'admin_menu', 'article_status_email_notification_menu' );
}

function article_status_email_notification_menu() {
	add_options_page( 'Article Status Email Notifications Options', 'Article Status Email Notifications', 'manage_options', 'article-status-email-notifications-settings', 'article_status_email_notification_options' );
	add_action( 'admin_init', 'register_article_status_email_notifications_settings' );
}

function register_article_status_email_notifications_settings() {
	//register our settings
	register_setting( 'article-status-email-notification-group', 'article_status_email_notification_admin_email' );
}

function article_status_email_notification_options() {

?>
	<div class="wrap">
	<h2>Article Status Email Notifications</h2>
	<p>Who should receive an email notification for article status updates?</p>
	<form method="post" action="options.php">
		<?php settings_fields( 'article-status-email-notification-group' ); ?>
		<?php do_settings_sections( 'article-status-email-notification-group' ); ?>
		<table class="form-table">
			<tr valign="top">
        	<th scope="row">Email Address:</th>
        	<td><input type="text" name="article_status_email_notification_admin_email" class="regular-text" value="<?php echo get_option('article_status_email_notification_admin_email'); ?>" /></td>
        	</tr>
		</table>
		<?php submit_button(); ?>
	</form>
	</div>
<?php
}

add_action('transition_post_status','article_status_send_email', 10, 3 );
function article_status_send_email( $new_status, $old_status, $post ) {

// Notify Admin that a Contributor has written a post
if ($new_status == 'pending' && user_can($post->post_author, 'edit_posts') && !user_can($post->post_author, 'publish_posts')) {
	$article_status_email = get_option('article_status_email_notification_admin_email');
	$admins = (empty($article_status_email)) ? get_option('admin_email') : $article_status_email;
	$url = get_permalink($post->ID);
	$edit_link = get_edit_post_link($post->ID, '');
	$preview_link = get_permalink($post->ID) . '&preview=true';
	$username = get_userdata($post->post_author);
	$username_last_edit = get_the_modified_author($post->ID);
	$post_modified = $post->post_modified;
	$subject = 'New submission pending review: "' . $post->post_title . '"';
	$message = 'A new submission is pending review.';
	$message .= "\r\n\r\n";
	$message .= "Author: $username->user_login\r\n";
	$message .= "Title: $post->post_title\r\n";
	$message .= "Last Edited By: $username_last_edit\r\n";
	$message .= "Last Edited Date: $post->post_modified";
	$message .= "\r\n\r\n";
	$message .= "Edit the submission: $edit_link\r\n";
	$message .= "Preview it: $preview_link";
	$result = wp_mail($admins, $subject, $message);
	}
	
// Notify Admin that a new draft has been saved
else if ($new_status == 'draft') {
	$article_status_email = get_option('article_status_email_notification_admin_email');
	$admins = (empty($article_status_email)) ? get_option('admin_email') : $article_status_email;
	$url = get_permalink($post->ID);
	$edit_link = get_edit_post_link($post->ID, '');
	$preview_link = get_permalink($post->ID) . '&preview=true';
	$username = get_userdata($post->post_author);
	$username_last_edit = get_the_modified_author($post->ID);
	$post_modified = $post->post_modified;
	$subject = 'New draft article has been saved: "' . $post->post_title . '"';
	$message = 'A new draft article has been saved.';
	$message .= "\r\n\r\n";
	$message .= "Author: $username->user_login\r\n";
	$message .= "Title: $post->post_title\r\n";
	$message .= "Last Edited By: $username_last_edit\r\n";
	$message .= "Last Edited Date: $post->post_modified";
	$message .= "\r\n\r\n";
	$message .= "Edit the draft: $edit_link\r\n";
	$message .= "Preview it: $preview_link";
	$result = wp_mail($admins, $subject, $message);
	}
	
// Notify Admin that a new post has been published
else if ($new_status == 'publish') {
	$article_status_email = get_option('article_status_email_notification_admin_email');
	$admins = (empty($article_status_email)) ? get_option('admin_email') : $article_status_email;
	$url = get_permalink($post->ID);
	$edit_link = get_edit_post_link($post->ID, '');
	$preview_link = get_permalink($post->ID) . '&preview=true';
	$username = get_userdata($post->post_author);
	$username_last_edit = get_the_modified_author($post->ID);
	$post_modified = $post->post_modified;
	$subject = 'New article has been published: "' . $post->post_title . '"';
	$message = 'A new article has been published.';
	$message .= "\r\n\r\n";
	$message .= "Author: $username->user_login\r\n";
	$message .= "Title: $post->post_title\r\n";
	$message .= "Last Edited By: $username_last_edit\r\n";
	$message .= "Last Edited Date: $post->post_modified";
	$message .= "\r\n\r\n";
	$message .= "Edit the article: $edit_link\r\n";
	$message .= "View it: $url";
	$result = wp_mail($admins, $subject, $message);
	}

// Notify the Contributor that the Admin has published their post

else if ($old_status == 'pending' && $new_status == 'publish' && user_can($post->post_author, 'edit_posts') && !user_can($post->post_author, 'publish_posts')) {
    $username = get_userdata($post->post_author);
    $url = get_permalink($post->ID);
	$subject = "Your submission is now live:" . " " . $post->post_title;
	$message = '"' . $post->post_title . '"' . " was just published!. \r\n";
	$message .= $url;
	$result = wp_mail($username->user_email, $subject, $message);
	}
}

?>
