<?php
if ( $wps_nonce_valid ) {

	$wps_option_list = array(
		'wps_geoip',
		'wps_update_geoip',
		'wps_schedule_geoip',
		'wps_geoip_city',
		'wps_auto_pop',
		'wps_private_country_code',
		'wps_referrerspam',
		'wps_update_referrerspam',
		'wps_schedule_referrerspam'
	);

	// For country codes we always use upper case, otherwise default to 000 which is 'unknown'.
	if ( array_key_exists( 'wps_private_country_code', $_POST ) ) {
		$_POST['wps_private_country_code'] = trim( strtoupper( $_POST['wps_private_country_code'] ) );
	} else {
		$_POST['wps_private_country_code'] = '000';
	}

	if ( $_POST['wps_private_country_code'] == '' ) {
		$_POST['wps_private_country_code'] = '000';
	}

	foreach ( $wps_option_list as $option ) {
		$new_option = str_replace( "wps_", "", $option );
		if ( array_key_exists( $option, $_POST ) ) {
			$value = $_POST[ $option ];
		} else {
			$value = '';
		}
		$WP_Statistics->store_option( $new_option, $value );
	}

}

?>
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e( 'GeoIP settings', 'wp-statistics' ); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row" colspan="2">
				<?php echo sprintf(
					__( 'IP location services are provided by data created by %s.', 'wp-statistics' ),
					'<a href="http://www.maxmind.com" target=_blank>MaxMind</a>'
				); ?>
            </th>
        </tr>

		<?php
		if ( wp_statistics_geoip_supported() ) {
			?>
            <tr valign="top">
                <th scope="row">
                    <label for="geoip-enable"><?php _e( 'GeoIP collection:', 'wp-statistics' ); ?></label>
                </th>

                <td>
                    <input id="geoip-enable" type="checkbox" name="wps_geoip" <?php echo ($WP_Statistics->get_option( 'geoip' ) === 'on' ? "checked='checked'" : ''); ?>>
                    <label for="geoip-enable">
                        <?php _e( 'Enable', 'wp-statistics' ); ?>
                        <form action="" method="post" style="display: inline;">
                            <input type="hidden" name="geoip_name" value="country">
		                    <?php submit_button(__("Update Database", 'wp-statistics' ), "secondary", "update_geoip", false); ?>
                        </form>
                    </label>

                    <p class="description"><?php _e(
							'For getting more information and location (country) from visitor, enable this feature.',
							'wp-statistics'
						); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="geoip-city"><?php _e( 'GeoIP City:', 'wp-statistics' ); ?></label>
                </th>

                <td>
                    <input id="geoip-city" type="checkbox"
                           name="wps_geoip_city" <?php echo ($WP_Statistics->get_option( 'geoip_city' ) == 'on' ? "checked='checked'" : ''); ?>>
                    <label for="geoip-city">
                        <?php _e( 'Enable', 'wp-statistics' ); ?>
                        <form action="" method="post" style="display: inline;">
                            <input type="hidden" name="geoip_name" value="city">
		                    <?php submit_button(__("Update Database", 'wp-statistics' ), "secondary", "update_geoip", false); ?>
                        </form>
                    </label>

                    <p class="description"><?php _e(
                            'See Visitor\'s City Name',
                            'wp-statistics'
                        ); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="geoip-schedule"><?php _e( 'Schedule monthly update of GeoIP DB:', 'wp-statistics' ); ?></label>
                </th>

                <td>
                    <input id="geoip-schedule" type="checkbox"
                           name="wps_schedule_geoip" <?php echo $WP_Statistics->get_option( 'schedule_geoip' ) == true
						? "checked='checked'" : ''; ?>>
                    <label for="geoip-schedule"><?php _e( 'Enable', 'wp-statistics' ); ?></label>
					<?php
					if ( $WP_Statistics->get_option( 'schedule_geoip' ) ) {
						echo '<p class="description">' . __( 'Next update will be', 'wp-statistics' ) . ': <code>';
						$last_update = $WP_Statistics->get_option( 'last_geoip_dl' );
						$this_month  = strtotime( __( 'First Tuesday of this month', 'wp-statistics' ) );

						if ( $last_update > $this_month ) {
							$next_update = strtotime( __( 'First Tuesday of next month', 'wp-statistics' ) ) +
							               ( 86400 * 2 );
						} else {
							$next_update = $this_month + ( 86400 * 2 );
						}

						$next_schedule = wp_next_scheduled( 'wp_statistics_geoip_hook' );

						if ( $next_schedule ) {
							echo $WP_Statistics->Local_Date( get_option( 'date_format' ), $next_update ) .
							     ' @ ' .
							     $WP_Statistics->Local_Date( get_option( 'time_format' ), $next_schedule );
						} else {
							echo $WP_Statistics->Local_Date( get_option( 'date_format' ), $next_update ) .
							     ' @ ' .
							     $WP_Statistics->Local_Date( get_option( 'time_format' ), time() );
						}

						echo '</code></p>';
					}
					?>
                    <p class="description"><?php _e(
							'Download of the GeoIP database will be scheduled for 2 days after the first Tuesday of the month.',
							'wp-statistics'
						); ?></p>

                    <p class="description"><?php _e(
							'This option will also download the database if the local filesize is less than 1k (which usually means the stub that comes with the plugin is still in place).',
							'wp-statistics'
						); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="geoip-schedule"><?php _e(
							'Populate missing GeoIP after update of GeoIP DB:',
							'wp-statistics'
						); ?></label>
                </th>

                <td>
                    <input id="geoip-auto-pop" type="checkbox"
                           name="wps_auto_pop" <?php echo $WP_Statistics->get_option( 'auto_pop' ) == true
						? "checked='checked'" : ''; ?>>
                    <label for="geoip-auto-pop"><?php _e( 'Enable', 'wp-statistics' ); ?></label>

                    <p class="description"><?php _e(
							'Update any missing GeoIP data after downloading a new database.',
							'wp-statistics'
						); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="geoip-schedule"><?php _e( 'Country code for private IP addresses:', 'wp-statistics' ); ?></label>
                </th>

                <td>
                    <input type="text" size="3" id="geoip-private-country-code" name="wps_private_country_code"
                           value="<?php echo htmlentities(
						       $WP_Statistics->get_option( 'private_country_code', '000' ),
						       ENT_QUOTES
					       ); ?>">

                    <p class="description"><?php echo __(
							'The international standard two letter country code (ie. US = United States, CA = Canada, etc.) for private (non-routable) IP addresses (ie. 10.0.0.1, 192.158.1.1, 127.0.0.1, etc.).',
							'wp-statistics'
						) . ' ' . __(
							'Use "000" (three zeros) to use "Unknown" as the country code.',
							'wp-statistics'
						); ?></p>
                </td>
            </tr>
			<?php
		} else {
			?>
            <tr valign="top">
                <th scope="row" colspan="2">
					<?php
					echo __( 'GeoIP collection is disabled due to the following reasons:', 'wp-statistics' ) . '<br><br>';

					if ( ! function_exists( 'curl_init' ) ) {
						echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;* ';
						_e(
							'GeoIP collection requires the cURL PHP extension and it is not loaded on your version of PHP!',
							'wp-statistics'
						);
						echo '<br>';
					}

					if ( ! function_exists( 'bcadd' ) ) {
						echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;* ';
						_e(
							'GeoIP collection requires the BC Math PHP extension and it is not loaded on your version of PHP!',
							'wp-statistics'
						);
						echo '<br>';
					}

					if ( ini_get( 'safe_mode' ) ) {
						echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;* ';
						_e(
							'PHP safe mode detected! GeoIP collection is not supported with PHP\'s safe mode enabled!',
							'wp-statistics'
						);
						echo '<br>';
					}
					?>
                </th>
            </tr>
			<?php
		} ?>



        <tr valign="top">
            <th scope="row" colspan="2">
                <h3><?php _e( 'Matomo Referrer Spam Blacklist settings', 'wp-statistics' ); ?></h3>
            </th>
        </tr>

        <tr valign="top">
            <th scope="row" colspan="2">
				<?php echo sprintf(
					__( 'Referrer spam blacklist is provided by Matomo, available from %s.', 'wp-statistics' ),
					'<a href="https://github.com/matomo-org/referrer-spam-blacklist" target=_blank>https://github.com/matomo-org/referrer-spam-blacklist</a>'
				); ?>
            </th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="referrerspam-enable"><?php _e( 'Matomo Referrer Spam Blacklist usage:', 'wp-statistics' ); ?></label>
            </th>

            <td>
                <input id="referrerspam-enable" type="checkbox"
                       name="wps_referrerspam" <?php echo $WP_Statistics->get_option( 'referrerspam' ) == true
					? "checked='checked'" : ''; ?>>
                <label for="referrerspam-enable"><?php _e( 'Enable', 'wp-statistics' ); ?></label>

                <p class="description"><?php _e(
						'The Matomo Referrer Spam Blacklist database will be downloaded and used to detect referrer spam.',
						'wp-statistics'
					); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="geoip-update"><?php _e( 'Update Matomo Referrer Spam Blacklist Info:', 'wp-statistics' ); ?></label>
            </th>

            <td>
                <input id="referrerspam-update" type="checkbox"
                       name="wps_update_referrerspam" <?php echo $WP_Statistics->get_option( 'update_referrerspam' ) ==
				                                                 true ? "checked='checked'" : ''; ?>>
                <label for="referrerspam-update"><?php _e(
						'Download Matomo Referrer Spam Blacklist Database',
						'wp-statistics'
					); ?></label>

                <p class="description"><?php _e(
						'Save changes on this page to download the update.',
						'wp-statistics'
					); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="referrerspam-schedule"><?php _e(
						'Schedule weekly update of Matomo Referrer Spam Blacklist DB:',
						'wp-statistics'
					); ?></label>
            </th>

            <td>
                <input id="referrerspam-schedule" type="checkbox"
                       name="wps_schedule_referrerspam" <?php echo $WP_Statistics->get_option(
					'schedule_referrerspam'
				) == true ? "checked='checked'" : ''; ?>>
                <label for="referrerspam-schedule"><?php _e( 'Enable', 'wp-statistics' ); ?></label>
				<?php
				if ( $WP_Statistics->get_option( 'schedule_referrerspam' ) ) {
					echo '<p class="description">' . __( 'Next update will be', 'wp-statistics' ) . ': <code>';
					$last_update = $WP_Statistics->get_option( 'schedule_referrerspam' );
					if ( $last_update == 0 ) {
						$last_update = time();
					}
					$next_update = $last_update + ( 86400 * 7 );

					$next_schedule = wp_next_scheduled( 'wp_statistics_referrerspam_hook' );

					if ( $next_schedule ) {
						echo date( get_option( 'date_format' ), $next_schedule ) .
						     ' @ ' .
						     date( get_option( 'time_format' ), $next_schedule );
					} else {
						echo date( get_option( 'date_format' ), $next_update ) .
						     ' @ ' .
						     date( get_option( 'time_format' ), time() );
					}

					echo '</code></p>';
				}
				?>
                <p class="description"><?php _e(
						'Download of the Matomo Referrer Spam Blacklist database will be scheduled for once a week.',
						'wp-statistics'
					); ?></p>
            </td>
        </tr>

        </tbody>
    </table>

<?php submit_button( __( 'Update', 'wp-statistics' ), 'primary', 'submit' );
