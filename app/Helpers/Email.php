<?php
namespace EasyCommerce\Helpers;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Traits\Cleaner;

/**
 * Email class to handle email template rendering and sending.
 */
class Email {

	use Cleaner;

	private $title = '';

	private $header = '';

	private $body = '';

	private $footer = '';

	private $subject = '';

	private $headers = array( 'Content-Type: text/html; charset=UTF-8' );

	private $attachments = array();

	private $recipients = array();

	private $placeholders = array();

	/**
	 * Sets the title of the email.
	 *
	 * @param string $title The title for the email.
	 */
	public function set_title( $title ) {
		$this->title = $title;
	}

	/**
	 * Sets the header of the email.
	 *
	 * @param string $header The HTML content for the email header section.
	 */
	public function set_header( $header ) {
		$this->header = $header;
	}

	/**
	 * Sets the body of the email.
	 *
	 * @param string $body The main content for the email body.
	 */
	public function set_body( $body ) {
		$this->body = $body;
	}

	/**
	 * Sets the footer of the email.
	 *
	 * @param string $footer The content for the email footer.
	 */
	public function set_footer( $footer ) {
		$this->footer = $footer;
	}

	/**
	 * Gets the header of the email.
	 *
	 * @return string $header The HTML content for the email header section.
	 */
	public function get_header() {
		return ! empty( $this->header ) ? $this->header : Utility::get_option( 'email', 'layout', 'header', '' );
	}

	/**
	 * Gets the body of the email.
	 *
	 * @return string $body The HTML content for the email body section.
	 */
	public function get_body() {
		return ! empty( $this->body ) ? $this->body : '';
	}

	/**
	 * Gets the footer of the email.
	 *
	 * @param string $footer The HTML content for the email footer section.
	 */
	public function get_footer() {
		return ! empty( $this->footer ) ? $this->footer : Utility::get_option( 'email', 'layout', 'footer', '' );
	}

	/**
	 * Sets the subject of the email.
	 *
	 * @param string $subject The subject of the email.
	 */
	public function set_subject( $subject ) {
		$this->subject = $subject;
	}

	/**
	 * Sets the recipient(s) of the email.
	 *
	 * @param mixed $recipients A single email address or an array of email addresses.
	 */
	public function set_recipient( $recipients ) {
		if ( is_array( $recipients ) ) {
			$this->recipients = $recipients;
		} else {
			$this->recipients = array( $recipients );
		}
	}

	/**
	 * Adds a recipient to the email.
	 *
	 * @param mixed $recipients A single email address or an array of email addresses.
	 */
	public function add_recipient( $recipients ) {
		if ( is_array( $recipients ) ) {
			$this->recipients = array_merge( $this->recipients, $recipients );
		} else {
			$this->recipients[] = $recipients;
		}
	}

	/**
	 * Adds a header to the email.
	 *
	 * @param string $header The header to add.
	 */
	public function add_header( $header ) {
		$this->headers[] = $header;
	}

	/**
	 * Adds an attachment to the email.
	 *
	 * @param string $attachment The file path of the attachment.
	 */
	public function add_attachment( $attachment ) {
		$this->attachments[] = $attachment;
	}

	/**
	 * Gets the subject of the email.
	 *
	 * @return string The email subject.
	 */
	public function get_subject() {
		return $this->apply_placeholders( $this->subject );
	}

	/**
	 * Gets the recipient(s) of the email.
	 *
	 * @return string A comma-separated string of valid recipients.
	 */
	public function get_recipient() {
		return implode( ',', array_filter( $this->recipients, 'is_email' ) );
	}

	/**
	 * Gets the headers for the email.
	 *
	 * @return array The headers for the email.
	 */
	public function get_headers() {
		return $this->headers;
	}

	/**
	 * Gets the attachments for the email.
	 *
	 * @return array The list of attachment file paths.
	 */
	public function get_attachments() {
		return $this->attachments;
	}

