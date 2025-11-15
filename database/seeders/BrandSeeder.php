<?php
namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $eticaret = Category::where('name', 'E-Ticaret')->first();
        $elektronik = Category::where('name', 'Elektronik')->first();
        $finans = Category::where('name', 'Finans')->first();

        Brand::create([
            'name' => 'Trendyol',
            'slug' => 'trendyol',
            'description' => 'Türkiye\'nin en büyük e-ticaret platformu',
            'category_id' => $eticaret->id,
            'website' => 'https://www.trendyol.com',
            'email' => 'destek@trendyol.com',
            'phone' => '0216 555 0000',
            'city' => 'İstanbul',
            'facebook' => 'trendyol',
            'twitter' => 'trendyolcom',
            'instagram' => 'trendyol',
            'subscription_type' => 'enterprise',
            'subscription_start' => Carbon::now(),
            'subscription_end' => Carbon::now()->addYear(),
            'status' => 'approved',
            'is_active' => true,
            'is_featured' => true,
        ]);

        Brand::create([
            'name' => 'Hepsiburada',
            'slug' => 'hepsiburada',
            'description' => 'Online alışveriş merkezi',
            'category_id' => $eticaret->id,
            'website' => 'https://www.hepsiburada.com',
            'email' => 'info@hepsiburada.com',
            'phone' => '0212 555 0000',
            'city' => 'İstanbul',
            'facebook' => 'hepsiburada',
            'twitter' => 'hepsiburada',
            'instagram' => 'hepsiburada',
            'subscription_type' => 'premium',
            'status' => 'approved',
            'is_active' => true,
            'is_featured' => true,
        ]);

        Brand::create([
            'name' => 'Samsung',
            'slug' => 'samsung',
            'description' => 'Elektronik ürünleri ve gereçleri',
            'category_id' => $elektronik->id,
            'website' => 'https://www.samsung.com/tr',
            'email' => 'support@samsung.com',
            'city' => 'Istanbul',
            'subscription_type' => 'premium',
            'status' => 'approved',
            'is_active' => true,
        ]);

        Brand::create([
            'name' => 'Garanti BBVA',
            'slug' => 'garanti-bbva',
            'description' => 'Türkiye\'nin önde gelen bankalarından biri',
            'category_id' => $finans->id,
            'website' => 'https://www.garantibbva.com.tr',
            'email' => 'iletisim@garanti.com.tr',
            'phone' => '0216 555 3333',
            'city' => 'Istanbul',
            'subscription_type' => 'basic',
            'status' => 'approved',
            'is_active' => true,
        ]);
    }
}
