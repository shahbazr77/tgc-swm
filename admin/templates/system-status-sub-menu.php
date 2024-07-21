<h2><?php esc_html_e( 'System Status', 's2w-import-shopify-to-woocommerce' ) ?></h2>
<table cellspacing="0" id="status" class="wc_status_table widefat">
    <thead>
    <tr>
        <th><?php esc_html_e( 'Option', 's2w-import-shopify-to-woocommerce' ) ?></th>
        <th><?php esc_html_e( 'Value', 's2w-import-shopify-to-woocommerce' ) ?></th>
        <th><?php esc_html_e( 'Minimum Required', 's2w-import-shopify-to-woocommerce' ) ?></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td data-export-label="file_get_contents">file_get_contents</td>
        <td>
			<?php
			if ( function_exists( 'file_get_contents' ) ) {
				?>
                <mark class="yes">&#10004; <code class="private"></code></mark>
				<?php
			} else {
				?>
                <mark class="error">&#10005;</mark>'
				<?php
			}
			?>
        </td>
        <td><?php esc_html_e( 'Required', 's2w-import-shopify-to-woocommerce' ) ?></td>
    </tr>
    <tr>
        <td data-export-label="file_put_contents">file_put_contents</td>
        <td>
			<?php
			if ( function_exists( 'file_put_contents' ) ) {
				?>
                <mark class="yes">&#10004; <code class="private"></code></mark>
				<?php
			} else {
				?>
                <mark class="error">&#10005;</mark>
				<?php
			}
			?>

        </td>
        <td><?php esc_html_e( 'Required', 's2w-import-shopify-to-woocommerce' ) ?></td>
    </tr>
    <tr>
        <td data-export-label="mkdir">mkdir</td>
        <td>
			<?php
			if ( function_exists( 'mkdir' ) ) {
				?>
                <mark class="yes">&#10004; <code class="private"></code></mark>
				<?php
			} else {
				?>
                <mark class="error">&#10005;</mark>
				<?php
			}
			?>

        </td>
        <td><?php esc_html_e( 'Required', 's2w-import-shopify-to-woocommerce' ) ?></td>
    </tr>
    <tr>
        <td data-export-label="<?php esc_html_e( 'Log Directory Writable', 's2w-import-shopify-to-woocommerce' ) ?>"><?php esc_html_e( 'Log Directory Writable', 's2w-import-shopify-to-woocommerce' ) ?></td>
        <td>
			<?php

			if ( wp_is_writable( SWM_CACHE ) ) {
				echo '<mark class="yes">&#10004; <code class="private">' . SWM_CACHE . 'swm-cache/</code></mark> ';
			} else {
				printf( '<mark class="error">&#10005; ' . __( 'To allow logging, make <code>%s</code> writable or define a custom <code>SWM_CACHE</code>.', 's2w-import-shopify-to-woocommerce' ) . '</mark>', SWM_CACHE );
			}
			?>

        </td>
        <td><?php esc_html_e( 'Required', 's2w-import-shopify-to-woocommerce' ) ?></td>
    </tr>
	<?php
	$max_execution_time = ini_get( 'max_execution_time' );
	$max_input_vars     = ini_get( 'max_input_vars' );
	$memory_limit       = ini_get( 'memory_limit' );
	?>
    <tr>
        <td data-export-label="<?php esc_attr_e( 'PHP Time Limit', 's2w-import-shopify-to-woocommerce' ) ?>"><?php esc_html_e( 'PHP Time Limit', 's2w-import-shopify-to-woocommerce' ) ?></td>
        <td style="<?php if ( $max_execution_time > 0 && $max_execution_time < 300 ) {
			esc_attr_e( 'color:red' );
		} ?>"><?php esc_html_e( $max_execution_time ); ?></td>
        <td><?php esc_html_e( '300', 's2w-import-shopify-to-woocommerce' ) ?></td>
    </tr>
    <tr>
        <td data-export-label="<?php esc_attr_e( 'PHP Max Input Vars', 's2w-import-shopify-to-woocommerce' ) ?>"><?php esc_html_e( 'PHP Max Input Vars', 's2w-import-shopify-to-woocommerce' ) ?></td>

        <td style="<?php if ( $max_input_vars < 1000 ) {
			esc_attr_e( 'color:red' );
		} ?>"><?php esc_html_e( $max_input_vars ); ?></td>
        <td><?php esc_html_e( '1000', 's2w-import-shopify-to-woocommerce' ) ?></td>
    </tr>
    <tr>
        <td data-export-label="<?php esc_attr_e( 'Memory Limit', 's2w-import-shopify-to-woocommerce' ) ?>"><?php esc_html_e( 'Memory Limit', 's2w-import-shopify-to-woocommerce' ) ?></td>

        <td style="<?php if ( intval( $memory_limit ) < 64 ) {
			esc_attr_e( 'color:red' );
		} ?>"><?php esc_html_e( $memory_limit ); ?></td>
        <td><?php esc_html_e( '64M', 's2w-import-shopify-to-woocommerce' ) ?></td>
    </tr>

    </tbody>
</table>