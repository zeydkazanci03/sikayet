<?php
namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // E-Ticaret
        $eticaret = Category::create([
            'name' => 'E-Ticaret',
            'description' => 'Online alışveriş ve dijital ticaret hizmetleri',
            'icon' => 'fa-shopping-bag',
            'color' => '#FF6B6B',
            'order' => 1,
        ]);

        Category::create([
            'name' => 'Kargo ve Teslimat',
            'parent_id' => $eticaret->id,
            'icon' => 'fa-box',
            'color' => '#FFC93C',
        ]);

        Category::create([
            'name' => 'İade ve Geri Ödeme',
            'parent_id' => $eticaret->id,
            'icon' => 'fa-undo',
            'color' => '#6BCB77',
        ]);

        // Finans
        $finans = Category::create([
            'name' => 'Finans',
            'description' => 'Bankacılık ve finansal hizmetler',
            'icon' => 'fa-credit-card',
            'color' => '#4D96FF',
            'order' => 2,
        ]);

        Category::create([
            'name' => 'Banka',
            'parent_id' => $finans->id,
            'icon' => 'fa-university',
        ]);

        Category::create([
            'name' => 'Sigorta',
            'parent_id' => $finans->id,
            'icon' => 'fa-shield-alt',
        ]);

        // İletişim
        $iletisim = Category::create([
            'name' => 'İletişim',
            'description' => 'Telekomunikasyon ve internet hizmetleri',
            'icon' => 'fa-phone',
            'color' => '#FF006E',
            'order' => 3,
        ]);

        Category::create(['name' => 'Mobil Operatörler', 'parent_id' => $iletisim->id]);
        Category::create(['name' => 'İnternet Sağlayıcıları', 'parent_id' => $iletisim->id]);

        // Diğer kategoriler
        Category::create([
            'name' => 'Elektronik',
            'icon' => 'fa-laptop',
            'color' => '#9D84B7',
            'order' => 4,
        ]);

        Category::create([
            'name' => 'Gıda ve Beslenme',
            'icon' => 'fa-utensils',
            'color' => '#FB5607',
            'order' => 5,
        ]);

        Category::create([
            'name' => 'Sağlık ve Eczacılık',
            'icon' => 'fa-pills',
            'color' => '#00D9FF',
            'order' => 6,
        ]);

        Category::create([
            'name' => 'Eğitim',
            'icon' => 'fa-graduation-cap',
            'color' => '#8338EC',
            'order' => 7,
        ]);
    }
}
