<?php 
/**
 * Exit if accessed directly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$bulk            = false;
$basic           = false;
$front_table     = false;
$instruct        = false;
$basic_tab       = '';
$front_table_tab = '';
$basic_tab       = '';
$instruct_tab    = '';
if (isset($_GET['tab']) && null !== $_GET['tab'] ) { 
	if ( 'basic' === $_GET['tab']) {		
		$basic_tab = 'nav-tab-active'; 
		$basic     = true;
	} elseif ( 'bulk' === $_GET['tab'] ) { 
		$bulk_tab = 'nav-tab-active'; 
		$bulk     = true;
	} elseif ( 'front-table' === $_GET['tab'] ) { 
		$front_table_tab = 'nav-tab-active'; 
		$front_table     = true;
	} elseif ( 'instructions' === $_GET['tab'] ) { 
		$instruct_tab = 'nav-tab-active'; 
		$instruct     = true;
	} else {
		$basic_tab = 'nav-tab-active'; 
		$basic     = true;
	}
	
} else {
	$basic_tab = 'nav-tab-active'; 
	$basic     = true;
}

if (isset($_POST['iwe_pqp_general_save'])) { 
	if ( !isset($_POST['iwe_pqp_plugin_enable']) ) { 
		$_POST['iwe_pqp_plugin_enable'] = 'off';
	}
	if ( !isset($_POST['iwe_pqp_tax_enable']) ) { 
		$_POST['iwe_pqp_tax_enable'] = 'off';
	}
	if ( !isset($_POST['iwe_pqp_ftable_enable']) ) { 
		$_POST['iwe_pqp_ftable_enable'] = 'off';
	}
	update_option( 'iwe_pqp_plugin_enable', sanitize_text_field( wp_unslash($_POST['iwe_pqp_plugin_enable']))); 
	update_option( 'iwe_pqp_tax_enable', sanitize_text_field( wp_unslash($_POST['iwe_pqp_tax_enable']))); 
	update_option( 'iwe_pqp_ftable_enable', sanitize_text_field( wp_unslash($_POST['iwe_pqp_ftable_enable']))); 
	?>
		<div class="notice notice-success is-dismissible">
			<p><strong><?php esc_html_e('Settings Saved Successfully.', 'woo-per-quantity-pricing'); ?></strong></p>
		</div>
	<?php
}
$plugin_enable = get_option('iwe_pqp_plugin_enable', false);
$tax_calc      = get_option('iwe_pqp_tax_enable', 'on');
$ftable_enable = get_option('iwe_pqp_ftable_enable', false);

if (isset($_POST['iwe_pqp_bulk_hidden']) && !isset($_POST['iwe_pqp_bulk_remove'])) { 
	
	if (isset($_POST['pqp_minimum_global_qty']) && $_POST['pqp_minimum_global_qty'] !== null && isset($_POST['pqp_maximum_global_qty']) && $_POST['pqp_maximum_global_qty'] != null && isset($_POST['pqp_price_global_qty']) && $_POST['pqp_price_global_qty'] != null && isset($_POST['pqp_pricing_global_type']) && $_POST['pqp_pricing_global_type'] != null) { // phpcs:ignore
		$min_arr   = array_filter(sanitize_text_field( wp_unslash($_POST['pqp_minimum_global_qty']))); 
		$min_arr   = array_values($min_arr);
		$max_arr   = array_filter(sanitize_text_field( wp_unslash($_POST['pqp_maximum_global_qty']))); 
		$max_arr   = array_values($max_arr);
		$price_arr = array_filter(sanitize_text_field( wp_unslash($_POST['pqp_price_global_qty']))); 
		$disc_type = array_filter(sanitize_text_field( wp_unslash($_POST['pqp_pricing_global_type']))); 
		$disc_type = array_values($disc_type);
		if ( ( count($min_arr) === count($max_arr) ) && ( count($max_arr) === count($price_arr) )) {
			$product_ids = array();
			if ( isset($_POST['iwe_pqp_choose_prod']) && null !== $_POST['iwe_pqp_choose_prod'] ) { 
				$product_ids = sanitize_text_field( wp_unslash($_POST['iwe_pqp_choose_prod'])); 
			} elseif ( isset($_POST['iwe_pqp_choose_cat']) && null !== $_POST['iwe_pqp_choose_cat'] ) { 
				$catID               =sanitize_text_field( wp_unslash( $_POST['iwe_pqp_choose_cat'])); 
				$tax_qty['taxonomy'] = 'product_cat';  
				$tax_qty['field']    = 'id'; 
				$tax_qty['terms']    = $catID; 
				$tax_quer[]          = $tax_qty; 
				$args                = array( 
					'post_type' => array('product','product_variation'),
					'posts_per_page' => -1,
					'tax_query' => $tax_quer, 
				);
				$loop                = new WP_Query( $args );
			   

				$all_ids      = wp_list_pluck($loop->posts, 'ID');
				$variable_ids = array();
				foreach ($all_ids as $key => $value) {
					$product      = wc_get_product($value);
					$product_type = $product->get_type();

					if ( 'variable' === $product_type) {
						unset($all_ids[$key]);
						$childrenids  = $product->get_children();
						$variable_ids = wp_parse_args($childrenids, $variable_ids);
					}
				}
				$product_ids = wp_parse_args($variable_ids, $all_ids);
			}
			$qty_price = array(
				'min'  =>   $min_arr,
				'max'  => 	$max_arr,
				'price'=>	$price_arr,
				'type'=>    $disc_type
			);
			if ( isset($product_ids) && null !== $product_ids ) {
				foreach ($product_ids as $key => $value) {
					update_post_meta( $value, '_pqp_simple_quantity_pricing', $qty_price);
					update_post_meta( $value, '_per_quantity_pqp', 'yes');
				}
				?>
					<div class="notice notice-success is-dismissible">
						<p><strong><?php esc_html_e('Per Quantity Prices are added to selected Categories and Products.', 'woo-per-quantity-pricing'); ?></strong></p>
					</div>
				<?php
			}
		}
	}
}
if ( isset($_POST['iwe_pqp_bulk_remove']) ) { 
	$args = array( 
		'post_type' => array('product','product_variation'),
		'posts_per_page' => -1,
	);
	$loop = new WP_Query( $args );
	if ( isset($loop) && null !== $loop) {
		foreach ($loop->posts as $key => $value) {
			delete_post_meta($value->ID, '_pqp_simple_quantity_pricing');
			delete_post_meta($value->ID, '_per_quantity_pqp');
		}
		?>
			<div class="notice notice-success is-dismissible">
				<p><strong><?php esc_html_e('Per Quantity Prices are removed.', 'woo-per-quantity-pricing'); ?></strong></p>
			</div>
		<?php
	}
}
if (isset($_POST['iwe_pqp_front_submit'])) { 
    if (isset($_POST['woo_per_qty_nonce']) && wp_verify_nonce($_POST['woo_per_qty_nonce'], 'woo_per_qty_nonce')) {  // phpcs:ignore
		if (isset($_POST['iwe_pqp_table_thead_border']) && null !== $_POST['iwe_pqp_table_thead_border']) {
			update_option('iwe_pqp_table_thead_border', sanitize_text_field(wp_unslash($_POST['iwe_pqp_table_thead_border'])));
		}
		if (isset($_POST['iwe_pqp_table_thead_bg']) && null !== $_POST['iwe_pqp_table_thead_bg']) {
			update_option('iwe_pqp_table_thead_bg', sanitize_text_field(wp_unslash($_POST['iwe_pqp_table_thead_bg'])));
		}
		if (isset($_POST['iwe_pqp_table_thead_text']) && null !== $_POST['iwe_pqp_table_thead_text']) {
			update_option('iwe_pqp_table_thead_text', sanitize_text_field(wp_unslash($_POST['iwe_pqp_table_thead_text'])));
		}
		if (isset($_POST['iwe_pqp_table_tbody_border']) && null !== $_POST['iwe_pqp_table_tbody_border']) {
			update_option('iwe_pqp_table_tbody_border', sanitize_text_field(wp_unslash($_POST['iwe_pqp_table_tbody_border'])));
		}
		if (isset($_POST['iwe_pqp_table_tbody_bg']) && null !== $_POST['iwe_pqp_table_tbody_bg']) {
			update_option('iwe_pqp_table_tbody_bg', sanitize_text_field(wp_unslash($_POST['iwe_pqp_table_tbody_bg'])));
		}
		if (isset($_POST['iwe_pqp_table_tbody_text']) && null !== $_POST['iwe_pqp_table_tbody_text']) {
			update_option('iwe_pqp_table_tbody_text', sanitize_text_field(wp_unslash($_POST['iwe_pqp_table_tbody_text'])));
		}
	}
} 
$iwe_thead_border = get_option('iwe_pqp_table_thead_border', false);
$iwe_thead_bg     = get_option('iwe_pqp_table_thead_bg', false);
$iwe_thead_text   = get_option('iwe_pqp_table_thead_text', false);
$iwe_tbody_border = get_option('iwe_pqp_table_tbody_border', false);
$iwe_tbody_bg     = get_option('iwe_pqp_table_tbody_bg', false);
$iwe_tbody_text   = get_option('iwe_pqp_table_tbody_text', false);
?>
<div class="wrap woocommerce" id="iwe_pqp_setting_div">
	<div style="display: none;" class="loading-image" id="iwe_pqp_loader">
		<img src="<?php echo esc_url('IWE_PQP_URL'); ?>assets/backend/images/loading.gif">
	</div>
	<h1 class="iwe_pqp_setting_title"><?php esc_html_e('WooCommerce Per Quantity Pricing Settings', 'woo-per-quantity-pricing'); ?></h1>
	<nav class="nav-tab-wrapper woo-nav-tab-wrapper iwe_pqp_nav_tab_wrapper">
		<a class="nav-tab <?php echo esc_html($basic_tab); ?>" href="?page=iwe-pqp-setting&tab=basic"><?php esc_html_e('General Settings', 'woo-per-quantity-pricing'); ?></a>
		<a class="nav-tab <?php echo esc_html($bulk_tab); ?>" href="?page=iwe-pqp-setting&tab=bulk"><?php esc_html_e('Global/Bulk Table', 'woo-per-quantity-pricing'); ?></a>
		<a class="nav-tab <?php echo esc_html($front_table_tab); ?>" href="?page=iwe-pqp-setting&tab=front-table"><?php esc_html_e('Front End Table', 'woo-per-quantity-pricing'); ?></a>
		<a class="nav-tab <?php echo esc_html($instruct_tab); ?>" href="?page=iwe-pqp-setting&tab=instructions"><?php esc_html_e('Instructions', 'woo-per-quantity-pricing'); ?></a>
	</nav>
	
	<form enctype="multipart/form-data" action="" id="iwe_setting_form" method="post">
	<p>
				<input type="hidden" name="woo_per_qty_nonce" value="<?php echo esc_js(wp_create_nonce('woo_per_qty_nonce')); ?>"/>
				
			</p>
		<?php 
		
		
		if ( $basic ) {
			?>
					<table class="form-table iwe_pqp_basic_setting wp-list-table widefat striped">
						<tbody>	
							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="iwe_pqp_plugin_enable"><?php esc_html_e('Enable', 'woo-per-quantity-pricing'); ?></label>
								</th>
								<td class="forminp forminp-text">
									
									<label for="iwe_pqp_plugin_enable">
										<input type="checkbox" <?php echo ( 'on' === $plugin_enable )?"checked='checked'":''; ?> name="iwe_pqp_plugin_enable" id="iwe_pqp_plugin_enable" class="input-text">
										<p class="description"><?php esc_html_e('Check this box to enable the Plugin.', 'woo-per-quantity-pricing'); ?></p> 
									</label>						
								</td>
							</tr>
							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="iwe_pqp_tax_enable"><?php esc_html_e('Enable Tax Calculation', 'woo-per-quantity-pricing'); ?></label>
								</th>
								<td class="forminp forminp-text">
									
									<label for="iwe_pqp_tax_enable">
										<input type="checkbox" <?php echo ( 'on' === $tax_calc )?"checked='checked'":''; ?> name="iwe_pqp_tax_enable" id="iwe_pqp_tax_enable" class="input-text">
										<p class="description"><?php esc_html_e('Check this box to enable tax calculation on Per Quantity Pricing Table.', 'woo-per-quantity-pricing'); ?></p> 
									</label>						
								</td>
							</tr>
							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="iwe_pqp_ftable_enable"><?php esc_html_e('Display Table on product page', 'woo-per-quantity-pricing'); ?></label>
								</th>
								<td class="forminp forminp-text">
									
									<label for="iwe_pqp_ftable_enable">
										<input type="checkbox" <?php echo ( 'on' === $ftable_enable )?"checked='checked'":''; ?> name="iwe_pqp_ftable_enable" id="iwe_pqp_ftable_enable" class="input-text">
										<p class="description"><?php esc_html_e('Check this box to display Per Quantity Pricing Table on product single page.( Displays table on Variable Products also.)', 'woo-per-quantity-pricing'); ?></p> 
									</label>						
								</td>
							</tr>
						</tbody>
					</table>
					<p class="iwe_pqp_submit">
						<input type="submit" value="<?php esc_html_e('Save changes', 'woo-per-quantity-pricing'); ?>" class="button-primary woocommerce-save-button" name="iwe_pqp_general_save" id="iwe_pqp_general_save" >
					</p>

				<?php
		}
		if ( $bulk ) {
			?>
					<table class="form-table iwe_pqp_bulk_setting wp-list-table widefat striped">
						<tbody>	
							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="iwe_pqp_choose_cat"><?php esc_html_e('Select Categories', 'woo-per-quantity-pricing'); ?></label>
								</th>
								<td class="forminp forminp-text">
									
									<select id="iwe_pqp_choose_cat" multiple="multiple" name="iwe_pqp_choose_cat[]">
									<?php 
									$args       = array('taxonomy'=>'product_cat');
									$categories = get_terms($args);
									if (isset($categories) && !empty($categories)) {
										foreach ($categories as $category) {
											$catid   = $category->term_id;
											$catname = $category->name;
											?>
												<option value="<?php echo esc_html($catid); ?>"><?php echo esc_html($catname); ?></option>
												<?php 
										}
									}	
									?>
									</select>
									<p class="description"><?php esc_html_e('Select the categories on which you want to apply per quantity table.', 'woo-per-quantity-pricing'); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="iwe_pqp_choose_prod"><?php esc_html_e('Select Products', 'woo-per-quantity-pricing'); ?></label>
								</th>
								<td class="forminp forminp-text">
									
									<label for="iwe_pqp_choose_prod">
										<select id="iwe_pqp_choose_prod" multiple="multiple" name="iwe_pqp_choose_prod[]">
										</select>
										<p class="description"><?php esc_html_e('Select the products on which you want to apply per quantity table.', 'woo-per-quantity-pricing'); ?></p> 
									</label>						
								</td>
							</tr>
							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="iwe_pqp_qty_table"><?php esc_html_e('Per Quantity Table', 'woo-per-quantity-pricing'); ?></label>
								</th>
								<td colspan="" rowspan="" headers=""></td>
							</tr>
							<tr valign="top">
								<td class="forminp forminp-text" colspan="2">
									<table class="form-table wp-list-table widefat striped">
										<thead>
											<th><?php esc_html_e('Minimum Product Quantity', 'woo-per-quantity-pricing'); ?></th>
											<th><?php esc_html_e('Maximim Product Quantity', 'woo-per-quantity-pricing'); ?></th>
											<th><?php esc_html_e('Pricing Type', 'woo-per-quantity-pricing'); ?></th>
											<th><?php esc_html_e('Product Price', 'woo-per-quantity-pricing'); ?></th>
											<th><?php esc_html_e('Action', 'woo-per-quantity-pricing'); ?></th>
										</thead>
										<tbody class="pqp_tbody_global_rows">
											<tr>
												<td>
													<input type="number" class="pqp_minimum_global_qty" name="pqp_minimum_global_qty[]">
												</td>
												<td>
													<input type="number" class="pqp_maximum_global_qty" name="pqp_maximum_global_qty[]">
												</td>
												<td>
													<select name="pqp_pricing_global_type[]">
														<option value="pqp_sell_price"><?php esc_html_e('Selling Price', 'woo-per-quantity-pricing'); ?></option>
														<option value="pqp_fixed_disc"><?php esc_html_e('Fixed Discount', 'woo-per-quantity-pricing'); ?></option>
														<option value="pqp_perc_disc"><?php esc_html_e('Discount Percentage', 'woo-per-quantity-pricing'); ?></option>
													</select>
												</td>
												<td>
													<input type="text" class="pqp_price_global_qty wc_input_price" name="pqp_price_global_qty[]">
												</td>
												<td>
													<input type="button" class="pqp_action_remove button" data-index="0" value="<?php esc_html_e('Remove', 'woo-per-quantity-pricing'); ?>">
												</td>
											</tr>
										</tbody>
									</table>
									<input type="button" class="pqp_action_global_add button" value="<?php esc_html_e('Add More', 'woo-per-quantity-pricing'); ?>">
								</td>
							</tr>
						</tbody>
					</table>
					<input type="hidden" name="iwe_pqp_bulk_hidden">
					<p class="iwe_pqp_submit">
						<input type="submit" name="iwe_pqp_bulk_add" value="<?php esc_html_e('Assign Selected'); ?>" id="iwe_pqp_bulk_add" class="button-primary">
						<input type="submit" name="iwe_pqp_bulk_remove" value="<?php esc_html_e('Remove All'); ?>" id="iwe_pqp_bulk_remove" class="button-primary">
					</p>
				<?php
		}
		if ( $front_table ) {
			?>
					<table class="form-table iwe_pqp_front_end_setting wp-list-table widefat striped">
						<tbody>	
							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="iwe_pqp_table_thead_border"><?php esc_html_e('Table head border color', 'woo-per-quantity-pricing'); ?></label>
								</th>
								<td class="forminp forminp-text">
									<input type="text" class="iwe_pqp_color" name="iwe_pqp_table_thead_border" value="<?php echo esc_html($iwe_thead_border); ?>">
								</td>
							</tr>
							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="iwe_pqp_table_thead_bg"><?php esc_html_e('Table head background color', 'woo-per-quantity-pricing'); ?></label>
								</th>
								<td class="forminp forminp-text">
									<input type="text" class="iwe_pqp_color" name="iwe_pqp_table_thead_bg" value="<?php echo  esc_html($iwe_thead_bg); ?>">
								</td>
							</tr>
							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="iwe_pqp_table_thead_text"><?php esc_html_e('Table head text color', 'woo-per-quantity-pricing'); ?></label>
								</th>
								<td class="forminp forminp-text">
									<input type="text" class="iwe_pqp_color" name="iwe_pqp_table_thead_text" value="<?php echo esc_html($iwe_thead_text); ?>">
								</td>
							</tr>
							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="iwe_pqp_table_tbody_border"><?php esc_html_e('Table body border color', 'woo-per-quantity-pricing'); ?></label>
								</th>
								<td class="forminp forminp-text">
									<input type="text" class="iwe_pqp_color" name="iwe_pqp_table_tbody_border" value="<?php echo esc_html($iwe_tbody_border); ?>">
								</td>
							</tr>
							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="iwe_pqp_table_tbody_bg"><?php esc_html_e('Table body background color', 'woo-per-quantity-pricing'); ?></label>
								</th>
								<td class="forminp forminp-text">
									<input type="text" class="iwe_pqp_color" name="iwe_pqp_table_tbody_bg" value="<?php echo esc_html($iwe_tbody_bg); ?>">
								</td>
							</tr>
							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="iwe_pqp_table_tbody_text"><?php esc_html_e('Table body text color', 'woo-per-quantity-pricing'); ?></label>
								</th>
								<td class="forminp forminp-text">
									<input type="text" class="iwe_pqp_color" name="iwe_pqp_table_tbody_text" value="<?php echo esc_html($iwe_tbody_text); ?>">
								</td>
							</tr>
						</tbody>
					</table>
					<p class="iwe_pqp_submit">
						<input type="submit" name="iwe_pqp_front_submit" value="<?php esc_html_e('Save Settings'); ?>" id="iwe_pqp_front_submit" class="button-primary">
					</p>
				<?php
		}
		if ( $instruct ) {
			?>
					<h2><?php esc_html_e('Instructions to use this plugin', 'woo-per-quantity-pricing'); ?></h2>
				<?php
		}
		?>
	</form>
</div>
