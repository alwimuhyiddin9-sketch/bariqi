<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        DB::table('users')->updateOrInsert(
            ['username' => 'admin'],
            [
                'name' => 'Bariqi Admin',
                'email' => 'admin@bariqi.co',
                'password' => Hash::make('Bariqi2026!'),
                'phone' => '081234567890',
                'role' => 'admin',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // Settings
        DB::table('store_settings')->updateOrInsert(
            ['id' => 1],
            [
                'name' => 'Bariqi.co Store',
                'address' => 'Jl. Merdeka No. 45, Bandung, Jawa Barat',
                'phone' => '081234567890',
                'email' => 'admin@bariqi.co',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
        DB::table('app_settings')->updateOrInsert(
            ['id' => 1],
            ['updated_at' => now(), 'created_at' => now()]
        );

        // Categories
        $categories = [
            ['name' => 'Kaos', 'description' => 'Kaos basic dan graphic', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kemeja', 'description' => 'Kemeja kasual dan formal', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Hoodie', 'description' => 'Hoodie dan sweatshirt', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Jaket', 'description' => 'Jaket dan outer', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Celana', 'description' => 'Celana pendek dan panjang', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Aksesori', 'description' => 'Topi, kaos kaki, ikat pinggang', 'created_at' => now(), 'updated_at' => now()],
        ];
        $catIds = [];
        foreach ($categories as $c) {
            $existing = DB::table('categories')->where('name', $c['name'])->first();
            if ($existing) {
                $catIds[$c['name']] = $existing->id;
            } else {
                $catIds[$c['name']] = DB::table('categories')->insertGetId($c);
            }
        }

        // Products with variants
        $p1Id = DB::table('products')->insertGetId([
            'name' => 'Kaos Basic Premium', 'sku' => 'KAOS-BASIC-001', 'category_id' => $catIds['Kaos'],
            'cost_price' => 45000, 'sell_price' => 89000, 'min_stock' => 10, 'unit' => 'pcs',
            'description' => 'Kaos katun combed 30s', 'status' => 'active', 'has_variants' => true, 'stock' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $colors = ['Hitam', 'Putih', 'Navy'];
        $sizes = ['S', 'M', 'L', 'XL'];
        $vSku = 1;
        foreach ($colors as $color) {
            foreach ($sizes as $size) {
                $stock = rand(5, 35);
                $vId = DB::table('product_variants')->insertGetId([
                    'product_id' => $p1Id, 'sku' => 'KAOS-BASIC-' . str_pad($vSku, 3, '0', STR_PAD_LEFT),
                    'color' => $color, 'size' => $size, 'cost_price' => 45000, 'sell_price' => 89000, 'stock' => $stock,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                DB::table('stock_movements')->insert([
                    'product_id' => $p1Id, 'variant_id' => $vId, 'type' => 'in', 'quantity' => $stock,
                    'stock_before' => 0, 'stock_after' => $stock, 'reason' => 'Pembelian stok awal', 'reference' => 'INIT-STOCK',
                    'movement_date' => now(), 'created_at' => now(), 'updated_at' => now(),
                ]);
                $vSku++;
            }
        }

        // Product without variants - Hoodie
        $p2Id = DB::table('products')->insertGetId([
            'name' => 'Hoodie Oversize Cream', 'sku' => 'HOODIE-CREAM-001', 'category_id' => $catIds['Hoodie'],
            'cost_price' => 95000, 'sell_price' => 185000, 'min_stock' => 5, 'unit' => 'pcs',
            'description' => 'Hoodie oversize bahan fleece', 'status' => 'active', 'has_variants' => false, 'stock' => 25,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('stock_movements')->insert([
            'product_id' => $p2Id, 'variant_id' => null, 'type' => 'in', 'quantity' => 25,
            'stock_before' => 0, 'stock_after' => 25, 'reason' => 'Pembelian stok awal', 'reference' => 'INIT-STOCK',
            'movement_date' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        // Topi - low stock
        $p3Id = DB::table('products')->insertGetId([
            'name' => 'Topi Bucket Hitam', 'sku' => 'TOPI-BUCKET-001', 'category_id' => $catIds['Aksesori'],
            'cost_price' => 25000, 'sell_price' => 59000, 'min_stock' => 5, 'unit' => 'pcs',
            'description' => 'Topi bucket parasut', 'status' => 'active', 'has_variants' => false, 'stock' => 3,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('stock_movements')->insert([
            'product_id' => $p3Id, 'variant_id' => null, 'type' => 'in', 'quantity' => 3,
            'stock_before' => 0, 'stock_after' => 3, 'reason' => 'Pembelian stok awal', 'reference' => 'INIT-STOCK',
            'movement_date' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        // Expenses
        $expenses = [
            ['name' => 'Tagihan listrik bulanan', 'category' => 'listrik', 'amount' => 450000, 'date' => now()->subDays(5)],
            ['name' => 'Sewa kiosk', 'category' => 'sewa', 'amount' => 2500000, 'date' => now()->subDays(10)],
            ['name' => 'Bensin pengiriman', 'category' => 'transportasi', 'amount' => 80000, 'date' => now()->subDays(2)],
        ];
        foreach ($expenses as $e) {
            DB::table('expenses')->insert(array_merge($e, ['created_at' => now(), 'updated_at' => now()]));
        }

        echo "Seed selesai. Login: admin / Bariqi2026!\n";
    }
}
