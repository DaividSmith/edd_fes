<?php
/**
 * Graphing Functions
 *
 * Handles graphing functionality
 * used in FES, like the vendor
 * backend profile graphs.
 *
 * @package FES
 * @subpackage Reports
 * @since 2.3.0
 *
 * @todo It's crazy how much stuff FES
 *       has to copy out of EDD because
 *       EDD's isn't extensible enough.
 *       Maybe over time we can fix EDD
 *       core so we don't have to copy 
 *       so much.
 *
 * @todo This is so convoluted because
 *       when this was copied from EDD,
 *       EDD's system is designed to be
 *       used for multiple graphs, but 
 *       FES only uses one and this makes
 *       the whole thing so complicated.
 *       One day, FES could hopefully use
 *       EDD's system instead of needing
 *       our own stuff.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * Show report graphs.
 *
 * Makes the vendor graphs for the vendor
 * profile in the admin.
 *
 * @since 2.3.0
 * @access public
 *
 * @todo This function is doing way too many things. Maybe over
 *       time it can be split up.
 * @todo This function isn't generic enough. It can't be reused
 *       easily for other graphs. This is a problem.
 *
 * @param FES_Vendor $vendor Vendor object to make graph for.
 * @return string HTML output of the graph or error.
 */
function fes_reports_graph( $vendor ) {
	// Retrieve the queried dates
	$dates = edd_get_report_dates();

	// Determine graph options
	switch ( $dates['range'] ) :
		case 'today' :
		case 'yesterday' :
			$day_by_day	= true;
			break;
		case 'last_year' :
		case 'this_year' :
		case 'last_quarter' :
		case 'this_quarter' :
			$day_by_day = false;
			break;
		case 'other' :
			if ( $dates['m_end'] - $dates['m_start'] >= 2 || $dates['year_end'] > $dates['year'] && ( $dates['m_start'] != '12' && $dates['m_end'] != '1' ) ) {
				$day_by_day = false;
			} else {
				$day_by_day = true;
			}
			break;
		default:
			$day_by_day = true;
			break;
	endswitch;

	$earnings_totals = 0.00; // Total earnings for time period shown
	$sales_totals    = 0;            // Total sales for time period shown
	$commissions_totals = 0.00; // Total for commissions for time period shown

	$earnings_data = array();
	$sales_data    = array();
	$products = EDD_FES()->vendors->get_all_products( $vendor->user_id );
	$arr = array();
	if ( empty ( $products ) ){
		return sprintf( __( 'This user has no %s!', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = true, $uppercase = false ) );
	}
	foreach( $products as $product ){
		array_push( $arr, $product['ID'] );
	}

	if ( $dates['range'] == 'today' || $dates['range'] == 'yesterday' ) {
		// Hour by hour
		$hour  = 1;
		$month = $dates['m_start'];
		while ( $hour <= 23 ) :
			$sales    = fes_get_sales_by_date( $dates['day'], $month, $dates['year'], $hour, $arr );
			$earnings = fes_get_earnings_by_date( $dates['day'], $month, $dates['year'], $hour, $arr );
			if ( EDD_FES()->integrations->is_commissions_active() ){
				$commissions = edd_get_commissions_by_date( $dates['day'], $month, $dates['year'], $hour, $vendor->user_id );
			}

			$sales_totals += $sales;
			$earnings_totals += $earnings;
			if ( EDD_FES()->integrations->is_commissions_active() ){
				$commissions_totals += $commissions;
			}			

			$date            = mktime( $hour, 0, 0, $month, $dates['day'], $dates['year'] ) * 1000;
			$sales_data[]    = array( $date, $sales );
			$earnings_data[] = array( $date, $earnings );
			if ( EDD_FES()->integrations->is_commissions_active() ){
				$commissions_data[] = array( $date, $commissions );
			}

			$hour++;
		endwhile;

	} else if ( $dates['range'] == 'this_week' || $dates['range'] == 'last_week' ) {

		// Day by day
		$day     = $dates['day'];
		$day_end = $dates['day_end'];
		$month   = $dates['m_start'];
		while ( $day <= $day_end ) :
			$sales = fes_get_sales_by_date( $day, $month, $dates['year'], null, $arr );
			$earnings = fes_get_earnings_by_date( $day, $month, $dates['year'], null, $arr );
			if ( EDD_FES()->integrations->is_commissions_active() ){
				$commissions = edd_get_commissions_by_date( $dates['day'], $month, $dates['year'], null, $vendor->user_id );
			}

			$sales_totals += $sales;
			$earnings_totals += $earnings;
			if ( EDD_FES()->integrations->is_commissions_active() ){
				$commissions_totals += $commissions;
			}

			$date = mktime( 0, 0, 0, $month, $day, $dates['year'] ) * 1000;
			$sales_data[] = array( $date, $sales );
			$earnings_data[] = array( $date, $earnings );
			if ( EDD_FES()->integrations->is_commissions_active() ){
				$commissions_data[] = array( $date, $commissions );
			}
			$day++;
		endwhile;

	} else {

		$y = $dates['year'];
		while( $y <= $dates['year_end'] ) :

			if ( $dates['year'] == $dates['year_end'] ) {
				$month_start = $dates['m_start'];
				$month_end   = $dates['m_end'];
			} elseif ( $y == $dates['year'] ) {
				$month_start = $dates['m_start'];
				$month_end   = 12;
			} elseif ( $y == $dates['year_end'] ) {
				$month_start = 1;
				$month_end   = $dates['m_end'];
			} else {
				$month_start = 1;
				$month_end   = 12;
			}

			$i = $month_start;
			while ( $i <= $month_end ) :

				if ( $day_by_day ) :

					if ( $i == $month_end ) {

						$num_of_days = $dates['day_end'];

					} else {

						$num_of_days = cal_days_in_month( CAL_GREGORIAN, $i, $y );

					}

					$d = $dates['day'];

					while ( $d <= $num_of_days ) :
						$sales = fes_get_sales_by_date( $d, $i, $y, null, $arr );
						$earnings = fes_get_earnings_by_date( $d, $i, $y, null, $arr  );
						if ( EDD_FES()->integrations->is_commissions_active() ){
							$commissions = edd_get_commissions_by_date( $d, $i, $y, null, $vendor->user_id );
						}
						$sales_totals += $sales;
						$earnings_totals += $earnings;
						if ( EDD_FES()->integrations->is_commissions_active() ){
							$commissions_totals += $commissions;
						}
						$date = mktime( 0, 0, 0, $i, $d, $y ) * 1000;
						$sales_data[] = array( $date, $sales );
						$earnings_data[] = array( $date, $earnings );
						if ( EDD_FES()->integrations->is_commissions_active() ){
							$commissions_data[] = array( $date, $commissions );
						}
						$d++;

					endwhile;

				else :

					$sales = fes_get_sales_by_date( null, $i, $y, null, $arr );
					$sales_totals += $sales;

					$earnings = fes_get_earnings_by_date( null, $i, $y, null, $arr );
					$earnings_totals += $earnings;

					if ( EDD_FES()->integrations->is_commissions_active() ){
						$commissions = edd_get_commissions_by_date(  null, $i, $y, null, $vendor->user_id );
						$commissions_totals += $commissions;
					}

					if ( $i == $month_end ) {

						$num_of_days = cal_days_in_month( CAL_GREGORIAN, $i, $y );

					} else {

						$num_of_days = 1;

					}

					$date = mktime( 0, 0, 0, $i, $num_of_days, $y ) * 1000;
					$sales_data[] = array( $date, $sales );
					$earnings_data[] = array( $date, $earnings );
					if ( EDD_FES()->integrations->is_commissions_active() ){
						$commissions_data[] = array( $date, $commissions );
					}
				endif;

				$i++;

			endwhile;

			$y++;
		endwhile;

	}
	$data = array(
		__( 'Earnings', 'edd_fes' ) => $earnings_data,
		__( 'Sales', 'edd_fes' )    => $sales_data
	);
	if ( EDD_FES()->integrations->is_commissions_active() ){
		$data = array(
			__( 'Earnings', 'edd_fes' ) => $earnings_data,
			__( 'Sales', 'edd_fes' )    => $sales_data,
			__( 'Commissions', 'edd_fes' ) => $commissions_data
		);
	}

	// start our own output buffer
	ob_start();
	?>
	<div id="edd-dashboard-widgets-wrap">
		<div class="metabox-holder" style="padding-top: 0;">
			<div class="postbox">
				<h3><span><?php _e('Earnings Over Time', 'edd_fes'); ?></span></h3>

				<div class="inside">
					<?php
					fes_reports_graph_controls( $vendor );
					$graph = new EDD_Graph( $data );
					$graph->set( 'x_mode', 'time' );
					$graph->set( 'multiple_y_axes', true );
					$graph->display();

					if ( 'this_month' == $dates['range'] ) {
						$estimated = fes_estimated_monthly_stats( $vendor->user_id, $arr );
					}
					?>

					<p class="edd_graph_totals"><strong><?php _e( 'Total earnings for period shown: ', 'edd_fes' ); echo edd_currency_filter( edd_format_amount( $earnings_totals ) ); ?></strong></p>
					<p class="edd_graph_totals"><strong><?php _e( 'Total sales for period shown: ', 'edd_fes' ); echo edd_format_amount( $sales_totals, false ); ?></strong></p>
					<?php if ( EDD_FES()->integrations->is_commissions_active() ){ ?>
						<p class="edd_graph_totals"><strong><?php _e( 'Total commissions for period shown: ', 'edd_fes' ); echo edd_currency_filter( edd_format_amount( $commissions_totals ) ); ?></strong></p>
					<?php } ?>

					<?php if ( 'this_month' == $dates['range'] ) : ?>
						<p class="edd_graph_totals"><strong><?php _e( 'Estimated monthly earnings: ', 'edd_fes' ); echo edd_currency_filter( edd_format_amount( $estimated['earnings'] ) ); ?></strong></p>
						<p class="edd_graph_totals"><strong><?php _e( 'Estimated monthly sales: ', 'edd_fes' ); echo edd_format_amount( $estimated['sales'], false ); ?></strong></p>
						<?php if ( EDD_FES()->integrations->is_commissions_active() ){ ?>
							<p class="edd_graph_totals"><strong><?php _e( 'Estimated monthly commissions: ', 'edd_fes' ); echo edd_currency_filter( edd_format_amount( $estimated['commissions'] ) ); ?></strong></p>
						<?php } ?>
					<?php endif; ?>

					<?php 
					/**
					 * Vendor Addtional Graph Stats.
					 *
					 * Outputs directly below the statistics
					 * FES provides on the graph on the admin
					 * vendor profiles.
					 *
					 * @since 2.3.0
					 */
					do_action( 'fes_reports_graph_additional_stats' ); ?>

				</div>
			</div>
		</div>
	</div>
	<?php
	// get output buffer contents and end our own buffer
	$output = ob_get_contents();
	ob_end_clean();

	return $output;
}

