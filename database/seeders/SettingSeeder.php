<?php
namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::create(['key' => 'site_name', 'value' => 'Şikayetvar', 'type' => 'string', 'group' => 'general']);
        Setting::create(['key' => 'site_description', 'value' => 'Türkiye\'nin en güvenilir şikayet ve çözüm platformu', 'type' => 'string', 'group' => 'general']);
        Setting::create(['key' => 'site_url', 'value' => 'https://sikayetvar.com', 'type' => 'string', 'group' => 'general']);

        Setting::create(['key' => 'email_from', 'value' => 'noreply@sikayetvar.com', 'type' => 'string', 'group' => 'email']);
        Setting::create(['key' => 'email_support', 'value' => 'support@sikayetvar.com', 'type' => 'string', 'group' => 'email']);

        Setting::create(['key' => 'complaint_moderation_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'moderation']);
        Setting::create(['key' => 'spam_threshold', 'value' => '75', 'type' => 'integer', 'group' => 'moderation']);
        Setting::create(['key' => 'auto_publish_complaints', 'value' => '0', 'type' => 'boolean', 'group' => 'moderation']);
    }
}
