<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

if (! class_exists('bookingpress_fileupload_class') ) {
    class bookingpress_fileupload_class Extends BookingPress_Core
    {
        var $file;
        var $check_cap;
        var $capabilities;
        var $check_nonce;
        var $nonce_data;
        var $nonce_action;
        var $check_only_image;
        var $check_specific_ext;
        var $allowed_ext;
        var $invalid_ext;
        var $compression_ext;
        var $error_message;
        var $default_error_msg;
        var $check_file_size;
        var $file_size;
        var $max_file_size;
        var $field_error_msg;
        var $field_size_error_msg;
        var $copy_file;
        var $manage_junks;
        var $image_exts;

        
        /**
         * Load all variables from file object
         *
         * @param  mixed $file
         * @return void
         */
        function __construct( $file, $import = false )
        {
            global $BookingPress;
            if (empty($file) && ! $import ) {
                $this->error_message = esc_html__('Please select a file to process', 'bookingpress-appointment-booking');
                return false;
            }

            if( !empty( $file ) && !$import ){
                $file = ! empty($file) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $file) : array();
            }

            $this->file      = $file;
            if( !$import ){
                $this->file_size = $file['size'];
            }

            $this->copy_file = $import;

            $this->invalid_ext = apply_filters('bookingpress_restricted_file_ext', array( 'php', 'php3', 'php4', 'php5', 'py', 'pl', 'jsp', 'asp', 'cgi', 'exe', 'htaccess', 'html', 'htm' ));

            $this->compression_ext = apply_filters('bookingpress_exclude_file_check_ext', array( 'tar', 'zip', 'gz', 'gzip', 'rar', '7z' ));

            $mimes = get_allowed_mime_types();

            $type_img = array();

            foreach ( $mimes as $ext => $type ) {
                if (preg_match('/(image\/)/', $type) ) {
                    if (preg_match('/(\|)/', $ext) ) {
                        $type_imgs = explode('|', $ext);
                        $type_img  = array_merge($type_img, $type_imgs);
                    } else {
                        $type_img[] = $ext;
                    }
                }
            }

            $this->image_exts = $type_img;

            $this->capabilities = array( 'bookingpress' );
        }

        
        /**
         * Core function for upload file
         *
         * @param  mixed $destination
         * @return void
         */
        function bookingpress_process_upload( $destination ){
            $allow_file_upload = 1;
            $allow_file_upload = apply_filters('bookingpress_allow_file_uploads', $allow_file_upload);	
            if(empty($allow_file_upload)){
                return false;
            }
            if ($this->check_cap ) {
                $capabilities = $this->capabilities;

                if (! empty($capabilities) ) {
                    if (is_array($capabilities) ) {
                        $isFailed = false;
                        foreach ( $capabilities as $caps ) {
                            if (! current_user_can($caps) ) {
                                $isFailed            = true;
                                $this->error_message = esc_html__("Sorry, you don't have permission to perform this action.", 'bookingpress-appointment-booking');
                                break;
                            }
                        }

                        if ($isFailed ) {
                            return false;
                        }
                    } else {
                        if (! current_user_can($capabilities) ) {
                            $this->error_message = esc_html__("Sorry, you don't have permission to perform this action.", 'bookingpress-appointment-booking');
                        }
                    }
                } else {
                    $this->error_message = esc_html__("Sorry, you don't have permission to perform this action.", 'bookingpress-appointment-booking');
                    return false;
                }
            }

            if ($this->check_nonce ) {
                if (empty($this->nonce_data) || empty($this->nonce_action) ) {
                    $this->error_message = esc_html__('Sorry, Your request could not be processed due to security reasons.', 'bookingpress-appointment-booking');
                    return false;
                }

                if (! wp_verify_nonce($this->nonce_data, $this->nonce_action) ) {
                    $this->error_message = esc_html__('Sorry, Your request could not be processed due to security reasons.', 'bookingpress-appointment-booking');
                    return false;
                }
            }

            if( $this->copy_file ){
                $ext_data = explode( '.', sanitize_file_name( $this->file ) );
            } else {
                $ext_data = explode('.', sanitize_file_name( $this->file['name'] ) );
            }
            $ext      = end($ext_data);
            $ext      = strtolower($ext);

            if (in_array($ext, $this->invalid_ext) ) {
                $this->error_message = esc_html__('The file could not be uploaded due to security reasons.', 'bookingpress-appointment-booking');
                return false;
            }

            if ($this->check_only_image ) {
                if ( !$this->copy_file && !preg_match('/(image\/)/', $this->file['type']) ) {
                    $this->error_message = esc_html__('Please select image file only.', 'bookingpress-appointment-booking');
                    if (! empty($this->default_error_msg) ) {
                        $this->error_message = $this->default_error_msg;
                    }
                    return false;
                }

                if( $this->copy_file ){
                    if( ! in_array( $ext, $this->image_exts ) ){
                        $this->error_message = esc_html__( "Please select image file only.", "bookingpress-appointment-booking" );
                        if( !empty( $this->default_error_msg ) ){
                            $this->error_message = $this->default_error_msg;
                        }
                        return false;
                    }
                }
            }

            if ($this->check_specific_ext ) {
                if (empty($this->allowed_ext) ) {
                    $this->error_message = esc_html__('Please set extensions to validate file.', 'bookingpress-appointment-booking');
                    return false;
                }
                if (! in_array($ext, $this->allowed_ext) ) {
                    $this->error_message = esc_html__('Invalid file extension. Please select valid file', 'bookingpress-appointment-booking');
                    if (! empty($this->default_error_msg) ) {
                        $this->error_message = $this->default_error_msg;
                    }

                    if (! empty($this->field_error_msg) ) {
                        $this->error_message = $this->field_error_msg;
                    }

                    return false;
                }
            }

            if ($this->check_file_size ) {
                $size_in_bytes = $this->bookingpress_convert_to_bytes();
                if ($size_in_bytes < $this->file_size || $this->file_size == 0 ) {
                    $this->error_message = esc_html__('Invalid File Size.', 'bookingpress-appointment-booking');

                    if (! empty($this->field_size_error_msg) ) {
                        $this->error_message = $this->field_size_error_msg;
                    }
                    return false;
                }
            }

            if (! function_exists('WP_Filesystem') ) {
                include_once ABSPATH . 'wp-admin/includes/file.php';
            }

            WP_Filesystem();
            global $wp_filesystem;
            
            if( $this->copy_file ){
                if( filter_var( $this->file, FILTER_VALIDATE_URL ) ){
                    
                    $args = array(
                        'timeout' => 4500
                    );
                    $getFileContent= wp_remote_get( $this->file, $args );

                    if( !is_wp_error( $getFileContent ) ){
						if( !empty( $getFileContent['response']['code'] ) && '200' == $getFileContent['response']['code'] ){
							$file_content = wp_remote_retrieve_body( $getFileContent );	
						} else {
							$file_content  = $wp_filesystem->get_contents( $this->file );
							if( false == $file_content ){
                                $file_content = $this->retrieve_file_content_from_temporary_directory();
							}
						}
						
                    } else {
                        $this->file = str_replace('https', 'http', $this->file);
                        $getFileContent= wp_remote_get( $this->file, $args );
                        if( !is_wp_error( $getFileContent ) ){
                            if( !empty( $getFileContent['response']['code'] ) && '200' == $getFileContent['response']['code'] ){
                                $file_content = wp_remote_retrieve_body( $getFileContent );	
                            } else {
                                $file_content  = $wp_filesystem->get_contents( $this->file );
                                if( false == $file_content ){
                                    $file_content = $this->retrieve_file_content_from_temporary_directory();
                                }
                            }
                        }else{
                            $file_content  = $wp_filesystem->get_contents( $this->file );
							if( false == $file_content ){
                                $file_content = $this->retrieve_file_content_from_temporary_directory();
							}
                        }
                    }
                } else {
                    $file_content  = $wp_filesystem->get_contents( $this->file );
                }
            } else {
                $file_content  = $wp_filesystem->get_contents($this->file['tmp_name']);

                $bpa_wp_filetype_check   = wp_check_filetype_and_ext( $this->file['tmp_name'], $this->file['name'] );

                $ext             = empty( $bpa_wp_filetype_check['ext'] ) ? '' : $bpa_wp_filetype_check['ext'];
                $type            = empty( $bpa_wp_filetype_check['type'] ) ? '' : $bpa_wp_filetype_check['type'];

                if( ( ( ! $type || ! $ext ) && !current_user_can( 'unfiltered_upload' ) ) ){
                    $this->error_message = esc_html__('The file could not be uploaded due to security reason as it contains malicious code.', 'bookingpress-appointment-booking');
                    return false;
                }

            }
            $is_valid_file = $this->bookingpress_read_file($file_content, $ext);

            if (! $is_valid_file ) {
                return false;
            }

            if ('' == $file_content || ! $wp_filesystem->put_contents($destination, $file_content, 0777) ) {
                $this->error_message = esc_html__('There is an issue while uploading a file. Please try again', 'bookingpress-appointment-booking');
                return false;
            }

            $junk_files = array();
            if( $this->manage_junks ){
                $junk_files[] = current_time( 'timestamp' ) . '<|>' . $destination;
            }

            if( $this->manage_junks && !empty( $junk_files ) ){
                $bpa_remove_junk_files = wp_json_encode( $junk_files );
                $bpa_opt_val = get_option('bpa_remove_junk_files');
                $bpa_opt_val = json_decode( $bpa_opt_val, true );
                if ( empty($bpa_opt_val) ) {
                    update_option('bpa_remove_junk_files', $bpa_remove_junk_files);
                } else {
                    $bpa_opt_val = array_merge($junk_files, $bpa_opt_val);
                    $bpa_update_opt_val = wp_json_encode( $bpa_opt_val );
                    update_option('bpa_remove_junk_files', $bpa_update_opt_val);
                }
            }

            return 1;
        }

        function retrieve_file_content_from_temporary_directory(){
            global $wp_filesystem;
            $file_name = BOOKINGPRESS_TMP_IMAGES_DIR .'/'. basename( $this->file );
            $file_content = false;
            if( file_exists( $file_name ) ){
                $file_content = $wp_filesystem->get_contents( $file_name );
            }
            return $file_content;
        }
        
        /**
         * Core function for read specific file content
         *
         * @param  mixed $file_content
         * @param  mixed $ext
         * @return void
         */
        function bookingpress_read_file( $file_content, $ext )
        {
            if ('' == $file_content ) {
                return true;
            }

            if (in_array($ext, $this->compression_ext) ) {
                return true;
            }

            $file_bytes = $this->file_size;

            $file_size = number_format($file_bytes / 1048576, 2);

            if ($file_size > 10 ) {
                return true;
            }

            $bookingpress_valid_pattern = '/(\<\?(php)|\<\?\=)|(\<(script|iframe))/';

            if ( preg_match( $bookingpress_valid_pattern, $file_content ) ) {
                $this->error_message = esc_html__('The file could not be uploaded due to security reason as it contains malicious code.', 'bookingpress-appointment-booking');
                return false;
            }

            $is_short_tag_enabled = ini_get( 'short_open_tag' );

            if( (1 == $is_short_tag_enabled || true == $is_short_tag_enabled) && ( preg_match( '/\<\?(.*?)\?\>/', $file_content ) || preg_match( '/\<\?(.*?)$/', $file_content ) ) ){
                $this->error_message = esc_html__('The file could not be uploaded due to security reason as it contains malicious code', 'bookingpress-appointment-booking');
                return false;
            }

            $bpa_invalid_pattern_array = array( 'onblur', 'onchange', 'onclick', 'oncontextmenu', 'oncopy', 'oncut', 'ondblclick', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onfocus', 'oninput', 'oninvalid', 'onkeydown', 'onkeypress', 'onkeyup', 'onmousedown', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onpaste', 'onscroll', 'onselect', 'onwheel', 'javascript' );

            $block_invalid_keyword = false;
            foreach( $bpa_invalid_pattern_array as $invalid_keyword ){
                if( false !== stripos( $file_content, $invalid_keyword ) ){
                    $block_invalid_keyword = true;
                    break;
                }
            }

            if( true == $block_invalid_keyword ){
                $this->error_message = esc_html__('The file could not be uploaded due to security reason as it contains malicious code', 'bookingpress-appointment-booking');
                return false;
            }

            return true;
        }

        function bookingpress_convert_to_bytes()
        {
            $units_arr = array(
            'B'  => 0,
            'K'  => 1,
            'KB' => 1,
            'M'  => 2,
            'MB' => 2,
            'G'  => 3,
            'GB' => 3,
            'T'  => 4,
            'TB' => 4,
            'P'  => 5,
            'PB' => 5,
            );

            $numbers = preg_replace('/[^\d.]/', '', $this->max_file_size);
            $suffix  = preg_replace('/[\d.]+/', '', $this->max_file_size);
            if (is_numeric(substr($suffix, 0, 1)) ) {
                return preg_replace('/[^\d.]/', '', $this->max_file_size);
            }
            $exponent = ! empty($units_arr[ $suffix ]) ? $units_arr[ $suffix ] : null;
            if (null == $exponent ) {
                return null;
            }
            return $numbers * ( 1024 ** $exponent );
        }
    }
}
