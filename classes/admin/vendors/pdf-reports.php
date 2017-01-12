<?php
/**
 * PDF Report Generation Functions
 *
 * Handles graphing functionality
 * used in FES, like the vendor
 * backend profile graphs.
 *
 * @package FES
 * @subpackage Reports
 * @since 2.3.0
 *
 * @todo Move to EDD core's CSV system.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * Generate PDF Reports.
 *
 * Generates PDF report on sales and earnings
 * for all downloads for the current year.
 *
 * @since 2.3.0
 * @access public
 *
 * @param array $data Data to make PDF with.
 * @return void
 */
function fes_generate_vendor_pdf( $data ) {

	if ( ! EDD_FES()->vendors->user_is_admin() ) {
		wp_die( __( 'You do not have permission to generate PDF sales reports', 'edd_fes' ), __( 'Error', 'edd_fes' ), array( 'response' => 403 ) );
	}

	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'edd_generate_fes_pdf' ) ) {
		wp_die( __( 'Nonce verification failed', 'edd_fes' ), __( 'Error', 'edd_fes' ), array( 'response' => 403 ) );
	}
	require_once fes_plugin_dir . 'assets/lib/fpdf/fpdf.php';
	require_once fes_plugin_dir . 'assets/lib/fpdf/fes_pdf.php';

	$daterange = date_i18n( get_option( 'date_format' ), mktime( 0, 0, 0, 1, 1, date( 'Y' ) ) ) . ' ' . utf8_decode( __( 'to', 'edd_fes' ) ) . ' ' . date_i18n( get_option( 'date_format' ) );

	$pdf = new fes_pdf();
	$pdf->AddPage( 'L', 'A4' );
	$vendor = new FES_Vendor( $_REQUEST['id'] );
	$pdf->SetTitle( utf8_decode( __( 'Sales and earnings reports for ' . $vendor->name, 'edd_fes') ) );
	$pdf->SetAuthor( utf8_decode( __( 'Easy Digital Downloads', 'edd_fes' ) ) );
	$pdf->SetCreator( utf8_decode( __( 'Easy Digital Downloads', 'edd_fes' ) ) );

	$pdf->SetMargins( 8, 8, 8 );
	$pdf->SetX( 8 );

	$pdf->SetFont( 'Helvetica', '', 16 );
	$pdf->SetTextColor( 50, 50, 50 );
	$pdf->Cell( 0, 3, utf8_decode( __( 'Sales and Earnings Report for the current year for ' . $vendor->name, 'edd_fes' ) ), 0, 2, 'L', false );

	$pdf->SetFont( 'Helvetica', '', 13 );
	$pdf->Ln();
	$pdf->SetTextColor( 150, 150, 150 );
	$pdf->Cell( 0, 6, utf8_decode( __( 'Date Range: ', 'edd_fes' ) ) . $daterange, 0, 2, 'L', false );
	$pdf->Ln();
	$pdf->SetTextColor( 50, 50, 50 );
	$pdf->SetFont( 'Helvetica', '', 14 );
	$pdf->Cell( 0, 10, utf8_decode( __( 'Table View', 'edd_fes' ) ), 0, 2, 'L', false );
	$pdf->SetFont( 'Helvetica', '', 12 );

	$pdf->SetFillColor( 238, 238, 238 );
	$pdf->Cell( 70, 6, utf8_decode( __( 'Product Name', 'edd_fes' ) ), 1, 0, 'L', true );
	$pdf->Cell( 30, 6, utf8_decode( __( 'Price', 'edd_fes' ) ), 1, 0, 'L', true );
	$pdf->Cell( 50, 6, utf8_decode( __( 'Categories', 'edd_fes' ) ), 1, 0, 'L', true );
	$pdf->Cell( 50, 6, utf8_decode( __( 'Tags', 'edd_fes' ) ), 1, 0, 'L', true );
	$pdf->Cell( 45, 6, utf8_decode( __( 'Number of Sales', 'edd_fes' ) ), 1, 0, 'L', true );
	$pdf->Cell( 35, 6, utf8_decode( __( 'Earnings', 'edd_fes' ) ), 1, 1, 'L', true );
	$vendor = new FES_Vendor( $_REQUEST['id'] );
	$products = EDD_FES()->vendors->get_all_products( $vendor->user_id );
	$arr = array();

	if ( empty ( $products ) ) {
		return;
	}

	foreach( $products as $product ) {
		$arr[] = $product['ID'];
	}
	
	$year = date('Y');
	$downloads = get_posts( array( 'post_type' => 'download', 'year' => $year, 'posts_per_page' => -1, 'post__in' => $arr ) );

	if ( $downloads ):
		$pdf->SetWidths( array( 70, 30, 50, 50, 45, 35 ) );

		foreach ( $downloads as $download ):
			$pdf->SetFillColor( 255, 255, 255 );

			$title = utf8_decode( get_the_title( $download->ID ) );

			if ( edd_has_variable_prices( $download->ID ) ) {

				$prices = edd_get_variable_prices( $download->ID );

				$first = '';
				if ( isset ( $prices[0]['amount'] ) ) {
					$first = $prices[0]['amount'];
				} else {
					$first = $prices[1]['amount'];
				}
				$last = array_pop( $prices );
				$last = $last['amount'];

				if ( $first < $last ) {
					$min = $first;
					$max = $last;
				} else {
					$min = $last;
					$max = $first;
				}

				$price = html_entity_decode( edd_currency_filter( edd_format_amount( $min ) ) . ' - ' . edd_currency_filter( edd_format_amount( $max ) ) );
			} else {
				$price = html_entity_decode( edd_currency_filter( edd_get_download_price( $download->ID ) ) );
			}

			$categories = get_the_term_list( $download->ID, 'download_category', '', ', ', '' );
			$categories = $categories ? strip_tags( $categories ) : '';

			$tags = get_the_term_list( $download->ID, 'download_tag', '', ', ', '' );
			$tags = $tags ? strip_tags( $tags ) : '';

			$sales = edd_get_download_sales_stats( $download->ID );
			$amount = edd_get_download_earnings_stats( $download->ID );
			$earnings = html_entity_decode ( edd_currency_filter( edd_format_amount( $amount ) ) );

			if ( function_exists( 'iconv' ) ) {
				// Ensure characters like euro; are properly converted. See GithuB issue #472 and #1570
				$price    = iconv('UTF-8', 'windows-1252', utf8_encode( $price ) );
				$earnings = iconv('UTF-8', 'windows-1252', utf8_encode( $earnings ) );
			}

			$pdf->Row( array( $title, $price, $categories, $tags, $sales, $earnings ) );
		endforeach;
	else:
		$pdf->SetWidths( array( 280 ) );
		$title = utf8_decode( sprintf( __( 'No %s found.', 'edd_fes' ), edd_get_label_plural() ) );
		$pdf->Row( array( $title ) );
	endif;

	$pdf->Ln();
	$pdf->SetTextColor( 50, 50, 50 );
	$pdf->SetFont( 'Helvetica', '', 14 );
	$pdf->Cell( 0, 10, utf8_decode( __('Graph View', 'edd_fes') ), 0, 2, 'L', false );
	$pdf->SetFont( 'Helvetica', '', 12 );

	$image = html_entity_decode( urldecode( fes_draw_chart_image( $vendor ) ) );
	$image = str_replace( ' ', '%20', $image );

	$pdf->SetX( 25 );
	$pdf->Image( $image .'&file=.png' );
	$pdf->Ln( 7 );
	$pdf->Output( apply_filters( 'edd_sales_earnings_pdf_export_filename', 'edd-report-' . date_i18n('Y-m-d') ) . '.pdf', 'D' );
}
add_action( 'edd_generate_fes_pdf', 'fes_generate_vendor_pdf' );