/**
 * Grabs all of the selected date info and then redirects appropriately
 *
 * Gets the dates that were requested, as well as the type of report, and
 * then redirect people to the right graph. The action this is tied to is 
 * called using the hidden field in fes_reports_graph_controls() and EDD's
 * magic EDD functions.
 *
 * @since 2.3.0
 * @access public
 *
 * @param array $data Data for graph.
 * @return void
 */
function edd_parse_fes_report_dates( $data ) {
	$get = '';
	if ( isset( $_REQUEST['id'] ) ){
		$get = $_REQUEST['id'];
	}

	$dates = edd_get_report_dates();

	wp_redirect( add_query_arg( $dates, admin_url( 'admin.php?page=fes-vendors&view=reports&id=' . $get . '&rep=earnings' ) ) ); edd_die();
}
add_action( 'edd_filter_fes_reports', 'edd_parse_fes_report_dates' );

/**
 * Vendor graph.
 *
 * Renders the vendor graphs.
 *
 * @since 2.3.0
 * @access public
 *
 * @return void
 */
function fes_reports_page() {
	$get = '';
	if ( isset( $_REQUEST['id'] ) ){
		$get = 'id=' . $_REQUEST['id'] .'&';
	}
	$current_page = admin_url( 'admin.php?page=fes-vendors&view=reports&' . $get );
	?>
	<div class="wrap">
		<?php
		/**
		 * Vendor report page top.
		 *
		 * Renders the top of the reports page
		 * on the admin vendor profiles.
		 *
		 * @since 2.3.0
		 */
		do_action( 'fes_reports_page_top' );
		/**
		 * Vendor report page content.
		 *
		 * Renders the content of the reports page
		 * on the admin vendor profiles.
		 *
		 * @since 2.3.0
		 */		
		do_action( 'fes_reports_tab_' . $active_tab );
		/**
		 * Vendor report page bottom.
		 *
		 * Renders the bottom of the reports page
		 * on the admin vendor profiles.
		 *
		 * @since 2.3.0
		 */
		do_action( 'fes_reports_page_bottom' );
		?>
	</div>
	<?php
}

