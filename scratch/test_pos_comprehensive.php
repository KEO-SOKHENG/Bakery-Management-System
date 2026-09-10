<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$passed = 0;
$failed = 0;

function assertTest($description, $condition) {
    global $passed, $failed;
    if ($condition) {
        echo "[PASS] $description\n";
        $passed++;
    } else {
        echo "[FAIL] $description\n";
        $failed++;
    }
}

echo "=======================================================\n";
echo "   POS & SALES MODULE COMPREHENSIVE VERIFICATION SUITE\n";
echo "=======================================================\n\n";

// 1. Check Test Users
$admin = User::where('role', 'admin')->first();
$manager = User::where('role', 'manager')->first();
$cashier = User::where('role', 'cashier')->first();

assertTest("Admin user exists", $admin !== null);
assertTest("Manager user exists", $manager !== null);
assertTest("Cashier user exists", $cashier !== null);

// 2. Check Controller Instantiation & View Data
$posController = new \App\Http\Controllers\PosController();
$viewResponse = $posController->index();
$viewData = $viewResponse->getData();

assertTest("PosController@index returns view 'pos.index'", $viewResponse->name() === 'pos.index');
assertTest("View has 'products' collection", isset($viewData['products']) && $viewData['products']->count() > 0);
assertTest("View has 'categories' collection", isset($viewData['categories']) && $viewData['categories']->count() > 0);
assertTest("View has 'taxPercentage'", isset($viewData['taxPercentage']));
assertTest("View has 'currency'", isset($viewData['currency']));
assertTest("View has 'bakeryName'", isset($viewData['bakeryName']));

// 3. Check Cashier Dashboard Controller
$cashierDashboardController = new \App\Http\Controllers\Cashier\DashboardController();
$cashierView = $cashierDashboardController->index();
assertTest("Cashier Dashboard returns pos.index", $cashierView->name() === 'pos.index');

// 4. Test POS Checkout - Happy Path with Cashier Auth
Auth::login($cashier);

// Prepare a test product
$testProduct = Product::where('status', 'active')->where('stock', '>=', 5)->first();
if (!$testProduct) {
    // Create or top up for testing
    $testProduct = Product::first();
    $testProduct->stock = 20;
    $testProduct->save();
}

$initialStock = $testProduct->stock;
$purchaseQty = 2;
$unitPrice = (float) $testProduct->price;
$expectedSubtotal = round($unitPrice * $purchaseQty, 2);
$discount = 1.50;
$taxPercentage = (float) Setting::get('tax_percentage', Setting::get('tax_rate', 10.0));
$expectedTaxable = max(0, $expectedSubtotal - $discount);
$expectedTax = round($expectedTaxable * ($taxPercentage / 100.0), 2);
$expectedGrandTotal = round($expectedTaxable + $expectedTax, 2);

$checkoutRequest = Request::create('/pos/checkout', 'POST', [
    'customer_name'  => 'John Doe Test',
    'payment_method' => 'cash',
    'discount'       => $discount,
    'items'          => [
        [
            'product_id' => $testProduct->id,
            'quantity'   => $purchaseQty,
        ]
    ]
]);

$response = $posController->checkout($checkoutRequest);
$responseData = json_decode($response->getContent(), true);

assertTest("Checkout HTTP status is 200", $response->getStatusCode() === 200);
assertTest("Checkout response success is true", isset($responseData['success']) && $responseData['success'] === true);
assertTest("Response contains receipt object", isset($responseData['receipt']));

// Verify Stock Deduction
$testProduct->refresh();
assertTest("Stock was deducted accurately (initial: $initialStock, new: {$testProduct->stock})", $testProduct->stock === ($initialStock - $purchaseQty));

// Verify DB Records
$orderNum = $responseData['receipt']['order_number'];
$order = Order::where('order_number', $orderNum)->first();
assertTest("Order record created in DB", $order !== null);
assertTest("Order customer_name is 'John Doe Test'", $order && $order->customer_name === 'John Doe Test');
assertTest("Order subtotal matches expected ($expectedSubtotal)", $order && abs($order->subtotal - $expectedSubtotal) < 0.01);
assertTest("Order discount matches expected ($discount)", $order && abs($order->discount - $discount) < 0.01);
assertTest("Order tax matches expected ($expectedTax)", $order && abs($order->tax - $expectedTax) < 0.01);
assertTest("Order total matches expected ($expectedGrandTotal)", $order && abs($order->total - $expectedGrandTotal) < 0.01);
assertTest("Order status is 'completed'", $order && $order->order_status === 'completed');
assertTest("Order payment_status is 'paid'", $order && $order->payment_status === 'paid');

