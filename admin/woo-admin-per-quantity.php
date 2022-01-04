<?php
/**
 * If this file is called directly, abort.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'IWE_PQP_Admin_End' ) ) {

	/**
	 * This is class for managing admin panel functinality.
	 *
	 * @name    IWE_PQP_Admin_End
	 * @package Class
	 * Author   InnoWebEye
	 * @version  1.0.0
	 */
	class IWE_PQP_Admin_End {

		public function __construct() {
			$plugin_enable = get_option('iwe_pqp_plugin_enable', 'off');
			if ( 'on' === $plugin_enable) {
				add_action( 'woocommerce_product_options_general_product_data', array( $this, 'iwe_pqp_simple_table'), 10, 1);
				add_action( 'save_post', array( $this, 'iwe_pqp_save_simple_post'), 10, 1);
				add_action( 'woocommerce_variation_options_pricing', array($this, 'iwe_pqp_woo_variation_pricing'), 10, 3);
				add_action( 'woocommerce_save_product_variation', array($this, 'iwe_pqp_woo_save_product_variation'), 10, 2);
			}
			add_action( 'admin_enqueue_scripts', array($this, 'iwe_pqp_admin_enqueue_scripts'), 10, 1);
			add_action( 'admin_menu', array( $this, 'iwe_pqp_admin_menu' ));
			add_action( 'wp_ajax_iwe_pqp_choose_categories', array($this, 'iwe_pqp_choose_categories'));
			add_action( 'wp_ajax_nopriv_iwe_pqp_choose_categories', array($this, 'iwe_pqp_choose_categories'));

		}
		public function iwe_pqp_choose_categories() {
			check_ajax_referer( 'iwe_pqp_choose_categories', $iwe_pqp, false );
			if ( isset($_POST['catID']) && null !== $_POST['catID'] ) { 
				$catID           = $_POST['catID']; // phpcs:ignore
				$tax['taxonomy'] = 'product_cat';
				$tax['field']    = 'id';
				$tax['terms']    = $catID;
				$tax_quer[]      = $tax;
				$args            = array( 
					'post_type' => array('product','product_variation'),
					'posts_per_page' => -1,
					'tax_query' => $tax_quer,
				);
				$loop            = new WP_Query( $args );
				
				$html = '';
				foreach ($loop->posts as $key => $value) {
					$product      = wc_get_product($value->ID);
					$product_type = $product->get_type();

					if ( 'variable' === $product_type) {
						$childrenids = $product->get_children();
						foreach ($childrenids as $key1 => $value1) {
							$variation = wc_get_product($value1);
							$html     .= '<option value="' . $value1 . '">' . $variation->get_name() . '</option>';
						}	            		
					} else {
						$html .= '<option value="' . $value->ID . '">' . $product->get_formatted_name() . '</option>';
					}
				}
				echo wp_json_encode($html);
			}
			die;
		}
		public function iwe_pqp_admin_menu() {
			add_submenu_page( 
				'woocommerce',
				__('Woo Per Quantity Pricing', ' woo-per-quantity-pricing'),
				__('Woo Per Quantity Pricing', ' woo-per-quantity-pricing'),
				'manage_woocommerce', 'iwe-pqp-setting',
				array($this, 'iwe_pqp_per_qty_admin_setting')
			);
		}
		public function iwe_pqp_per_qty_admin_setting() {
			include_once IWE_PQP_DIRPATH . '/admin/partials/woo-per-qty-setting.php';
		}
		public function iwe_pqp_woo_save_product_variation( $variation_id, $loop ) {
			$product_id = $variation_id;
			if ( !isset($_POST['_per_quantity_pqp_' . $loop]) ) {
				$_POST['_per_quantity_pqp_' . $loop] = 'no';
			}
			if ( isset($_POST['_per_quantity_pqp_' . $loop]) ) {
				check_ajax_referer( 'iwe_pqp_choose_categories', $iwe_pqp, false );
				update_post_meta( $product_id, '_per_quantity_pqp', sanitize_text_field( wp_unslash($_POST['_per_quantity_pqp_' . $loop])));
			}
			/*if( isset($_POST['_pqp_simple_minimum_'.$loop]) ) {
				update_post_meta( $product_id, '_pqp_simple_minimum_'.$loop, $_POST['_pqp_simple_minimum_'.$loop]);
			}
			if( isset($_POST['_pqp_simple_maximum_'.$loop]) ) {
				update_post_meta( $product_id, '_pqp_simple_maximum_'.$loop, $_POST['_pqp_simple_maximum_'.$loop]);
			}*/
			
			if (isset($_POST['pqp_minimum_qty'][$loop][0]) && null !== $_POST['pqp_minimum_qty'][$loop][0] && isset($_POST['pqp_maximum_qty'][$loop][0]) && null !== $_POST['pqp_maximum_qty'][$loop][0] && isset($_POST['pqp_price_qty'][$loop][0]) && null !== $_POST['pqp_price_qty'][$loop][0] && isset($_POST['pqp_pricing_type'][$loop][0]) && null !== $_POST['pqp_pricing_type'][$loop][0]) {
				check_ajax_referer( 'iwe_pqp_choose_categories', $iwe_pqp, false );
				$min_arr = array_filter(sanitize_text_field( wp_unslash($_POST['pqp_minimum_qty'][$loop])));
				
				$min_arr = array_values($min_arr);
				
				$max_arr   = array_filter(sanitize_text_field( wp_unslash($_POST['pqp_maximum_qty'][$loop])));
				$max_arr   = array_values($max_arr);
				$price_arr = array_filter(sanitize_text_field( wp_unslash($_POST['pqp_price_qty'][$loop])));
				$price_arr = array_values($price_arr);
				$disc_type = array_filter(sanitize_text_field( wp_unslash($_POST['pqp_pricing_type'][$loop])));
				$disc_type = array_values($disc_type);
				if ( ( count($min_arr) === count($max_arr) ) && ( count($max_arr) === count($price_arr) )) {
					$qty_price = array(
							'min'  =>   $min_arr,
							'max'  => 	$max_arr,
							'price'=>	$price_arr,
							'type'=>    $disc_type
						);
					update_post_meta( $product_id, '_pqp_simple_quantity_pricing', $qty_price);
				}

			} else {
				delete_post_meta( $product_id, '_pqp_simple_quantity_pricing' );
			}
		}
		public function iwe_pqp_woo_variation_pricing( $loop, $variation_data, $variation ) {
			$product_id       = $variation->ID;
			$pqp_product_data = get_post_meta( $product_id, '_pqp_simple_quantity_pricing', true );
			
			$loop_table = get_post_meta( $product_id, '_per_quantity_pqp', true);

			
			$display = '';
			if (isset($loop_table) && null !== $loop_table && 'yes' === $loop_table ) {
				$display = 'block';
			} else {
				$display = 'none';
			}
			?>
				<div class="options_group show_if_simple show_if_external">
					<?php
						woocommerce_wp_checkbox( array(
							'id'      => "_per_quantity_pqp_{$loop}",
							'value'	  => $loop_table,
							'class'	  => 'iwe_pqp_variation_enable',
							'label'   => __( 'Enable per quantity pricing', 'woo-per-quantity-pricing' ),
							'desc_tip'    => true,
							'description' => __( 'Check this box if you want to enable per quantity pricing table.', 'woo-per-quantity-pricing' ),
						) );
					
						
					?>
					<div id="iwe_pqp_table_variable_<?php echo esc_html($loop); ?>" style="display: <?php echo esc_html($display); ?>;" class="iwe_pqp_table_variable">
						<table>
							<thead>
								<th><?php esc_html_e('Minimum Product Quantity', 'woo-per-quantity-pricing'); ?></th>
								<th><?php esc_html_e('Maximim Product Quantity', 'woo-per-quantity-pricing'); ?></th>
								<th><?php esc_html_e('Pricing Type', 'woo-per-quantity-pricing'); ?></th>
								<th><?php esc_html_e('Product Price', 'woo-per-quantity-pricing'); ?></th>
								<th><?php esc_html_e('Action', 'woo-per-quantity-pricing'); ?></th>
							</thead>
							<tbody class="pqp_tbody_rows_<?php echo esc_html($loop); ?>">
								<?php
								if ( isset($pqp_product_data) && null !== $pqp_product_data ) {
									foreach ($pqp_product_data['min'] as $key => $value) {
										?>
												<tr>
													<td>
														<input type="number" name="pqp_minimum_qty[<?php echo esc_html($loop); ?>][]" class="pqp_minimum_qty_<?php echo esc_html($loop); ?>" value="<?php echo esc_html($value); ?>">
													</td>
													<td>
														<input type="number" name="pqp_maximum_qty[<?php echo esc_html($loop); ?>][]" class="pqp_maximum_qty_<?php echo esc_html($loop); ?>" value="<?php echo esc_html($pqp_product_data['max'][$key]); ?>">
													</td>
													<td>
														<select name="pqp_pricing_type[<?php echo esc_html($loop); ?>][]">
															<option value="pqp_sell_price" <?php selected( $pqp_product_data['type'][$key], 'pqp_sell_price' ); ?>><?php esc_html_e('Selling Price', 'woo-per-quantity-pricing'); ?></option>
															<option value="pqp_fixed_disc" <?php selected( $pqp_product_data['type'][$key], 'pqp_fixed_disc' ); ?>><?php esc_html_e('Fixed Discount', 'woo-per-quantity-pricing'); ?></option>
															<option value="pqp_perc_disc" <?php selected( $pqp_product_data['type'][$key], 'pqp_perc_disc' ); ?>><?php esc_html_e('Discount Percentage', 'woo-per-quantity-pricing'); ?></option>
														</select>
													</td>
													<td>
														<input type="text" name="pqp_price_qty[<?php echo esc_html($loop); ?>][]" class="pqp_price_qty_<?php echo esc_html($loop); ?> wc_input_price" value="<?php echo esc_html($pqp_product_data['price'][$key]); ?>">
													</td>
													<td>
														<input type="button" class="pqp_action_remove button" data-index="<?php echo esc_html($key); ?>" value="<?php esc_html_e('Remove', 'woo-per-quantity-pricing'); ?>">
													</td>
												</tr>
											<?php
									}
								} else {
									?>
											<tr>
												<td>
													<input type="number" class="pqp_minimum_qty_<?php echo esc_html($loop); ?>" name="pqp_minimum_qty[<?php echo esc_html($loop); ?>][]">
												</td>
												<td>
													<input type="number" class="pqp_maximum_qty_<?php echo esc_html($loop); ?>" name="pqp_maximum_qty[<?php echo esc_html($loop); ?>][]">
												</td>
												<td>
													<select name="pqp_pricing_type[<?php echo esc_html($loop); ?>][]">
														<option value="pqp_sell_price"><?php esc_html_e('Selling Price', 'woo-per-quantity-pricing'); ?></option>
														<option value="pqp_fixed_disc"><?php esc_html_e('Fixed Discount', 'woo-per-quantity-pricing'); ?></option>
														<option value="pqp_perc_disc"><?php esc_html_e('Discount Percentage', 'woo-per-quantity-pricing'); ?></option>
													</select>
												</td>
												<td>
													<input type="text" class="pqp_price_qty_<?php echo esc_html($loop); ?> wc_input_price" name="pqp_price_qty[<?php echo esc_html($loop); ?>][]">
												</td>
												<td>
													<input type="button" class="pqp_action_remove button" data-index="0" value="<?php esc_html_e('Remove', 'woo-per-quantity-pricing'); ?>">
												</td>
											</tr>
										<?php
								}
								?>
							</tbody>
						</table>
						<input type="button" class="pqp_action_add button" value="<?php esc_html_e('Add More', 'woo-per-quantity-pricing'); ?>" data-loop="<?php echo esc_html($loop); ?>">
					</div>
				</div>
			<?php
		}
		public function iwe_pqp_admin_enqueue_scripts() {
			$screen = get_current_screen();
			
			if (isset($screen->id)) {
				$pagescreen = $screen->id;
				if ('product' === $pagescreen) {
					wp_register_script('iwe_pqp_table_script', IWE_PQP_URL . '/assets/backend/js/woo-per-qty-product-admin.js', array('jquery'), '1.0.0');
					wp_enqueue_script('iwe_pqp_table_script');
					wp_enqueue_style('iwe_pqp_table_style', IWE_PQP_URL . '/assets/backend/css/woo-per-qty-product-admin.css', array(), '1.0');
					
				} elseif ( 'woocommerce_page_iwe-pqp-setting' === $pagescreen) {
					wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), '1.0.0' );
					wp_enqueue_style( 'woocommerce_admin_menu_styles' );
					wp_enqueue_style( 'woocommerce_admin_styles' );
						
					wp_register_script( 'woocommerce_admin', WC()->plugin_url() . '/assets/js/admin/woocommerce_admin.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip' ), WC_VERSION );
					$locale  = localeconv();
					$decimal = isset( $locale['decimal_point'] ) ? $locale['decimal_point'] : '.';
					$params  = array(
						/* translators: %s: decimal */
						'i18n_decimal_error'                => sprintf( __( 'Please enter in decimal (%s) format without thousand separators.', 'woo-per-quantity-pricing' ), $decimal ),
						/* translators: %s: price decimal separator */
						'i18n_mon_decimal_error'            => sprintf( __( 'Please enter in monetary decimal (%s) format without thousand separators and currency symbols.', 'woo-per-quantity-pricing' ), wc_get_price_decimal_separator() ),
						'i18n_country_iso_error'            => __( 'Please enter in country code with two capital letters.', 'woo-per-quantity-pricing' ),
						'i18_sale_less_than_regular_error'  => __( 'Please enter in a value less than the regular price.', 'woo-per-quantity-pricing' ),
						'decimal_point'                     => $decimal,
						'mon_decimal_point'                 => wc_get_price_decimal_separator(),
						'strings' => array(
							'import_products' => __( 'Import', 'woo-per-quantity-pricing' ),
							'export_products' => __( 'Export', 'woo-per-quantity-pricing' ),
						),
						'urls' => array(
							'import_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_importer' ) ),
							'export_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_exporter' ) ),
						),
					);

					wp_localize_script( 'woocommerce_admin', 'woocommerce_admin', $params );
					wp_enqueue_script( 'woocommerce_admin' );

					wp_enqueue_style( 'wp-color-picker' );
					wp_enqueue_script( 'wp-color-picker' );
					wp_enqueue_style('iwe_pqp_table_style', IWE_PQP_URL . '/assets/backend/css/woo-per-qty-setting-admin.css', '1.0.0', true);
					wp_register_script('iwe_pqp_table_global_script', IWE_PQP_URL . '/assets/backend/js/woo-per-qty-global-admin.js', array('jquery','wp-color-picker'), '1.0.0');


					 $iwe_pqp['ajaxurl'] = admin_url('admin-ajax.php');
					 $iwe_pqp['nonce']   = wp_create_nonce('woo_per_qty_category_nonce');
				
					wp_localize_script('iwe_pqp_table_global_script', 'iwe_pqp', $iwe_pqp );
					
					wp_enqueue_script('iwe_pqp_table_global_script', '1.0.0'); // phpcs:ignore
					wp_enqueue_script('iwe_pqp_select2', IWE_PQP_URL . 'assets/backend/js/select2.js', array(), '1.0.0', true );
					wp_enqueue_style('iwe_pqp_select2', IWE_PQP_URL . 'assets/backend/css/select2.css', '1.0.0', true);
				}
			}
		}
		public function iwe_pqp_save_simple_post() {
			global $post;
			if ( isset($post)) {
				$product_id = $post->ID;
			
				if (!isset($_POST['_per_quantity_pqp'])) {
					check_ajax_referer( 'iwe_pqp_choose_categories', $iwe_pqp, false );
					$_POST['_per_quantity_pqp'] = 'no';
				}

				if ( isset($_POST['_per_quantity_pqp']) ) {
					check_ajax_referer( 'iwe_pqp_choose_categories', $iwe_pqp, false );
					update_post_meta( $product_id, '_per_quantity_pqp', sanitize_text_field( wp_unslash($_POST['_per_quantity_pqp'])));
				}
				
				if (isset($_POST['pqp_minimum_qty']) && null !== $_POST['pqp_minimum_qty'] && isset($_POST['pqp_maximum_qty']) && null !== $_POST['pqp_maximum_qty'] && isset($_POST['pqp_price_qty']) && null !== $_POST['pqp_price_qty'] && isset($_POST['pqp_pricing_type']) && null !== $_POST['pqp_pricing_type']) {
					check_ajax_referer( 'iwe_pqp_choose_categories', $iwe_pqp, false );
					$min_arr   = array_filter(sanitize_text_field( wp_unslash($_POST['pqp_minimum_qty'])));
					$min_arr   = array_values($min_arr);
					$max_arr   = array_filter(sanitize_text_field( wp_unslash($_POST['pqp_maximum_qty'])));
					$max_arr   = array_values($max_arr);
					$price_arr = array_filter(sanitize_text_field( wp_unslash($_POST['pqp_price_qty'])));
					$price_arr = array_values($price_arr);
					$disc_type = array_filter(sanitize_text_field( wp_unslash($_POST['pqp_pricing_type'])));
					$disc_type = array_values($disc_type);
					if ( ( count($min_arr) === count($max_arr) ) && ( count($max_arr) === count($price_arr) )) {
						$qty_price = array(
								'min'  =>   $min_arr,
								'max'  => 	$max_arr,
								'price'=>	$price_arr,
								'type'=>    $disc_type
							);
						update_post_meta( $product_id, '_pqp_simple_quantity_pricing', $qty_price);
					}

				} else {
					delete_post_meta( $product_id, '_pqp_simple_quantity_pricing' );
				}
			}
		}
		public function iwe_pqp_simple_table() {
			global $post;
			$product_id       = $post->ID;
			$pqp_product_data = get_post_meta( $product_id, '_pqp_simple_quantity_pricing', true );
			

			?>
				<div class="options_group show_if_simple show_if_external">
					<?php
						woocommerce_wp_checkbox( array(
							'id'      => '_per_quantity_pqp',
							'label'   => __( 'Enable per quantity pricing', 'woo-per-quantity-pricing' ),
							'desc_tip'    => true,
							'description' => __( 'Check this box if you want to enable per quantity pricing table.', 'woo-per-quantity-pricing' ),
						) );
					
						
					?>
					<div class="iwe_pqp_table_single" style="display: none;">
						<table>
							<thead>
								<th><?php esc_html_e('Minimum Product Quantity', 'woo-per-quantity-pricing'); ?></th>
								<th><?php esc_html_e('Maximim Product Quantity', 'woo-per-quantity-pricing'); ?></th>
								<th><?php esc_html_e('Pricing Type', 'woo-per-quantity-pricing'); ?></th>
								<th><?php esc_html_e('Product Price', 'woo-per-quantity-pricing'); ?></th>
								<th><?php esc_html_e('Action', 'woo-per-quantity-pricing'); ?></th>
							</thead>
							<tbody class="pqp_tbody_rows">
								<?php
								if ( isset($pqp_product_data) && null !== $pqp_product_data ) {
									foreach ($pqp_product_data['min'] as $key => $value) {
										?>
												<tr>
													<td>
														<input type="number" name="pqp_minimum_qty[]" class="pqp_minimum_qty" value="<?php echo esc_html($value); ?>">
													</td>
													<td>
														<input type="number" name="pqp_maximum_qty[]" class="pqp_maximum_qty" value="<?php echo esc_html($pqp_product_data['max'][$key]); ?>">
													</td>
													<td>
														<select name="pqp_pricing_type[]">
															<option value="pqp_sell_price" <?php selected( $pqp_product_data['type'][$key], 'pqp_sell_price' ); ?>><?php esc_html_e('Selling Price', 'woo-per-quantity-pricing'); ?></option>
															<option value="pqp_fixed_disc" <?php selected( $pqp_product_data['type'][$key], 'pqp_fixed_disc' ); ?>><?php esc_html_e('Fixed Discount', 'woo-per-quantity-pricing'); ?></option>
															<option value="pqp_perc_disc" <?php selected( $pqp_product_data['type'][$key], 'pqp_perc_disc' ); ?>><?php esc_html_e('Discount Percentage', 'woo-per-quantity-pricing'); ?></option>
														</select>
													</td>
													<td>
														<input type="text" name="pqp_price_qty[]" class="pqp_price_qty wc_input_price" value="<?php echo esc_html($pqp_product_data['price'][$key]); ?>">
													</td>
													<td>
														<input type="button" class="pqp_action_remove button" data-index="<?php echo esc_html($key); ?>" value="<?php esc_html_e('Remove', 'woo-per-quantity-pricing'); ?>">
													</td>
												</tr>
											<?php
									}
								} else {
									?>
											<tr>
												<td>
													<input type="number" class="pqp_minimum_qty" name="pqp_minimum_qty[]">
												</td>
												<td>
													<input type="number" class="pqp_maximum_qty" name="pqp_maximum_qty[]">
												</td>
												<td>
													<select name="pqp_pricing_type[]">
														<option value="pqp_sell_price"><?php esc_html_e('Selling Price', 'woo-per-quantity-pricing'); ?></option>
														<option value="pqp_fixed_disc"><?php esc_html_e('Fixed Discount', 'woo-per-quantity-pricing'); ?></option>
														<option value="pqp_perc_disc"><?php esc_html_e('Discount Percentage', 'woo-per-quantity-pricing'); ?></option>
													</select>
												</td>
												<td>
													<input type="text" class="pqp_price_qty wc_input_price" name="pqp_price_qty[]">
												</td>
												<td>
													<input type="button" class="pqp_action_remove button" data-index="0" value="<?php esc_html_e('Remove', 'woo-per-quantity-pricing'); ?>">
												</td>
											</tr>
										<?php
								}
								?>
							</tbody>
						</table>
						<input type="button" class="pqp_action_add button" value="<?php esc_html_e('Add More', 'woo-per-quantity-pricing'); ?>">
					</div>
				</div>
			<?php
		}
	}
	new IWE_PQP_Admin_End();
}