/**
 * Vendor graph.
 *
 * Renders the reports vendor graphs.
 *
 * @since 2.3.0
 * @access public
 *
 * @return void
 */
function fes_reports_tab_reports() {

	if ( ! EDD_FES()->vendors->user_is_admin() ) {
		wp_die( __( 'You do not have permission to access this report', 'edd_fes'  ), __( 'Error', 'edd_fes' ), array( 'response' => 403 ) );
	}

	$current_view = 'earnings';
	/**
	 * Vendor report page view.
	 *
	 * Renders the view of the reports page
	 * on the admin vendor profiles.
	 *
	 * @since 2.3.0
	 */
	do_action( 'fes_reports_view_' . $current_view );

}
add_action( 'fes_reports_tab_reports', 'fes_reports_tab_reports' );

/**
 * Vendor earnings graph.
 *
 * Renders the vendor earnings graphs.
 *
 * @since 2.3.0
 * @access public
 *
 * @return void
 */
function fes_reports_earnings() {

	if ( ! EDD_FES()->vendors->user_is_admin() ) {
		return;
	}
	?>
	<div class="tablenav top">
		<div class="alignleft actions"><?php fes_report_views(); ?></div>
	</div>
	<?php
	fes_reports_graph();
}
add_action( 'fes_reports_view_earnings', 'fes_reports_earnings' );

