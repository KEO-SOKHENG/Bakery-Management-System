<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add high-impact performance indexes justified by the database performance audit.
     */
    public function up(): void
    {
        // 1. Orders table indexes
        Schema::table('orders', function (Blueprint $table) {
            $table->index('order_status', 'idx_orders_status');
            $table->index('created_at', 'idx_orders_created_at');
            $table->index('customer_id', 'idx_orders_customer_id');
            $table->index('user_id', 'idx_orders_user_id');
            $table->index(['is_custom', 'order_status', 'pickup_date'], 'idx_orders_custom_status_pickup');
        });

        // 2. Order items table indexes
        Schema::table('order_items', function (Blueprint $table) {
            $table->index('order_id', 'idx_order_items_order_id');
            $table->index('product_id', 'idx_order_items_product_id');
        });

        // 3. Sales table indexes
        Schema::table('sales', function (Blueprint $table) {
            $table->index('sold_at', 'idx_sales_sold_at');
            $table->index('order_id', 'idx_sales_order_id');
            $table->index('user_id', 'idx_sales_user_id');
            $table->index('payment_method', 'idx_sales_payment_method');
        });

        // 4. Productions table indexes
        Schema::table('productions', function (Blueprint $table) {
            $table->index('status', 'idx_productions_status');
            $table->index('product_id', 'idx_productions_product_id');
            $table->index('baker_id', 'idx_productions_baker_id');
            $table->index('scheduled_at', 'idx_productions_scheduled_at');
        });

        // 5. Stock movements table indexes
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index('ingredient_id', 'idx_stock_movements_ingredient_id');
            $table->index('created_at', 'idx_stock_movements_created_at');
        });

        // 6. Purchase orders table indexes
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->index('status', 'idx_purchase_orders_status');
            $table->index('order_date', 'idx_purchase_orders_order_date');
            $table->index('supplier_id', 'idx_purchase_orders_supplier_id');
        });

        // 7. Purchase order items table indexes
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->index('purchase_order_id', 'idx_purchase_order_items_po_id');
            $table->index('ingredient_id', 'idx_purchase_order_items_ingredient_id');
        });

        // 8. Ingredients table indexes
        Schema::table('ingredients', function (Blueprint $table) {
            $table->index('status', 'idx_ingredients_status');
            $table->index('expiry_date', 'idx_ingredients_expiry_date');
        });

        // 9. Products table indexes
        Schema::table('products', function (Blueprint $table) {
            $table->index('category_id', 'idx_products_category_id');
            $table->index('status', 'idx_products_status');
        });

        // 10. Audit logs table index
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index('user_id', 'idx_audit_logs_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_audit_logs_user_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_category_id');
            $table->dropIndex('idx_products_status');
        });

        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropIndex('idx_ingredients_status');
            $table->dropIndex('idx_ingredients_expiry_date');
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropIndex('idx_purchase_order_items_po_id');
            $table->dropIndex('idx_purchase_order_items_ingredient_id');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex('idx_purchase_orders_status');
            $table->dropIndex('idx_purchase_orders_order_date');
            $table->dropIndex('idx_purchase_orders_supplier_id');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('idx_stock_movements_ingredient_id');
            $table->dropIndex('idx_stock_movements_created_at');
        });

        Schema::table('productions', function (Blueprint $table) {
            $table->dropIndex('idx_productions_status');
            $table->dropIndex('idx_productions_product_id');
            $table->dropIndex('idx_productions_baker_id');
            $table->dropIndex('idx_productions_scheduled_at');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('idx_sales_sold_at');
            $table->dropIndex('idx_sales_order_id');
            $table->dropIndex('idx_sales_user_id');
            $table->dropIndex('idx_sales_payment_method');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('idx_order_items_order_id');
            $table->dropIndex('idx_order_items_product_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_status');
            $table->dropIndex('idx_orders_created_at');
            $table->dropIndex('idx_orders_customer_id');
            $table->dropIndex('idx_orders_user_id');
            $table->dropIndex('idx_orders_custom_status_pickup');
        });
    }
};
