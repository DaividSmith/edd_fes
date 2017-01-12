<?php
/**
 * FES Emails
 *
 * This is where all emails in FES are
 * sent from. Soon to be replaced by the
 * Notifications API in 2.4
 *
 * @package FES
 * @subpackage Emails
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FES Emails.
 *
 * The email class in FES.
 *
 * @since 2.0.0
 * @access public
 */
class FES_Emails {
	/**
	 * Register post transition email.
	 *
	 * Registers a filter on transition post status.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return void
	 */
	function __construct() {
		add_action( 'transition_post_status', array( $this, 'post_status' ), 10, 3 );
	}

	/**
	 * Custom email meta values.
	 *
	 * Substitute placeholders for fields with
	 * the actual field values. Only works for fields
	 * that use the FES Fields API.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int $id ID of user or post being saved.
	 * @param int $user_id ID of user who performed action.
	 * @param string $type Type of form submitted (user,post or other).
	 * @param string $message Message being sent.
	 * @return string Message after substitutions.
	 */
	public function custom_meta_values( $id, $user_id, $type = "user", $message ){
		$form = '';

		if ( $type == 'user' ){

			foreach( EDD_FES()->load_forms as $template => $class ){

				$form = EDD_FES()->helper->get_form_by_name( $template, $id );

				if ( $form->type == 'user' ){

					foreach( $form->fields as $field ){

						if ( ! is_object( $field ) ) {
							continue;
						}

						$message = str_replace('{'. $field->name() .'}', $field->formatted_data( $user_id ), $message );
					}

				} else if ( $form->type == 'post' ) {
					continue;
				} else {
					continue;
				}

			}

		} else if ( $type == 'post' ){

			foreach( EDD_FES()->load_forms as $template => $class ){

				$form = EDD_FES()->helper->get_form_by_name( $template, $id );

				if ( $form->type == 'user' ){

					$form = EDD_FES()->helper->get_form_by_name( $template, $user_id );

					foreach( $form->fields as $field ) {

						if ( ! is_object( $field ) || is_wp_error( $field ) ) {
							continue;
						}

						if( is_wp_error( $field->formatted_data( $user_id ) ) ) {
							continue;
						}

						$message = str_replace('{'. $field->name() .'}', $field->formatted_data( $user_id ), $message );
					}

				} else if ( $form->type == 'post' ) {

					foreach( $form->fields as $field ){

						if ( ! is_object( $field ) || is_wp_error( $field ) ) {
							continue;
						}

						if( is_wp_error( $field->formatted_data( $user_id ) ) ) {
							continue;
						}

						$message = str_replace('{'. $field->name() .'}', $field->formatted_data( $user_id ), $message );
					}

				} else {
					continue;
				}

			}

		} else { // type == custom

		}

		return $message;
	}