/**
 * Retrieves estimated monthly earnings and sales
 *
 * This function attempts to figure out a vendor's
 * estimated monthly earnings and sales for the 
 * graphs.
 *
 * @since 2.3.0
 * @access public
 *
 * @param int $vendor User id of user graph is being made for.
 * @param array $arr Array of products.
 * @return array Estimated monthly stats.
 */
function fes_estimated_monthly_stats( $vendor, $arr ) {

	$estimated = get_transient( 'edd_estimated_monthly_stats_' . $vendor );

	if ( false === $estimated ) {

		$estimated = array(
			'earnings' => 0.00,
			'sales'    => 0.00,
		);

		$stats = new EDD_Payment_Stats;
		$to_date_earnings = 0.00;
		$to_date_sales = 0;

		foreach( $arr as $product ){
			$to_date_earnings = $to_date_earnings + $stats->get_earnings( $product, 'this_month' );
			$to_date_sales    = $to_date_sales + $stats->get_sales( $product, 'this_month' );
		}

		$current_day      = date( 'd', current_time( 'timestamp' ) );
		$current_month    = date( 'n', current_time( 'timestamp' ) );
		$current_year     = date( 'Y', current_time( 'timestamp' ) );
		$days_in_month    = cal_days_in_month( CAL_GREGORIAN, $current_month, $current_year );

		if ( EDD_FES()->integrations->is_commissions_active() ){
			$to_date_commissions = edd_get_commissions_by_date(  null, $current_month , $current_year, null, $vendor );
		}

		$estimated['earnings'] = ( $to_date_earnings / $current_day ) * $days_in_month;
		$estimated['sales']    = ( $to_date_sales / $current_day ) * $days_in_month;
		if ( EDD_FES()->integrations->is_commissions_active() ){
			$estimated['commissions']    = ( $to_date_commissions / $current_day ) * $days_in_month;
		}

		// Cache for one day
		set_transient( 'fes_estimated_monthly_stats_' . $vendor, $estimated, DAY_IN_SECONDS );
	}

	return maybe_unserialize( $estimated );
}

