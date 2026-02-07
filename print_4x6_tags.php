<?php
require($_SERVER['DOCUMENT_ROOT'] . '/fpdf/fpdf.php');
require_once "../inc/i_common.php";  // Sets up database; Defines TABLEPREFIX &

// sanitize the inputs
$selected_toppings = filter_input(
	INPUT_POST,
	'tag',
	FILTER_SANITIZE_STRING,
	FILTER_REQUIRE_ARRAY
) ?? [];


// SITENAME
define('WP_DEBUG', true);
//$atumTable  = $wpdb->prefix . 'atum_product_data';


function getProduct($id)
{
	$product = wc_get_product($id);
	$aProd = array();
	//$id = $product->get_id();// The product ID
	$aProd['ID'] = $id;
	$aProd['description'] = wc_get_product($id)->get_description();
	$aProd['short_description'] = wc_get_product($id)->get_short_description();
	//$aProds[$id]['product_cost'] = number_format( getCost( $id ), 2 );
	$aProd['title'] = $product->get_title(); // The product name
	//$aProd['qty'] = $product->get_stock_quantity(); //
	$aProd['price'] = $product->get_price(); // The product price
	$aProd['regular_price'] = $product->get_regular_price();
	$aProd['sale_price'] =  $product->get_sale_price();
	//$aProd['description'] = $product->get_description();
	//$aProd['short_description'] = $product->get_short_description();
	//$aProd['cost'] = ($show_cost) ? getCost($id) : '--'; // The product cost
	//$aProd['permalink'] = $product->get_permalink(); // Product permalink.
	//$aProd = $product->get_short_description();
	//$aSQL['sku'] = $product->get_sku(); // The product SKU
	//$atts = array('brand', 'condition');
	//list($brand, $condition) = getAtts($id , $atts);
	//$aSQL['excerpt'] = $product->get_short_description(); // excerpt
	//$aSQL['description'] = $product->get_description();
	//$aSQL['stock_status'] = $product->get_stock_status();
	//$aSQL['brand']   = $brand;
	//$aSQL['condition']   = $condition;
	//$aSQL['category']   = cs_getParentCategory($id);
	//$aSQL['gallery'] = getGalleryImgs($id);
	//$aSQL['featured_img'] = get_the_post_thumbnail_url( $id, 'medium' );
	//$aSQL['prod_cat'] = cs_get_product_cats($id,1);
	//$aSQL['sale_price'] = $product->get_sale_price(); //

	//$aSQL['display'] = $product->get_catalog_visibility();// cat visibility.
	//$image_url       = wp_get_attachment_image_src( $product->get_image_id() )
	//$aSQL['shipping_class'] = $product->get_shipping_class();
	return $aProd;
}
// 101.6 x 152.4
setlocale(LC_CTYPE, 'en_US');

// Create PDF
$pdf = new FPDF('P', 'mm', array(100, 150));
define('PAGE_WIDTH', 100.0);
define('PAGE_HEIGHT', 150);
define('BORDER_THICKNESS', 0.5);
$biggerFont = 18;
$bigFont = 12;
$smFont = 10;
$pdf->SetAutoPageBreak(false);
$pdf->SetMargins(3, 3, 3);
foreach ($_POST['tag'] as $id) {
	$aProd = getProduct($id);
	$pdf->AddPage();
	$pdf->Image('../tags/Price Tag.png', 0, 0, PAGE_WIDTH, PAGE_HEIGHT);
	// Draw border
	$pdf->SetDrawColor(0, 0, 0);
	$pdf->Rect(BORDER_THICKNESS, BORDER_THICKNESS, PAGE_WIDTH - 2 * BORDER_THICKNESS, PAGE_HEIGHT - 2 * BORDER_THICKNESS, 'D');

	// Header
	$pdf->SetFillColor(0, 0, 0);
	//$pdf->Image('imgs/logo.png', 3, 2, 8, 8);
	//$pdf->SetY(2);
	//$pdf->SetFont('Arial', 'B', $bigFont);
	//$pdf->SetTextColor(0, 0, 0);
	//$txt = '#' . $id;
	//$pdf->Cell(0, 10, $txt, 0, 0, 'R'); // Stock #
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFont('Arial', 'B', $bigFont);
	$pdf->SetY(40, true);
	$txt = $aProd['title']; // Heading
	$txt = iconv('UTF-8', 'ASCII//TRANSLIT', $txt); // Must use if showing quotes!
	$pdf->MultiCell(0, 6, $txt, '', 'C', 0);
	$pdf->Ln(5);
	$noBorder = 0;
	$cursorGoToBeginningNextLine = 1; // 0 = right, 2 = below
	$alignLeft = 'L'; // C, R
	$fill = false; // default
	$pdf->SetFont('Arial', '', $smFont);
	// Our preference is short_description, but...
	if (!empty($aProd['short_description']) && strlen(trim($aProd['short_description'])) > 10) {
		$txt = strip_tags($aProd['short_description']);
	} else {
		$txt = strip_tags($aProd['description']);
	}
	// if the dumb humans haven't limited the text, then we do it here...
	if (strlen($txt) > 600) {
		$txt = substr($txt, 0, 600) . '...';
	}
	if (!mb_check_encoding($txt, 'UTF-8')) {
		$txt = utf8_encode($txt);
	}

	$txt = iconv('UTF-8', 'ASCII//TRANSLIT', $txt); // Must use if showing quotes!

	$pdf->MultiCell(0, 4, $txt, '', 'L', 0);
	// Footer
	$pdf->SetY(-30);
	$pdf->SetFont('Arial', 'B', $smFont);
	$txt = 'Retail: $' . $aProd['regular_price'];
	$pdf->Cell(0, 10, $txt, 0, 0, 'L');
	$pdf->SetFont('Arial', 'BI', $biggerFont);
	$pdf->SetTextColor(255, 0, 0);
	$txt = 'Sale: $' . $aProd['sale_price'];
	$pdf->Cell(0, 10, $txt, 0, 0, 'R');
	$pdf->SetFont('Arial', 'B', $bigFont);
	$pdf->SetTextColor(0, 0, 0);
	$txt = '#' . $id;
	// Calculate the width of the text
	$textWidth = $pdf->GetStringWidth($txt);

	// Calculate X position to center the text
	$x = (100 - $textWidth) / 2; // Page width is 100mm

	// Set Y position near the bottom of the page
	$y = 150 - 10; // Page height is 150mm, adjust for margin (10mm)

	// Place the text
	$pdf->SetXY($x, $y);
	$pdf->Cell($textWidth, 10, $txt, 0, 0, 'C'); // No border, no line break, centered text
}
// Output PDF
$pdf->Output();
