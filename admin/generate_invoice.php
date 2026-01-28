<?php
session_start();
include('../db_config.php');

if (!isset($_SESSION['admin_email'])) {
    header("location: index.php");
    exit();
}

$root = dirname(__DIR__);
$paths = [
    $root . DIRECTORY_SEPARATOR . 'dompdf' . DIRECTORY_SEPARATOR . 'autoload.inc.php',
    $root . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php'
];

$found = false;
foreach ($paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $found = true;
        break;
    }
}
if (!$found) {
    $checked_paths = implode("<br>", array_map('htmlspecialchars', $paths));
    
    $extra_info = "";
    $dompdf_dir = $root . DIRECTORY_SEPARATOR . 'dompdf';
    if (is_dir($dompdf_dir)) {
        if (!file_exists($dompdf_dir . DIRECTORY_SEPARATOR . 'autoload.inc.php')) {
            $extra_info = "<div style='margin-top:15px; color: #856404; background: #fff3cd; padding: 15px; border-radius: 6px; border: 1px solid #ffeeba;'>
                <strong>Diagnostic:</strong> The <code>dompdf</code> folder was found, but <code>autoload.inc.php</code> is missing. <br>
                This confirms you downloaded the <strong>Source Code</strong>. Please download the <strong>Packaged Release</strong> instead.
            </div>";
        }
    }

    die("
        <div style='font-family: sans-serif; padding: 30px; border: 2px solid #ff3b30; background: #fff5f5; color: #d32f2f; border-radius: 12px; max-width: 700px; margin: 50px auto; box-shadow: 0 10px 30px rgba(0,0,0,0.1);'>
            <h2 style='margin-top: 0; color: #ff3b30;'>Dompdf Library Not Found</h2>
            <p>The invoice generator could not find the required autoloader file at these locations:</p>
            <div style='background: #eee; padding: 15px; border-radius: 6px; font-family: monospace; font-size: 13px; line-height: 1.6; color: #333;'>$checked_paths</div>
            $extra_info
            <p style='margin-top: 20px;'><strong>How to fix this:</strong></p>
            <ol style='line-height: 1.8;'>
                <li>Go to the <a href='https://github.com/dompdf/dompdf/releases' target='_blank' style='color: #009444; font-weight: bold;'>Dompdf Releases Page</a>.</li>
                <li>Download the <strong>packaged release</strong> (e.g., <code>dompdf_3-0-1.zip</code>). <br><span style='color: #666; font-size: 0.9em;'><em>Note: Do NOT download 'Source code (zip)', as it is missing required files.</em></span></li>
                <li>Extract the zip and rename the folder to exactly <code>dompdf</code>.</li>
                <li>Place that folder inside: <code>D:\\xampp\\htdocs\\Zyroid\\</code></li>
                <li>Ensure the file <code>D:\\xampp\\htdocs\\Zyroid\\dompdf\\autoload.inc.php</code> exists.</li>
            </ol>
            <button onclick='window.location.reload()' style='background: #009444; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; margin-top: 10px;'>I've fixed it, reload page</button>
        </div>
    ");
}

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_GET['id'])) {
    die("Order ID is required.");
}

$order_id = mysqli_real_escape_string($con, $_GET['id']);

$query = "SELECT o.*, u.user as customer_name, u.phone as customer_phone, u.email as customer_email 
          FROM tb_orders o 
          LEFT JOIN tb_users u ON o.email = u.email 
          WHERE o.order_id = '$order_id'";
$result = mysqli_query($con, $query);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    die("Order not found.");
}

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: "Helvetica", sans-serif; color: #333; margin: 0; padding: 0; }
        .invoice-container { padding: 40px; }
        .header { border-bottom: 3px solid #009444; padding-bottom: 20px; margin-bottom: 30px; }
        .header table { width: 100%; }
        .brand { font-size: 32px; font-weight: bold; color: #009444; }
        .invoice-meta { text-align: right; font-size: 14px; }
        .info-section { width: 100%; margin-bottom: 40px; }
        .info-section td { width: 50%; vertical-align: top; }
        .label { font-weight: bold; color: #009444; text-transform: uppercase; font-size: 12px; margin-bottom: 5px; display: block; border-bottom: 1px solid #eee; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { background: #f4f4f4; padding: 12px; text-align: left; border-bottom: 2px solid #eee; font-size: 13px; }
        .items-table td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; }
        .total-section { text-align: right; }
        .total-amount { font-size: 20px; font-weight: bold; color: #000; }
        .footer { margin-top: 60px; text-align: center; font-size: 11px; color: #777; border-top: 1px solid #eee; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header">
            <table>
                <tr>
                    <td class="brand">ZYROID.</td>
                    <td class="invoice-meta">
                        <strong>INVOICE</strong><br>
                        Order ID: #' . $order['order_id'] . '<br>
                        Date: ' . date("d M Y", strtotime($order['Date'])) . '
                    </td>
                </tr>
            </table>
        </div>

        <table class="info-section">
            <tr>
                <td>
                    <span class="label">Customer Details</span>
                    <strong>' . htmlspecialchars($order['customer_name']) . '</strong><br>
                    ' . htmlspecialchars($order['customer_email']) . '<br>
                    ' . htmlspecialchars($order['customer_phone']) . '
                </td>
                <td>';
if (!empty($order['shipping_address'])) {
    $html .= '
                    <span class="label">Shipping Address</span>
                    <strong>' . htmlspecialchars($order['shipping_name']) . '</strong><br>
                    ' . htmlspecialchars($order['shipping_address']) . '<br>
                    ' . htmlspecialchars($order['shipping_city']) . ', ' . htmlspecialchars($order['shipping_state']) . ' ' . htmlspecialchars($order['shipping_zip']);
}
$html .= '
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Product Description</th>
                    <th style="text-align: right;">Price</th>
                </tr>
            </thead>
            <tbody>';

$products = explode(',', $order['Product']);
foreach ($products as $product) {
    $p_name = trim($product);
    $p_name_esc = mysqli_real_escape_string($con, $p_name);
    $p_res = mysqli_query($con, "SELECT product_price FROM tb_products WHERE product_name = '$p_name_esc'");
    $p_data = mysqli_fetch_assoc($p_res);
    $price = $p_data ? '$' . number_format($p_data['product_price'], 2) : '-';
    $html .= '
                <tr>
                    <td>' . htmlspecialchars($p_name) . '</td>
                    <td style="text-align: right;">' . $price . '</td>
                </tr>';
}

$html .= '
            </tbody>
        </table>

        <div class="total-section">
            <span style="font-size: 14px; color: #555;">Grand Total:</span><br>
            <span class="total-amount">$' . number_format($order['amount'], 2) . '</span>
        </div>

        <div class="footer">
            This is a computer-generated document. No signature is required.<br>
            <strong>Zyroid Mobiles</strong> | support@zyroid.com | www.zyroid.com
        </div>
    </div>
</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream("Zyroid_Invoice_" . $order['order_id'] . ".pdf", array("Attachment" => 1));
exit();