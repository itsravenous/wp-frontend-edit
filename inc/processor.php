<?php
	require_once(ABSPATH.'wp-admin/includes/file.php');

	Class FrontEndEditProcessor {

		public $post;

		/**
		 * Updates the current post with (sanitized) user submitted data
		 * @param {array} new post data
		 */
		public function process_submission($data)
		{
			$post = $this->post;
			$useredit = isset($data['fee_submit']);

			if ($useredit)
			{
				$user = wp_get_current_user();
				$has_permission = ($user->ID && $user->ID == $post->post_author);
				if ($has_permission)
				{
					// Upload image and add to media library
					if ($this->image_received())
					{
						$proceed = $this->upload_image();
					}
					else
					{
						$proceed = TRUE;
					}
					if ($proceed)
					{
						// Get new content
						$new_content = $data['fee_post_content'];
						// Use HTML purifier instance for XSS-prevention
						$config = HTMLPurifier_Config::createDefault();
						$config->set('HTML.Allowed', 'p,b,a[href],i');
						$purifier = new HTMLPurifier($config);
						$new_content = $purifier->purify($new_content);

						// Create data array
						$post_data = array(
							'ID' => $post->ID,
							'post_content' => $new_content
						);
						// Update post
						$result = wp_update_post($post_data, TRUE);

						if (!isset($result->errors))
						{
							header('Location: '.get_site_url().'?p='.$post->ID.'&feesave=1');
						}
						else
						{
							global $feep_errors;
							$feep_errors = $result->errors;
						}
					}
				}
				else
				{
					header('HTTP/1.0 403 Forbidden');
					die('Permission denied');
				}
			}
		}

		private function image_received()
		{
			return isset($_FILES['fee_post_img']) && !empty($_FILES['fee_post_img']['name']);
		}

		/**
		 * Uploads the image chosen in the front end form
		 */
		private function upload_image()
		{
			// Have WP upload the file for us
			$upload_result = wp_handle_upload($_FILES['fee_post_img'], array('test_form' => FALSE));
			$upload_error = $upload_result['error'];
			if ($upload_error)
			{
				global $feep_errors;
				$feep_errors = array(
					'upload_error' => array('We had trouble uploading the file you chose. Please make sure it\'s less than 2MB and is a JPG or PNG image')
				);
				return FALSE;
			}
			else
			{
				// Get full filename including absolute path
				$filename = $upload_result['file'];
				// Get file type
				$wp_filetype = wp_check_filetype(basename($filename), null );
				// Get current upload dir
				$wp_upload_dir = wp_upload_dir();
				// Create attachment (media library item)
				$attachment = array(
					'guid' => $wp_upload_dir['url'] . '/' . basename( $filename ), 
					'post_mime_type' => $wp_filetype['type'],
					'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
					'post_content' => '',
					'post_status' => 'inherit'
				);
				$attach_id = wp_insert_attachment( $attachment, $filename, $this->post->ID );

				if ($attach_id)
				{
					// Include WP image file to provide wp_generate_attachment_metadata()
					require_once( ABSPATH . 'wp-admin/includes/image.php' );
					// Generate metadata (width/height etc) and also create any thumbnails/crops
					$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
					if ($attach_data)
					{
						if (wp_update_attachment_metadata( $attach_id, $attach_data ))
						{
							// Add image as featured image for the current post
							return update_post_meta($this->post->ID, '_thumbnail_id', $attach_id);	
						}
						else
						{
							return FALSE;
						}
					}
					else
					{
						return FALSE;
					}
				}
				else
				{
					return FALSE;
				}
			}
			
		}

	}
?>