	/**
	 * Built in FES email tags.
	 *
	 * Substitute placeholders for built in FES
	 * email placeholders. Guest user ids and
	 * unpublished post ids can be null or (int) -1.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int $id ID of user or post being saved.
	 * @param string $message Message being sent.
	 * @param string $type Type of form submitted (user,post or other).
	 * @return string Message after substitutions.
	 */
	function email_tags( $id = null, $message = " ", $type = "other" ){

		$has_tags = ( strpos( $message, '{' ) !== false );
		if ( ! $has_tags ){
			return $message;
		}

		// Some sort of email to do with users. Application received. Application approved. Etc.
		if ( $type === "user" ){
			$user = new WP_User( $id );
			$firstname = '';
			$lastname  = '';
			$fullname  = '';
			$username  = '';
			if ( !empty( $user->ID ) && $user->ID > 0 && !empty( $user->first_name ) ) {
				$user_data = get_userdata( $user->ID );
				$firstname = $user->first_name;
				$lastname  = $user->last_name;
				$fullname  = $user->first_name . ' ' . $user->last_name;
				$username  = $user_data->user_login;
			} else if ( !empty( $user->first_name ) ) {
				$firstname = $user->first_name;
				$lastname  = $user->last_name;
				$fullname  = $user->first_name . ' ' . $user->last_name;
				$username  = $user->first_name;
			} else if ( !empty( $user->user_email ) ) {
				$name      = $user->user_email;
				$firstname = $name;
				$lastname  = $name;
				$fullname  = $name;
				$username  = $name;
			}
			$message = str_replace( '{firstname}', $firstname, $message );
			$message = str_replace( '{lastname}', $lastname, $message );
			$message = str_replace( '{fullname}', $fullname, $message );
			$message = str_replace( '{username}', $username, $message );
			$message = str_replace( '{sitename}', get_bloginfo( 'name' ), $message );
			$message = EDD_FES()->emails->custom_meta_values( $id, $user->ID, $type, $message );
			/**
			 * User Email Tags.
			 *
			 * Substitute placeholders for custom values.
			 *
			 * @since 2.0.0
			 *
			 * @param  string $message Messsage being sent.
			 * @param  int $id ID of user being saved.
			 */
			return apply_filters( "fes_email_tags_user", $message, $id );
		}

		// Some sort of email to do with posts. Post submitted. Post approved. Etc.
		else if ( $type === "post" ){
			$post = get_post( $id );
			$user = new WP_User( $post->post_author);
			$firstname = '';
			$lastname  = '';
			$fullname  = '';
			$username  = '';
			if ( isset( $user->ID ) && $user->ID > 0 && isset( $user->first_name ) ) {
				$user_data = get_userdata( $user->ID );
				$firstname = $user->first_name;
				$lastname  = $user->last_name;
				$fullname  = $user->first_name . ' ' . $user->last_name;
				$username  = $user_data->user_login;
			} elseif ( isset( $user->first_name ) ) {
				$firstname = $user->first_name;
				$lastname  = $user->last_name;
				$fullname  = $user->first_name . ' ' . $user->last_name;
				$username  = $user->first_name;
			} else {
				$name      = $user->user_email;
				$firstname = $name;
				$lastname  = $name;
				$fullname  = $name;
				$username  = $name;
			}
			$message = str_replace( '{firstname}', $firstname, $message );
			$message = str_replace( '{lastname}', $lastname, $message );
			$message = str_replace( '{fullname}', $fullname, $message );
			$message = str_replace( '{username}', $username, $message );
			$message = str_replace( '{sitename}', get_bloginfo( 'name' ), $message );
			$message = str_replace( '{post-title}', $post->post_title, $message );
			$message = str_replace( '{post-content}', wp_strip_all_tags( $post->post_content ), $message );
			$message = str_replace( '{post-date}', $post->post_date, $message );
			$message = str_replace( '{post-excerpt}', wp_strip_all_tags( $post->post_excerpt ), $message );
			$message = str_replace( '{post-status}', $post->post_status, $message );

			$taglist = "";
			$posttags = get_the_terms( $post->ID, 'download_tag' );
			if ($posttags) {
				foreach($posttags as $tag) {
					$taglist .=  $tag->name . ', ';
				}
				$taglist = rtrim($taglist, ", ");
			}

			$message = str_replace( '{post-tags}', $taglist, $message );

			$catlist = "";
			$postcats = get_the_terms( $post->ID, 'download_category' );
			if ($postcats) {
				foreach($postcats as $cat) {
					$catlist .=  $cat->name . ', ';
				}
				$catlist = rtrim($catlist, ", ");
			}

			$message = str_replace( '{post-categories}', $catlist, $message );
			$message = str_replace( '{post-category}', $catlist, $message );
			$message = EDD_FES()->emails->custom_meta_values( $id, $user->ID,"post", $message );
			/**
			 * Post Email Tags.
			 *
			 * Substitute placeholders for custom values.
			 *
			 * @since 2.0.0
			 *
			 * @param  string $message Messsage being sent.
			 * @param  int $id ID of post being saved.
			 */
			return apply_filters( "fes_email_tags_post", $message, $id );
		} else {
			/**
			 * Other Email Tags.
			 *
			 * Substitute placeholders for custom values.
			 *
			 * @since 2.0.0
			 *
			 * @param  string $message Messsage being sent.
			 * @param  int $id Id of object being saved.
			 */
			return apply_filters( "fes_email_tags_other", $message, $id );
		}
	}

	// Devs: Please note, you should validate & sanitize all parameters before sending them in here.
	/**
	 * Send Email.
	 *
	 * Sends an FES email after attempting to substitute
	 * placeholders for values.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @todo  Validate passed in parameters.
	 *
	 * @param string $to Email address email is being sent to.
	 * @param string $from_name Name of sender.
	 * @param string $from_email Email address email is being sent from.
	 * @param string $subject Subject of email.
	 * @param string $message Message of email.
	 * @param string $type Type of form submitted (user,post or other).
	 * @param string $id Id of user/post being saved.
	 * @param string $args Custom arguments.
	 * @return void
	 */
	public function send_email( $to, $from_name, $from_email, $subject, $message, $type, $id, $args = array() ){

		if ( ! EDD_FES()->emails->should_send( $args ) ) {
			return false;
		}

		// start building the email
		$message_to_send = EDD_FES()->emails->email_tags( $id, $message, $type );
		$message_to_send = apply_filters('fes_send_mail_message', $message_to_send, $to, $from_name, $from_email, $subject, $message, $type, $id, $args );
		$emails = new EDD_Emails;
		$emails->from_name    = $from_name;
		$emails->from_address = $from_email;
		$emails->heading      = $subject;
		$emails->send( $to, $subject, $message_to_send );
	}