/**
 * Make PDF chart.
 *
 * Creates chart used in FES pdf.
 *
 * @since 2.3.0
 * @access public
 *
 * @param FES_Vendor $vendor Vendor to make PDF for.
 * @return GoogleChart Chart to put on pdf.
 */
function fes_draw_chart_image( $vendor ) {
	require_once fes_plugin_dir . 'assets/lib/googlechartlib/GoogleChart.php';
	require_once fes_plugin_dir . 'assets/lib/googlechartlib/markers/GoogleChartShapeMarker.php';
	require_once fes_plugin_dir . 'assets/lib/googlechartlib/markers/GoogleChartTextMarker.php';

	$earnings_array    = array();
	$sales_array       = array();
	$commissions_array = array();
	$products = EDD_FES()->vendors->get_all_products( $vendor->user_id );
	$arr = array();
	if ( empty ( $products ) ){
		return;
	}
	foreach( $products as $product ){
		array_push( $arr, $product['ID'] );
	}

	$y = date( "Y" );
	$i = 0;
	while ( $i <= 12 ) :
		$sales = fes_get_sales_by_date( null, $i, $y, null, $arr );
		$earnings = fes_get_earnings_by_date( null, $i, $y, null, $arr );

		if ( EDD_FES()->integrations->is_commissions_active() ){
			$commissions = edd_get_commissions_by_date(  null, $i, $y, null, $vendor->user_id );
		}

		$sales_array[ $i ]      = $sales;
		$earnings_array[ $i ]   = $earnings;

		if ( EDD_FES()->integrations->is_commissions_active() ){
			$commissions_array[] = $commissions;
		}

	$i++;
	endwhile;

	$chart = new GoogleChart( 'lc', 900, 330 );
	$max_earnings = max( $earnings_array );

	$data = new GoogleChartData( array(
		$earnings_array[0],
		$earnings_array[1],
		$earnings_array[2],
		$earnings_array[3],
		$earnings_array[4],
		$earnings_array[5],
		$earnings_array[6],
		$earnings_array[7],
		$earnings_array[8],
		$earnings_array[9],
		$earnings_array[10],
		$earnings_array[11]
	) );
	$data->setLegend( __( 'Earnings', 'edd_fes' ) );
	$data->setColor( '1b58a3' );
	$chart->addData( $data );
	$shape_marker = new GoogleChartShapeMarker( GoogleChartShapeMarker::CIRCLE );
	$shape_marker->setColor( '000000' );
	$shape_marker->setSize( 7 );
	$shape_marker->setBorder( 2 );
	$shape_marker->setData( $data );
	$chart->addMarker( $shape_marker );
	$value_marker = new GoogleChartTextMarker( GoogleChartTextMarker::VALUE );
	$value_marker->setColor( '000000' );
	$value_marker->setData( $data );
	$chart->addMarker( $value_marker );

	if ( EDD_FES()->integrations->is_commissions_active() ){
		$data = new GoogleChartData( array(
			$commissions_array[0],
			$commissions_array[1],
			$commissions_array[2],
			$commissions_array[3],
			$commissions_array[4],
			$commissions_array[5],
			$commissions_array[6],
			$commissions_array[7],
			$commissions_array[8],
			$commissions_array[9],
			$commissions_array[10],
			$commissions_array[11]
		) );
		$data->setLegend( __( 'Commissions', 'edd_fes' ) );
		$data->setColor( 'cb4b4b' );
		$chart->addData( $data );
		$shape_marker = new GoogleChartShapeMarker( GoogleChartShapeMarker::CIRCLE );
		$shape_marker->setColor( '000000' );
		$shape_marker->setSize( 7 );
		$shape_marker->setBorder( 2 );
		$shape_marker->setData( $data );
		$chart->addMarker( $shape_marker );
		$value_marker = new GoogleChartTextMarker( GoogleChartTextMarker::VALUE );
		$value_marker->setColor( '000000' );
		$value_marker->setData( $data );
		$chart->addMarker( $value_marker );
	}

	$data = new GoogleChartData( array(
		$sales_array[0],
		$sales_array[1],
		$sales_array[2],
		$sales_array[3],
		$sales_array[4],
		$sales_array[5],
		$sales_array[6],
		$sales_array[7],
		$sales_array[8],
		$sales_array[9],
		$sales_array[10],
		$sales_array[11] 
	) );
	$data->setLegend( __( 'Sales', 'edd_fes' ) );
	$data->setColor( 'ff6c1c' );
	$chart->addData( $data );
	$chart->setTitle( sprintf( __( 'Sales, Earnings, and Commissions by Month For All %s', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = true, $uppercase = true ) ), '336699', 18 );
	$chart->setScale( 0, $max_earnings );
	$y_axis = new GoogleChartAxis( 'y' );
	$y_axis->setDrawTickMarks( true )->setLabels( array( 0, $max_earnings ) );
	$chart->addAxis( $y_axis );
	$x_axis = new GoogleChartAxis( 'x' );
	$x_axis->setTickMarks( 5 );
	$x_axis->setLabels( array(
		__('Jan', 'edd_fes'),
		__('Feb', 'edd_fes'),
		__('Mar', 'edd_fes'),
		__('Apr', 'edd_fes'),
		__('May', 'edd_fes'),
		__('June', 'edd_fes'),
		__('July', 'edd_fes'),
		__('Aug', 'edd_fes'),
		__('Sept', 'edd_fes'),
		__('Oct', 'edd_fes'),
		__('Nov', 'edd_fes'),
		__('Dec', 'edd_fes')
	) );
	$chart->addAxis( $x_axis );
	$shape_marker = new GoogleChartShapeMarker( GoogleChartShapeMarker::CIRCLE );
	$shape_marker->setSize( 6 );
	$shape_marker->setColor( '000000' );
	$shape_marker->setBorder( 2 );
	$shape_marker->setData( $data );
	$chart->addMarker( $shape_marker );
	$value_marker = new GoogleChartTextMarker( GoogleChartTextMarker::VALUE );
	$value_marker->setData( $data );
	$value_marker->setColor( '000000' );
	$chart->addMarker( $value_marker );
	return $chart->getUrl();
}
