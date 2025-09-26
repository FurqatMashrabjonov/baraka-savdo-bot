<?php

namespace Database\Seeders;

use App\Models\ChinaAddress;
use Illuminate\Database\Seeder;

class ChinaAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default China address template
        ChinaAddress::create([
            'name' => 'Default China Address',
            'address_template' => "🇨🇳 **CHINA WAREHOUSE ADDRESS**\n\n📍 **Address:**\nBaraka Logistics Warehouse\nBuilding 15, Floor 3, Room 301\nYiwu International Trade Market\nYiwu, Zhejiang Province, China\n\n📱 **Contact:** +86 579 1234 5678\n📧 **Email:** china@baraka-logistics.com\n\n🔢 **Your Client ID:** {client_id}\n\n⚠️ **IMPORTANT:** Always include your Client ID ({client_id}) when sending parcels to this address!\n\n🎯 **Instructions:**\n1. Use this address for all your shipments\n2. Write your Client ID clearly on the package\n3. Contact us if you need assistance\n\n📞 **24/7 Support:** +998 95 123 45 67",
            'is_active' => true,
        ]);
    }
}