	/**
	 * Should Send Email.
	 *
	 * Checks to see if email shouldn't be sent based on
	 * permissions in settings panel.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @global $fes_settings FES settings to check for permissions settings against.
	 * @deprecated 2.4.0 Notifications API will replace this.
	 *
	 * @param string $args Custom arguments.
	 * @return bool Whether to send email or not.
	 */
	public function should_send( $args = array() ){
		$ret = true;
		global $fes_settings;
		if ( isset( $args['permissions'] ) ) {
			// See if there's a toggle for this email in the settings panel
			// If the toggle is enabled, we send
			$ret = isset( $fes_settings[ $args['permissions'] ] ) && '1' == $fes_settings[ $args['permissions'] ];
		}
		return (bool) $ret;
	}

	/**
	 * Post Status Transition Email.
	 *
	 * If the post status changes in the admin
	 * check to see if an email should be sent
	 * and if so, send it.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @global WP_Post $post Post object of current download.
	 * @deprecated 2.4.0 Notifications API will replace this.
	 *
	 * @param string $latest_status Old status.
	 * @param string $previous_status New status.
	 * @param WP_Post $post Post object of current download.
	 * @return void
	 */
	function post_status( $latest_status, $previous_status, $post ) {
		global $post;
		// Not an object if its not a draft yet. So prior to autosave this might throw warnings
		// We can prevent this by returning till it's been autosaved. This is when it becomes an obj.
		if ( !is_object( $post ) ) {
			return;
		}

		if ( $post->post_type !== 'download' ) {
			return;
		}

		if ( ! EDD_FES()->vendors->user_is_vendor( $post->post_author ) ) {
			return; // Do not send for non vendors
		}

		$user = new WP_User( $post->post_author );

		if ( $previous_status === 'pending' && $latest_status === 'trash' ) {
			/**
			 * Declined Submission Email To.
			 *
			 * Change the `to` field for the declined submission email.
			 *
			 * @since 2.0.0
			 *
			 * @param  string $email User email address email is going to.
			 * @param  WP_User $user User object of the download author.
			 */
			$to = apply_filters('fes_submission_declined_email_to',$user->user_email, $user);
			$from_name = edd_get_option( 'from_name', get_bloginfo( 'name' ) );
			$from_email = edd_get_option( 'from_email', get_bloginfo( 'admin_email' ) );
			/**
			 * Declined Submission Email Subject.
			 *
			 * Change the `subject` field for the declined submission email.
			 *
			 * @since 2.0.0
			 *
			 * @param  string $subject Subject for email.
			 */
			$subject = apply_filters( 'fes_submission_declined_message_subj', __( 'Submission Declined', 'edd_fes' ) );
			$message = EDD_FES()->helper->get_option( 'fes-vendor-submission-declined-email', '' );
			$type = "post";
			$id = $post->ID;
			$args['permissions'] = 'fes-vendor-submission-declined-email-toggle';
			EDD_FES()->emails->send_email( $to , $from_name, $from_email, $subject, $message, $type, $id, $args );
			return;
		}

		if ( $previous_status === 'publish' && $latest_status === 'trash' ) {
			/**
			 * Revoked Submission Email To.
			 *
			 * Change the `to` field for the revoked submission email.
			 *
			 * @since 2.0.0
			 *
			 * @param  string $email User email address email is going to.
			 * @param  WP_User $user User object of the download author.
			 */
			$to = apply_filters('fes_submission_revoked_email_to',$user->user_email, $user);
			$from_name = edd_get_option( 'from_name', get_bloginfo( 'name' ) );
			$from_email = edd_get_option( 'from_email', get_bloginfo( 'admin_email' ) );
			/**
			 * Revoked Submission Email Subject.
			 *
			 * Change the `subject` field for the revoked submission email.
			 *
			 * @since 2.0.0
			 *
			 * @param  string $subject Subject for email.
			 */
			$subject = apply_filters( 'fes_submission_revoked_message_subj', __( 'Submission Revoked', 'edd_fes' ) );
			$message = EDD_FES()->helper->get_option( 'fes-vendor-submission-revoked-email', '' );
			$type = "post";
			$id = $post->ID;
			$args['permissions'] = 'fes-vendor-submission-revoked-email-toggle';
			EDD_FES()->emails->send_email( $to , $from_name, $from_email, $subject, $message, $type, $id, $args );
			return;
		}
	}
}
