<?php
/**
 * Exit if accessed directly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'IWE_PQP_Public_End' ) ) {

	/**
	 * This is class for managing front end per quantity functionality
	 *
	 * @name    IWE_PQP_Public_End
	 * @package Class
	 * Author   InnoWebEye  // phpcs:ignore
	 */

	class IWE_PQP_Public_End {

		public function __construct() {
			$plugin_enable = get_option( 'iwe_pqp_plugin_enable', 'off' );
			if ( 'on' === $plugin_enable ) {
				add_filter( 'woocommerce_get_price_html', array( $this, 'iwe_pqp_woo_get_price_html' ), 10, 2 );
				add_action( 'wp_enqueue_scripts', array( $this, 'iwe_pqp_wp_enqueue_scripts' ), 10 );
				add_action( 'wp_ajax_iwe_pqp_single_qty', array( $this, 'iwe_pqp_single_qty' ) );
				add_action( 'wp_ajax_nopriv_iwe_pqp_single_qty', array( $this, 'iwe_pqp_single_qty' ) );
				add_action( 'woocommerce_before_calculate_totals', array( $this, 'iwe_pqp_woo_before_calculate_totals' ), 25, 1 );
				add_action( 'woocommerce_single_product_summary', array( $this, 'iwe_pqp_woo_single_product_summary' ), 25 );
				add_filter( 'woocommerce_product_is_taxable', array( $this, 'iwe_pqp_woo_product_is_taxable' ), 10, 2 );
			}

		}
		public function iwe_pqp_woo_product_is_taxable( $taxable, $product ) {
			$product_id     = $product->get_id();
			$pqp_qty_enable = get_post_meta( $product_id, '_per_quantity_pqp', true );
			if ( isset( $pqp_qty_enable ) && 'yes' === $pqp_qty_enable   ) {
				$tax_calc = get_option( 'iwe_pqp_tax_enable', 'on' );
				if ( 'off' === $tax_calc ) {
					$taxable = false;
				}
			}
			return $taxable;
		}
		public function iwe_pqp_woo_single_product_summary() {
			$ftable_enable = get_option( 'iwe_pqp_ftable_enable', 'off' );
			if ( 'on' === $ftable_enable ) {
				global $post;
				$product_id   = $post->ID;
				$product      = wc_get_product( $product_id );
				$product_type = $product->get_type();
				if ( 'variable' === $product_type ) {
					?>
						<div class="iwe_pqp_per_qty_table"></div>
					<?php
				} else {
					$pqp_qty_enable = get_post_meta( $product_id, '_per_quantity_pqp', true );
					if ( isset( $pqp_qty_enable ) && 'yes'=== $pqp_qty_enable  ) {
						$pqp_table_data = get_post_meta( $product_id, '_pqp_simple_quantity_pricing', true );
						if ( isset( $pqp_table_data ) && null !== $pqp_table_data ) {
							$iwe_thead_border = get_option( 'iwe_pqp_table_thead_border', false );
							$iwe_thead_bg     = get_option( 'iwe_pqp_table_thead_bg', false );
							$iwe_thead_text   = get_option( 'iwe_pqp_table_thead_text', false );
							$iwe_tbody_border = get_option( 'iwe_pqp_table_tbody_border', false );
							$iwe_tbody_bg     = get_option( 'iwe_pqp_table_tbody_bg', false );
							$iwe_tbody_text   = get_option( 'iwe_pqp_table_tbody_text', false );
							$style_thead      = '';
							if ( isset( $iwe_thead_border ) && null !== $iwe_thead_border) {
								$style_thead .= ' border-color:' . $iwe_thead_border . ';';
							}
							if ( isset( $iwe_thead_text ) && null !== $iwe_thead_text ) {
								$style_thead .= ' color:' . $iwe_thead_text . ';';
							}
							if ( isset( $iwe_thead_bg ) && null !== $iwe_thead_bg ) {
								$style_thead .= ' background-color:' . $iwe_thead_bg . ';';
							}
							$style_tbody = '';
							if ( isset( $iwe_tbody_border ) && null !== $iwe_tbody_border ) {
								$style_tbody .= ' border-color:' . $iwe_tbody_border . ';';
							}
							if ( isset( $iwe_tbody_text ) && null !== $iwe_tbody_text ) {
								$style_tbody .= ' color:' . $iwe_tbody_text . ';';
							}
							if ( isset( $iwe_tbody_bg ) &&  null!== $iwe_tbody_bg ) {
								$style_tbody .= ' background-color:' . $iwe_tbody_bg . ';';
							}

							?>
								<div class="iwe_pqp_per_qty_table">
									<table class="iwe_pqp_table">
										<thead style="<?php echo esc_html( $style_thead ); ?>">
											<th><?php esc_html_e( 'Quantities', 'woo-per-quantity-pricing' ); ?></th>
											<th><?php esc_html_e( 'Price', 'woo-per-quantity-pricing' ); ?></th>
											<th><?php esc_html_e( 'Pricing Type', 'woo-per-quantity-pricing' ); ?></th>
										</thead>
										<tbody style="<?php echo esc_html( $style_tbody ); ?>">
											<?php
											foreach ( $pqp_table_data['min'] as $key1 => $value1 ) {
												?>
														<tr>
															<td>
															<?php
																echo esc_html( $value1 ) . '-' . esc_html($pqp_table_data['max'][ $key1 ]);
															?>
															</td>
															<td>
															<?php
															if ( 'pqp_perc_disc' === $pqp_table_data['type'][ $key1 ]   ) {
																echo ( esc_html( $pqp_table_data['price'][ $key1 ] ) ) . '%';
															} else {
																echo esc_html(get_woocommerce_currency_symbol()) . ( esc_html($pqp_table_data['price'][ $key1 ]) );
															}
															?>
															</td>
															<td>
																<?php
																if ( 'pqp_sell_price' === $pqp_table_data['type'][ $key1 ] ) {
																	esc_html_e( 'Selling Price', 'woo-per-quantity-pricing' );
																} elseif ( 'pqp_fixed_disc' === $pqp_table_data['type'][ $key1 ] ) {
																	esc_html_e( 'Fixed Discount', 'woo-per-quantity-pricing' );
																} elseif (  'pqp_perc_disc' === $pqp_table_data['type'][ $key1 ]) {
																	esc_html_e( 'Percentage Discount', 'woo-per-quantity-pricing' );
																}
																?>
															</td>
														</tr>
													<?php
											}
											?>
										</tbody>
									</table>
								</div>
							<?php
						}
					}
				}
			}
		}
		
		public function iwe_pqp_woo_before_calculate_totals( $cart ) {
			$tax_calc = get_option( 'iwe_pqp_tax_enable', 'on' );
			$is_tax   = false;
			if ( 'on' === $tax_calc) {
				$is_tax = true;
			}

			foreach ( $cart->cart_contents as $key => $value ) {
				$product_id   = $value['product_id'];
				$product      = $value['data'];
				$product_type = $product->get_type();
				if (  'variation' === $product_type  ) {
					$product_id = $value['variation_id'];
				}
				$qty            = $value['quantity'];
				$pqp_qty_enable = get_post_meta( $product_id, '_per_quantity_pqp', true );
				if ( isset( $pqp_qty_enable ) &&  'yes'=== $pqp_qty_enable ) {
					$pqp_table_data = get_post_meta( $product_id, '_pqp_simple_quantity_pricing', true );
					if ( isset( $pqp_table_data ) && null !== $pqp_table_data ) {
						$pqp_price = 0;
						$flag      = false;
						$type      = '';
						foreach ( $pqp_table_data['min'] as $key1 => $value1 ) {
							if ( $qty >= $value1 && $qty <= $pqp_table_data['max'][ $key1 ] ) {
								$pqp_price = $pqp_table_data['price'][ $key1 ];
								$type      = $pqp_table_data['type'][ $key1 ];
								$flag      = true;
								break;
							}
						}
						if ( $flag ) {

							if ( 'pqp_sell_price' === $type  ) {
								if ( WC()->version < '3.0.0' ) {

									$price = $pqp_price;

									$value['data']->price = $price;
								} else {

									$price = $pqp_price;

									$value['data']->set_price( $price );
								}
							} elseif ( 'pqp_fixed_disc' === $type ) {
								if ( WC()->version < '3.0.0' ) {

									$price = $product->get_price() - $pqp_price;

									$value['data']->price = $price;
								} else {

									$price = $product->get_price() - $pqp_price;

									$value['data']->set_price( $price );
								}
							} elseif ( 'pqp_perc_disc' === $type ) {
								if ( WC()->version < '3.0.0' ) {

									$discount = $product->get_price() - ( ( $product->get_price() * $pqp_price ) / 100 );
									$price    = $discount;

									$value['data']->price = $price;
								} else {

									$discount = $product->get_price() - ( ( $product->get_price() * $pqp_price ) / 100 );

									$price = $discount;

									$value['data']->set_price( $price );
								}
							}
						}
					}
				}
			}
		}
		public function iwe_pqp_single_qty() {
			check_ajax_referer( 'woo_per_qty_nonce', $iwe_pqp, false );
				$response['result'] = false;
			if (isset($_POST['product_id']) && null !== $_POST['product_id'] && isset($_POST['qty']) && null !== $_POST['qty'] ) {
				$product_id     = sanitize_text_field( wp_unslash($_POST['product_id']));
				$product        = wc_get_product($product_id);
				$qty            = sanitize_text_field(wp_unslash($_POST['qty']));
				$pqp_table_data = get_post_meta($product_id, '_pqp_simple_quantity_pricing', true);

				if (isset($pqp_table_data) && null !== $pqp_table_data) {
					$tax_calc = get_option('iwe_pqp_tax_enable', 'on');
					$is_tax   = false;
					if ('on' === $tax_calc) {
						$is_tax = true;
					}
					$iwe_thead_border = get_option('iwe_pqp_table_thead_border', false);
					$iwe_thead_bg     = get_option('iwe_pqp_table_thead_bg', false);
					$iwe_thead_text   = get_option('iwe_pqp_table_thead_text', false);
					$iwe_tbody_border = get_option('iwe_pqp_table_tbody_border', false);
					$iwe_tbody_bg     = get_option('iwe_pqp_table_tbody_bg', false);
					$iwe_tbody_text   = get_option('iwe_pqp_table_tbody_text', false);
					$style_thead      = '';
					if (isset($iwe_thead_border) && null !== $iwe_thead_border) {
						$style_thead .= ' border-color:' . $iwe_thead_border . ';';
					}
					if (isset($iwe_thead_text) && null !== $iwe_thead_text) {
						$style_thead .= ' color:' . $iwe_thead_text . ';';
					}
					if (isset($iwe_thead_bg) &&  null!== $iwe_thead_bg) {
						$style_thead .= ' background-color:' . $iwe_thead_bg . ';';
					}
					$style_tbody = '';
					if (isset($iwe_tbody_border) && null !== $iwe_tbody_border) {
						$style_tbody .= ' border-color:' . $iwe_tbody_border . ';';
					}
					if (isset($iwe_tbody_text) && null !== $iwe_tbody_text) {
						$style_tbody .= ' color:' . $iwe_tbody_text . ';';
					}
					if (isset($iwe_tbody_bg) &&  null !== $iwe_tbody_bg) {
						$style_tbody .= ' background-color:' . $iwe_tbody_bg . ';';
					}
					$pqp_price = 0;
					$flag      = false;
					$type      = '';

					foreach ($pqp_table_data['min'] as $key => $value) {
						if ($qty >= $value && $qty <= $pqp_table_data['max'][ $key ]) {
							$pqp_price = $pqp_table_data['price'][ $key ];
							$type      = $pqp_table_data['type'][ $key ];
							$flag      = true;
							break;
						}
					}
					$ftable_enable = get_option('iwe_pqp_ftable_enable', 'off');
					if ('on' === $ftable_enable) {
						$html .= '<table class="iwe_pqp_table"><thead style="' . $style_thead . '"><th>' . __('Quantities', 'woo-per-quantity-pricing') . '</th><th>' . __('Price', 'woo-per-quantity-pricing') . '</th><th>' . __('Pricing Type', 'woo-per-quantity-pricing') . '</th></thead><tbody style="' . $style_tbody . '">';
						foreach ($pqp_table_data['min'] as $key => $value) {
							$html .= '<tr><td>' . $value . '-' . $pqp_table_data['max'][ $key ] . '</td><td>';
							if ('pqp_perc_disc'=== $pqp_table_data['type'][ $key ]) {
								$html .= ( $pqp_table_data['price'][ $key ] ) . '%';
							} else {
								$html .= get_woocommerce_currency_symbol() . ( $pqp_table_data['price'][ $key ] );
							}

							$html .= '</td><td>';

							if ('pqp_sell_price' === $pqp_table_data['type'][ $key ]) {
								$html .= __('Selling Price', 'woo-per-quantity-pricing');
							} elseif ('pqp_fixed_disc' === $pqp_table_data['type'][ $key ]) {
								$html .= __('Fixed Discount', 'woo-per-quantity-pricing');
							} elseif ('pqp_perc_disc' === $pqp_table_data['type'][ $key ]) {
								$html .= __('Percentage Discount', 'woo-per-quantity-pricing');
							}
							$html .= '</td></tr>';
						}
						$html .= '</tbody></table>';
					}
					if ($flag) {
						if ('pqp_sell_price' === $type) {
							if (WC()->version < '3.0.0') {
								if ($is_tax) {
									$price = $product->get_display_price($pqp_price, 1);
								} else {
									$price = $pqp_price;
								}
								$price              = wc_price($price);
								$response['result'] = true;
							} else {
								if ($is_tax) {
									$args  = array(
									'qty'   => 1,
									'price' => $pqp_price,
									);
									$price = wc_get_price_to_display($product, $args);
								} else {
									$price = $pqp_price;
								}
								$price              = wc_price($price);
								$response['result'] = true;
							}
						} elseif ('pqp_fixed_disc' === $type) {
							if (WC()->version < '3.0.0') {
								if ($is_tax) {
									$price    = $product->get_display_price($product->get_price(), 1);
									$discount = $product->get_display_price(( $product->get_price() - $pqp_price ), 1);
									$price    = get_price_html_from_to($price, $discount);
								} else {
									$price = get_price_html_from_to($product->get_price(), ( $product->get_price() - $pqp_price ));
								}

								$response['result'] = true;
							} else {
								if ($is_tax) {
									$args     = array(
									'qty'   => 1,
									'price' => $product->get_price(),
									);
									$price    = wc_get_price_to_display($product, $args);
									$args     = array(
									'qty'   => 1,
									'price' => $product->get_price() - $pqp_price,
									);
									$discount = wc_get_price_to_display($product, $args);
									$price    = wc_format_sale_price($price, $discount);
								} else {
									$price = wc_format_sale_price($product->get_price(), ( $product->get_price() - $pqp_price ));
								}

								$response['result'] = true;
							}
						} elseif ('pqp_perc_disc' === $type) {
							if (WC()->version < '3.0.0') {
								if ($is_tax) {
									$price = $product->get_display_price($product->get_price(), 1);

									$discount = $product->get_price() - ( ( $product->get_price() * $pqp_price ) / 100 );
									$discount = $product->get_display_price($discount, 1);
									$price    = get_price_html_from_to($price, $discount);
								} else {
									$discount = $product->get_price() - ( ( $product->get_price() * $pqp_price ) / 100 );
									$price    = get_price_html_from_to($product->get_price(), $discount);
								}

								$response['result'] = true;
							} else {
								if ($is_tax) {
									$args     = array(
									'qty'   => 1,
									'price' => $product->get_price(),
									);
									$price    = wc_get_price_to_display($product, $args);
									$discount = $product->get_price() - ( ( $product->get_price() * $pqp_price ) / 100 );
									$args     = array(
									'qty'   => 1,
									'price' => $discount,
									);
									$discount = wc_get_price_to_display($product, $args);
									$price    = wc_format_sale_price($price, $discount);
								} else {
									$discount = $product->get_price() - ( ( $product->get_price() * $pqp_price ) / 100 );

									$price = wc_format_sale_price($product->get_price(), $discount);
								}
								$response['result'] = true;
							}
						}
						$response['data'] = $price;
					}
					$ftable_enable = get_option('iwe_pqp_ftable_enable', 'off');
					if ('on' === $ftable_enable) {
						$response['html'] = $html;
					}
				}
			}
				echo wp_json_encode($response);
				die;
			
		}
		public function iwe_pqp_wp_enqueue_scripts() {
			if ( is_product() ) {
				global $post;
				$product_id   = $post->ID;
				$product      = wc_get_product( $product_id );
				$product_type = $product->get_type();
				$childarr     = array();
				$iwe_pqp      = array();
				if ( 'variable' === $product_type ) {
					$childids = $product->get_children();
					foreach ( $childids as $key => $value ) {
						$pqp_qty_enable = get_post_meta( $value, '_per_quantity_pqp', true );
						if ( ! isset( $pqp_qty_enable ) || null === $pqp_qty_enable   || '' === $pqp_qty_enable   ) {
							$pqp_qty_enable = 'no';
						}
						$childarr[ $value ] = $pqp_qty_enable;
					}
					$iwe_pqp['var_arr'] = $childarr;
				} else {
					$pqp_qty_enable = get_post_meta( $product_id, '_per_quantity_pqp', true );
					if ( ! isset( $pqp_qty_enable ) || null === $pqp_qty_enable  || '' ===  $pqp_qty_enable  ) {
						$pqp_qty_enable = 'no';
					}
					$iwe_pqp['pqp_enable'] = $pqp_qty_enable;
				}
				$iwe_pqp['nonce']      = wp_create_nonce('woo_per_qty_nonce');
				$iwe_pqp['ajaxurl']    = admin_url( 'admin-ajax.php' );
				$iwe_pqp['product_id'] = $product_id;
				$iwe_pqp['type']       = $product_type;
				wp_register_script( 'iwe_pqp_product_single', IWE_PQP_URL . 'assets/frontend/js/woo-per-qty-public-single.js', array( 'jquery' ), '1.0.0', true );
				wp_localize_script( 'iwe_pqp_product_single', 'iwe_pqp', $iwe_pqp );
				wp_enqueue_script( 'iwe_pqp_product_single'  );
				wp_enqueue_style( 'iwe_pqp_product_single_css', IWE_PQP_URL . 'assets/frontend/css/woo-per-qty-public-single.css', '1.0.0', true  );

				
			}
		}
		/**
	 * This function is used to get price.
	 *
	 * @name iwe_pqp_woo_get_price_html
	 * @since 1.0.0
	 */
		public function iwe_pqp_woo_get_price_html( $price, $product ) {

			$product_type = $product->get_type();
			if ('variation' === $product_type ) {
				return '';
			}
			if ( 'simple' === $product_type   ) {
				$product_id = $product->get_id();

				$pqp_qty_enable = get_post_meta( $product_id, '_per_quantity_pqp', true );
				if ( isset( $pqp_qty_enable ) && 'yes' === $pqp_qty_enable ) {
					$pqp_table_data = get_post_meta( $product_id, '_pqp_simple_quantity_pricing', true );
					if ( isset( $pqp_table_data ) && null !== $pqp_table_data   ) {

						$pqp_price = 0;
						$flag      = false;
						foreach ( $pqp_table_data['min'] as $key => $value ) {
							if ( 1 === $value ) {
								$pqp_price = $pqp_table_data['price'][ $key ];
								$pqp_type  = $pqp_table_data['type'][ $key ];
								$flag      = true;
								break;
							}
						}
						$tax_calc = get_option( 'iwe_pqp_tax_enable', 'on' );
						$is_tax   = false;
						if ( 'on' === $tax_calc) {
							$is_tax = true;
						}
						if ( $flag ) {

							if ( 'pqp_sell_price' === $pqp_type ) {
								if ( WC()->version < '3.0.0' ) {
									if ( $is_tax ) {
										$price = $product->get_display_price( $pqp_price, 1 );
									} else {
										$price = $pqp_price;
									}
									$price = wc_price( $price );
								} else {
									if ( $is_tax ) {
										$args  = array(
											'qty'   => 1,
											'price' => $pqp_price,
										);
										$price = wc_get_price_to_display( $product, $args );
									} else {
										$price = $pqp_price;
									}
									$price = wc_price( $price );
								}
							} elseif ( 'pqp_fixed_disc' === $pqp_type ) {
								if ( WC()->version < '3.0.0' ) {
									if ( $is_tax ) {
										$price    = $product->get_display_price( $product->get_price(), 1 );
										$discount = $product->get_price() - $pqp_price;
										$discount = $product->get_display_price( discount, 1 );
										$price    = get_price_html_from_to( $price, discount );
									} else {
										$price = get_price_html_from_to( $product->get_price(), ( $product->get_price() - $pqp_price ) );
									}
								} else {
									if ( $is_tax ) {
										$args     = array(
											'qty'   => 1,
											'price' => $product->get_price(),
										);
										$price    = wc_get_price_to_display( $product, $args );
										$discount = $product->get_price() - $pqp_price;
										$args     = array(
											'qty'   => 1,
											'price' => $discount,
										);
										$discount = wc_get_price_to_display( $product, $args );
										$price    = wc_format_sale_price( $price, $discount );
									} else {
										$price = wc_format_sale_price( $product->get_price(), ( $product->get_price() - $pqp_price ) );
									}
								}
							} elseif ( 'pqp_perc_disc' === $pqp_type ) {
								if ( WC()->version < '3.0.0' ) {
									if ( $is_tax ) {
										$price = $product->get_display_price( $product->get_price(), 1 );

										$discount = $product->get_price() - ( ( $product->get_price() * $pqp_price ) / 100 );
										$discount = $product->get_display_price( $discount, 1 );
										$price    = get_price_html_from_to( $price, $discount );
									} else {
										$discount = $product->get_price() - ( ( $product->get_price() * $pqp_price ) / 100 );
										$price    = get_price_html_from_to( $product->get_price(), $discount );
									}
								} else {
									if ( $is_tax ) {
										$args     = array(
											'qty'   => 1,
											'price' => $product->get_price(),
										);
										$price    = wc_get_price_to_display( $product, $args );
										$discount = $product->get_price() - ( ( $product->get_price() * $pqp_price ) / 100 );
										$args     = array(
											'qty'   => 1,
											'price' => $discount,
										);
										$discount = wc_get_price_to_display( $product, $args );
										$price    = wc_format_sale_price( $price, $discount );
									} else {
										$discount = $product->get_price() - ( ( $product->get_price() * $pqp_price ) / 100 );

										$price = wc_format_sale_price( $product->get_price(), $discount );
									}
								}
							}
						}
					}
				}
			}
			return $price;
		}
	}

	new IWE_PQP_Public_End();
}