/**
 * Get Sales By Date.
 *
 * This function attempts to figure out
 * a vendors sales.
 *
 * @since 2.3.0
 * @access public
 *
 * @param int $day Day number.
 * @param int $month_num Month number.
 * @param int $year Year.
 * @param int $hour Hour.
 * @param array $ids Array of download ids.
 * @return int Sales amount.
 */
function fes_get_sales_by_date( $day = null, $month_num = null, $year = null, $hour = null, $ids = array() ) {
	$args = array(
		'post_type'      => 'edd_payment',
		'nopaging'       => true,
		'year'           => $year,
		'fields'         => 'ids',
		'post_status'    => array( 'publish', 'revoked' ),
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'posts_per_page' => -1,
	);

	if ( ! empty( $month_num ) ){
		$args['monthnum'] = $month_num;
	}

	if ( ! empty( $day ) ){
		$args['day'] = $day;
	}

	if ( ! empty( $hour ) ){
		$args['hour'] = $hour;
	}

	$args = apply_filters( 'fes_get_sales_by_date_args', $args  );
	$key   = md5( serialize( $args ) );
	$count = get_transient( $key, 'edd_fes' );
	if ( false === $count ) {
		$count = 0;
		$sales = new WP_Query( $args );
		foreach ( $sales->posts as $post ){
			$cart_details   = edd_get_payment_meta_cart_details( $post );
			foreach ( $cart_details as $cart_index => $download ) {
				if ( in_array( $download['id'], $ids ) ){				        
					$count = $count + $download['quantity'];
				}
			}
		}
		// Cache the results for one day
		set_transient( $key, $count, DAY_IN_SECONDS );
	}
	return $count;
}

/**
 * Get Earnings By Date.
 *
 * This function attempts to figure out
 * a vendors earnings.
 *
 * @since 2.3.0
 * @access public
 * 
 * @param int $day Day number.
 * @param int $month_num Month number.
 * @param int $year Year.
 * @param int $hour Hour.
 * @param array $ids Array of download ids.
 * @return int Earnings amount.
 */
function fes_get_earnings_by_date( $day = null, $month_num, $year = null, $hour = null, $ids = array() ) {

	$args = array(
		'post_type'      => 'edd_payment',
		'nopaging'       => true,
		'year'           => $year,
		'monthnum'       => $month_num,
		'post_status'    => array( 'publish', 'revoked' ),
		'fields'         => 'ids',
		'update_post_term_cache' => false
	);

	if ( ! empty( $day ) ){
		$args['day'] = $day;
	}

	if ( ! empty( $hour ) ){
		$args['hour'] = $hour;
	}

	$args     = apply_filters( 'fes_get_earnings_by_date_args', $args );
	$key      = md5( serialize( $args ) );
	$earnings = get_transient( $key );
	if ( false === $earnings ) {
		$earnings = 0;
		$sales = new WP_Query( $args );
		foreach ( $sales->posts as $post ){
			$cart_details   = edd_get_payment_meta_cart_details( $post );
			foreach ( $cart_details as $cart_index => $download ) {
				if ( in_array( $download['id'], $ids ) ){				        
					$earnings = $earnings + ( $download['quantity'] * $download['price'] );
				   
				}
			}
		}
		// Cache the results for one day
		set_transient( $key, $earnings, DAY_IN_SECONDS );
	}
	return round( $earnings, 2 );
}

/**
 * Vendor graph date filters.
 *
 * Makes the HTML used to filter vendor
 * graphs by a particular time period.
 *
 * @since 2.3.0
 * @access public
 * 
 * @param FES_Vendor $vendor Vendor of graph.
 * @return string HTML of graph controls.
 */
