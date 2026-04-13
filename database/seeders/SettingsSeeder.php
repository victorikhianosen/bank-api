<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('settings')->insert([

            // COMPANY
            ['setting_group'=>'company','setting_key'=>'company_name','setting_value'=>'AssetMatrix MFB'],
            ['setting_group'=>'company','setting_key'=>'company_logo','setting_value'=>'assets/images/settings/logo.png'],
            ['setting_group'=>'company','setting_key'=>'company_share','setting_value'=>'200000000'],
            ['setting_group'=>'company','setting_key'=>'company_capital','setting_value'=>'112350552.04'],
            ['setting_group'=>'company','setting_key'=>'company_address','setting_value'=>'Ibadan'],
            ['setting_group'=>'company','setting_key'=>'company_currency','setting_value'=>'NGN'],
            ['setting_group'=>'company','setting_key'=>'company_country','setting_value'=>'Nigeria'],
            ['setting_group'=>'company','setting_key'=>'company_email','setting_value'=>'info@cba.assetmatrixmfb.com'],
            ['setting_group'=>'company','setting_key'=>'company_phone','setting_value'=>'080'],

            // SYSTEM
            ['setting_group'=>'system','setting_key'=>'system_version','setting_value'=>'1.0'],
            ['setting_group'=>'system','setting_key'=>'bank_fund','setting_value'=>'0'],
            ['setting_group'=>'system','setting_key'=>'enable_cron','setting_value'=>'1'],

            // ACCOUNTS
            ['setting_group'=>'accounts','setting_key'=>'till_account','setting_value'=>'10923392'],
            ['setting_group'=>'accounts','setting_key'=>'vault_account','setting_value'=>'10373391'],
            ['setting_group'=>'accounts','setting_key'=>'company_account','setting_value'=>'10997918'],

            // SMS
            ['setting_group'=>'sms','setting_key'=>'sms_enabled','setting_value'=>'1'],
            ['setting_group'=>'sms','setting_key'=>'active_sms','setting_value'=>'termii'],
            ['setting_group'=>'sms','setting_key'=>'sms_public_key','setting_value'=>'TLLOIHNInhqZtTKUhSdeBIgdGbPKGFiPjaaZTWzEqEyVEyCvSRTTbcVOsQZVqS'],
            ['setting_group'=>'sms','setting_key'=>'sms_secret_key','setting_value'=>null],
            ['setting_group'=>'sms','setting_key'=>'sms_baseurl','setting_value'=>'https://v3.api.termii.com/api/'],
            ['setting_group'=>'sms','setting_key'=>'sms_sender','setting_value'=>'AssetMatrix MFB'],

            // PAYMENT
            ['setting_group'=>'payment','setting_key'=>'payment_gateway','setting_value'=>'Flutterwave'],
            ['setting_group'=>'payment','setting_key'=>'gateway_pub_key','setting_value'=>'FLWPUBK-7e29638573271bd98b7a8ff83c31a6c7-'],
            ['setting_group'=>'payment','setting_key'=>'gateway_secret_key','setting_value'=>'FLWSECK-48901f50145a4f75549013bdaf5c289f-189fe7889d9vt-'],

            // LIMITS
            ['setting_group'=>'limits','setting_key'=>'deposit_limit','setting_value'=>'500'],
            ['setting_group'=>'limits','setting_key'=>'withdrawal_limit','setting_value'=>'500'],
            ['setting_group'=>'limits','setting_key'=>'stamp_duty','setting_value'=>'50'],
            ['setting_group'=>'limits','setting_key'=>'savings_account_interest_rate','setting_value'=>'3.5'],
            ['setting_group'=>'limits','setting_key'=>'savings_account_transfer_limit_per_month','setting_value'=>'2'],

        ]);
    }
}
