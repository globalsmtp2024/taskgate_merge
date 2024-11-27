<?php

namespace Modules\Purchase\Database\Seeders;

use App\Models\Product;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Purchase\Entities\PurchaseInventory;
use Modules\Purchase\Entities\PurchaseStockAdjustment;

class InventoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run($companyId)
    {
        $faker = Factory::create();
        $products = Product::inRandomOrder()->where('company_id','$companyId')->limit(2)->get();


        for ($i = 0; $i < 5; $i++) {
            $inventory = new PurchaseInventory();
            $inventory->company_id = $companyId;
            $inventory->type = $faker->randomElement(['quantity', 'value']);
            $inventory->date = now()->format('Y-m-d');
            $inventory->save();

            foreach($products as $product)
            {
                $purchaseStockAdjustment = new PurchaseStockAdjustment();
                $purchaseStockAdjustment->company_id = $companyId;
                $purchaseStockAdjustment->inventory_id = $inventory->id;
                $purchaseStockAdjustment->product_id = $product->id;
                $purchaseStockAdjustment->type = $faker->randomElement(['quantity', 'value']);
                $purchaseStockAdjustment->date = now()->format('Y-m-d');
                $purchaseStockAdjustment->net_quantity = rand(50, 100);
                $purchaseStockAdjustment->status = $faker->randomElement(['draft', 'converted']);
                $purchaseStockAdjustment->save();
            }

        }
    }
    
}
