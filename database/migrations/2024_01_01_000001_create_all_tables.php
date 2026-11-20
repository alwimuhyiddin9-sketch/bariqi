<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Users (admin tunggal)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('role')->default('admin');
            $table->timestamps();
        });

        // Store settings
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Bariqi.co Store');
            $table->string('logo')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        // App settings
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('language')->default('id');
            $table->string('currency')->default('IDR');
            $table->string('date_format')->default('DD/MM/YYYY');
            $table->string('theme')->default('light');
            $table->integer('min_stock_limit')->default(5);
            $table->string('trx_prefix')->default('TRX');
            $table->string('footer_note')->default('Terima kasih telah berbelanja!');
            $table->timestamps();
        });

        // Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Products
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->foreignId('category_id')->constrained();
            $table->string('image')->nullable();
            $table->integer('cost_price')->default(0);
            $table->integer('sell_price')->default(0);
            $table->integer('min_stock')->default(5);
            $table->string('unit')->default('pcs');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->boolean('has_variants')->default(false);
            $table->integer('stock')->default(0);
            $table->timestamps();
            $table->index('category_id');
            $table->index('status');
        });

        // Product variants
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('sku')->unique();
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->integer('cost_price')->default(0);
            $table->integer('sell_price')->default(0);
            $table->integer('stock')->default(0);
            $table->timestamps();
            $table->index('product_id');
        });

        // Sales
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->string('external_order')->nullable();
            $table->string('channel');
            $table->string('customer_name')->nullable();
            $table->dateTime('order_date');
            $table->integer('subtotal')->default(0);
            $table->integer('discount')->default(0);
            $table->integer('additional_fee')->default(0);
            $table->integer('total')->default(0);
            $table->integer('cost_total')->default(0);
            $table->string('payment_status')->default('unpaid');
            $table->string('order_status')->default('pending');
            $table->string('payment_method')->nullable();
            $table->integer('amount_paid')->nullable();
            $table->integer('change')->nullable();
            $table->text('note')->nullable();
            $table->boolean('stock_reserved')->default(false);
            $table->timestamps();
            $table->index('channel');
            $table->index('order_date');
            $table->index('order_status');
            $table->index('payment_status');
        });

        // Sale items
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
            $table->foreignId('variant_id')->nullable();
            $table->string('product_name');
            $table->string('variant_info')->nullable();
            $table->integer('quantity');
            $table->integer('cost_price');
            $table->integer('sell_price');
            $table->integer('subtotal');
            $table->timestamps();
            $table->index('sale_id');
            $table->index('product_id');
        });

        // Stock movements
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('variant_id')->nullable();
            $table->string('type');
            $table->integer('quantity');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->string('reason');
            $table->string('reference')->nullable();
            $table->dateTime('movement_date');
            $table->timestamps();
            $table->index('product_id');
            $table->index('movement_date');
            $table->index('type');
        });

        // Expenses
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date');
            $table->string('name');
            $table->string('category');
            $table->integer('amount');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index('date');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('app_settings');
        Schema::dropIfExists('store_settings');
        Schema::dropIfExists('users');
    }
};