function fes_reports_graph_controls( $vendor ) {
	$date_options = apply_filters( 'fes_report_date_options', array(
		'today' 	    => __( 'Today', 'edd_fes' ),
		'yesterday'     => __( 'Yesterday', 'edd_fes' ),
		'this_week' 	=> __( 'This Week', 'edd_fes' ),
		'last_week' 	=> __( 'Last Week', 'edd_fes' ),
		'this_month' 	=> __( 'This Month', 'edd_fes' ),
		'last_month' 	=> __( 'Last Month', 'edd_fes' ),
		'this_quarter'	=> __( 'This Quarter', 'edd_fes' ),
		'last_quarter'	=> __( 'Last Quarter', 'edd_fes' ),
		'this_year'		=> __( 'This Year', 'edd_fes' ),
		'last_year'		=> __( 'Last Year', 'edd_fes' ),
		'other'			=> __( 'Custom', 'edd_fes' )
	) );
	if ( isset( $date_options['other'] ) ){
		unset( $date_options['other'] );
	}
	$dates   = edd_get_report_dates();
	$display = $dates['range'] == 'other' ? '' : 'style="display:none;"';
	$view    = edd_get_reporting_view();
	if ( empty( $dates['day_end'] ) ) {
		$dates['day_end'] = cal_days_in_month( CAL_GREGORIAN, date( 'n' ), date( 'Y' ) );
	}
	?>
	<form id="edd-graphs-filter" method="get">
		<div class="tablenav top">
			<div class="alignleft actions">

				<input type="hidden" name="post_type" value="download"/>
				<input type="hidden" name="page" value="edd-reports"/>
				<input type="hidden" name="view" value="<?php echo esc_attr( $view ); ?>"/>

				<?php if ( isset( $_GET['download-id'] ) ) : ?>
					<input type="hidden" name="download-id" value="<?php echo absint( $_GET['download-id'] ); ?>"/>
				<?php endif; ?>

				<select id="edd-graphs-date-options" name="range">
				<?php foreach ( $date_options as $key => $option ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $key, $dates['range'] ); ?>><?php echo esc_html( $option ); ?></option>
					<?php endforeach; ?>
				</select>
				<div id="edd-date-range-options" <?php echo $display; ?>>
					<span><?php _e( 'From', 'edd_fes' ); ?>&nbsp;</span>
					<select id="edd-graphs-month-start" name="m_start">
						<?php for ( $i = 1; $i <= 12; $i++ ) : ?>
							<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $dates['m_start'] ); ?>><?php echo edd_month_num_to_name( $i ); ?></option>
						<?php endfor; ?>
					</select>
					<select id="edd-graphs-day-start" name="day">
						<?php for ( $i = 1; $i <= 31; $i++ ) : ?>
							<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $dates['day'] ); ?>><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
					<select id="edd-graphs-year-start" name="year">
						<?php for ( $i = 2007; $i <= date( 'Y' ); $i++ ) : ?>
							<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $dates['year'] ); ?>><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
					<span><?php _e( 'To', 'edd_fes' ); ?>&nbsp;</span>
					<select id="edd-graphs-month-end" name="m_end">
						<?php for ( $i = 1; $i <= 12; $i++ ) : ?>
							<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $dates['m_end'] ); ?>><?php echo edd_month_num_to_name( $i ); ?></option>
						<?php endfor; ?>
					</select>
					<select id="edd-graphs-day-end" name="day_end">
						<?php for ( $i = 1; $i <= 31; $i++ ) : ?>
							<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $dates['day_end'] ); ?>><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
					<select id="edd-graphs-year-end" name="year_end">
						<?php for ( $i = 2007; $i <= date( 'Y' ); $i++ ) : ?>
						<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $dates['year_end'] ); ?>><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
				</div>

				<input type="hidden" name="edd_action" value="filter_fes_reports" />
				<input type="hidden" name="id" value="<?php echo $vendor->id; ?>" />
				<input type="submit" class="button-secondary" value="<?php _e( 'Filter', 'edd_fes' ); ?>"/>
			</div>
		</div>
	</form>
	<?php
}