	/**
	 * Gets the email header content.
	 *
	 * @return string The header HTML content for the email.
	 */
	public function get_header_content() {

		$wrapper_bg = Utility::get_option( 'email', 'layout', 'wrapper_bg', '#f4f4f4' );
		$body_bg    = Utility::get_option( 'email', 'layout', 'body_bg', '#f4f4f4' );
		$width      = Utility::get_option( 'email', 'layout', 'width', '600' );

		ob_start();
		?>
		<table id="main-wrapper" style="width: 100%; margin: 0 auto; background-color: <?php echo esc_attr( $wrapper_bg ); ?>;">
			<tr>
				<td>
					<table id="email-body" style="margin: 0 auto; width: <?php echo esc_attr( $width ); ?>px; background-color: <?php echo esc_attr( $body_bg ); ?>; border-radius: 0px; margin-top: 30px; margin-bottom: 30px;">
						<tbody>
							<tr>
								<td style="margin: 0; padding: 0;">
									<div id="header">
										<?php
											echo wp_kses_post( wpautop( $this->get_header() ) );
										?>
									</div><!-- #header -->
		<?php
		return ob_get_clean();
	}

	/**
	 * Gets the email body content.
	 *
	 * @return string The HTML content for the email body.
	 */
	public function get_body_content() {
		ob_start();
		?>
									<div id="body" style="padding: 20px;">
										<?php echo wp_kses_post( wpautop( $this->get_body() ) ); ?>
									</div><!-- #body -->
		<?php
		return ob_get_clean();
	}

	/**
	 * Gets the email footer content.
	 *
	 * @return string The HTML content for the email footer.
	 */
	public function get_footer_content() {
		ob_start();
		?>
									<div id="footer">
										<?php
											echo wp_kses_post( wpautop( $this->get_footer() ) );
										?>
									</div><!-- #footer -->
								</td>
							</tr>
						</tbody>
					</table><!-- #email-body -->
				</td>
			</tr>
		</table><!-- #main-wrapper -->
		<?php
		return ob_get_clean();
	}

	/**
	 * Combines and returns the full email content.
	 *
	 * @return string The complete HTML content of the email.
	 */
	public function get_content() {
		$content = '';

		$content .= $this->get_header_content();
		$content .= $this->get_body_content();
		$content .= $this->get_footer_content();

		$content = $this->apply_placeholders( $content );

		return $content;
	}

	/**
	 * Loads and sets the email template.
	 *
	 * @param string $template_name The name of the template file (without extension).
	 * @param array  $args Optional. Associative array of variables to pass to the template.
	 */
	public function set_template( $template_name, $args = array() ) {
		$template_path = EASYCOMMERCE_PLUGIN_DIR . 'views/emails/' . $template_name . '.php';

		if ( file_exists( $template_path ) ) {
			if ( ! empty( $args ) && is_array( $args ) ) {
				foreach ( $args as $key => $value ) {
					${$key} = $value;
				}
			}

			ob_start();
			include $template_path;
			$template_content = ob_get_clean();

			$this->set_body( $template_content );
		}
	}

	/**
	 * Sets placeholders for the email content (title, body, footer, and subject).
	 *
	 * @param array $placeholders Associative array of placeholders and their values.
	 */
	public function set_placeholders( $placeholders = array() ) {

		$this->placeholders = array_merge(
			array(
				'##site_name##'      => get_bloginfo( 'name' ),
				'##shop_name##'      => Utility::get_option( 'general', 'business', 'store_name' ),
				'##year##'           => date_i18n( 'Y' ),
				'##shop_page##'      => easycommerce_shop_page( true ),
				'##checkout_page##'  => easycommerce_checkout_page( true ),
				'##dashboard_page##' => easycommerce_dashboard_page( true ),
			),
			$placeholders
		);
	}

	public function apply_placeholders( $content ) {
		$content = str_replace( array_keys( $this->placeholders ), array_values( $this->placeholders ), $content );

		return $content;
	}

	/**
	 * Sends the email.
	 *
	 * @param string|null $to The recipient email address.
	 * @param string      $subject The subject of the email.
	 * @param string      $body The body content of the email.
	 * @param string      $footer The footer content of the email.
	 * @return bool Whether the email was sent successfully.
	 */
	public function send( $to = null, $subject = '', $body = '', $footer = '' ) {
		if ( ! empty( $to ) ) {
			$this->set_recipient( $to );
		}

		if ( ! empty( $body ) ) {
			$this->set_body( $body );
		}

		if ( ! empty( $subject ) ) {
			$this->set_subject( $subject );
		}

		if ( ! empty( $footer ) ) {
			$this->set_footer( $footer );
		}

		if ( empty( $this->get_recipient() ) || empty( $this->get_content() ) || empty( $this->get_subject() ) ) {
			return false;
		}

		return wp_mail(
			$this->get_recipient(),
			$this->get_subject(),
			$this->get_content(),
			$this->get_headers(),
			$this->get_attachments()
		);
	}
}