// Verify Order Items
$orderItem = OrderItem::where('order_id', $order->id)->where('product_id', $testProduct->id)->first();
assertTest("OrderItem record created in DB", $orderItem !== null);
assertTest("OrderItem quantity is $purchaseQty", $orderItem && $orderItem->quantity === $purchaseQty);
assertTest("OrderItem subtotal matches line total ($expectedSubtotal)", $orderItem && abs($orderItem->subtotal - $expectedSubtotal) < 0.01);

// Verify Sale record
$sale = Sale::where('order_id', $order->id)->first();
assertTest("Sale record created in DB", $sale !== null);
assertTest("Sale total matches grand total", $sale && abs($sale->total - $expectedGrandTotal) < 0.01);
assertTest("Sale user_id matches Cashier ID ({$cashier->id})", $sale && $sale->user_id === $cashier->id);

// Verify Payment record
$payment = Payment::where('order_id', $order->id)->first();
assertTest("Payment record created in DB", $payment !== null);
assertTest("Payment amount matches grand total", $payment && abs($payment->amount - $expectedGrandTotal) < 0.01);
assertTest("Payment method is 'cash'", $payment && $payment->payment_method === 'cash');
assertTest("Payment status is 'completed'", $payment && $payment->payment_status === 'completed');

// 5. Test Insufficient Stock Handling & Rollback
$insufficientQty = $testProduct->stock + 9999;
$stockBeforeFailed = $testProduct->stock;

$failedRequest = Request::create('/pos/checkout', 'POST', [
    'customer_name'  => 'Fail Customer',
    'payment_method' => 'qr_code',
    'items'          => [
        [
            'product_id' => $testProduct->id,
            'quantity'   => $insufficientQty,
        ]
    ]
]);

try {
    $failedResponse = $posController->checkout($failedRequest);
    $failedData = json_decode($failedResponse->getContent(), true);
    assertTest("Insufficient stock returns 422 error code", $failedResponse->getStatusCode() === 422);
    assertTest("Insufficient stock returns success=false", $failedData['success'] === false);
    assertTest("Error message mentions Insufficient stock", strpos($failedData['message'], 'Insufficient stock') !== false);
} catch (\Illuminate\Validation\ValidationException $ve) {
    assertTest("Validation caught error", true);
}

$testProduct->refresh();
assertTest("Stock was NOT deducted on failure (remains $stockBeforeFailed)", $testProduct->stock === $stockBeforeFailed);

// 6. Test Payment Method: KHQR
$qrRequest = Request::create('/pos/checkout', 'POST', [
    'customer_name'  => 'QR Customer',
    'payment_method' => 'qr_code',
    'items'          => [
        [
            'product_id' => $testProduct->id,
            'quantity'   => 1,
        ]
    ]
]);
$qrResponse = $posController->checkout($qrRequest);
$qrData = json_decode($qrResponse->getContent(), true);
assertTest("KHQR payment succeeds", $qrResponse->getStatusCode() === 200 && $qrData['receipt']['payment_method'] === 'KHQR');

// 7. Test Payment Method: Card
$cardRequest = Request::create('/pos/checkout', 'POST', [
    'customer_name'  => 'Card Customer',
    'payment_method' => 'card',
    'items'          => [
        [
            'product_id' => $testProduct->id,
            'quantity'   => 1,
        ]
    ]
]);
$cardResponse = $posController->checkout($cardRequest);
$cardData = json_decode($cardResponse->getContent(), true);
assertTest("Card payment succeeds", $cardResponse->getStatusCode() === 200 && $cardData['receipt']['payment_method'] === 'CARD');

echo "\n=======================================================\n";
echo "SUMMARY: $passed Passed, $failed Failed\n";
echo "=======================================================\n";
exit($failed === 0 ? 0 : 1);
