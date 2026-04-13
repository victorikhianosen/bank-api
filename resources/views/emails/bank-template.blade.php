<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            width: 100%;
            padding: 40px 0;
        }

        .main {
            max-width: 600px;
            margin: auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
        }

        .header {
            background: #059669;
            padding: 25px;
            text-align: center;
            color: #fff;
        }

        .content {
            padding: 30px;
            color: #334155;
            line-height: 1.6;
        }

        .divider {
            height: 1px;
            background: #e2e8f0;
            margin: 20px 0;
        }

        .footer {
            background: #059669;
            color: #fff;
            padding: 20px;
            font-size: 16px;
            text-align: center;
        }
    </style>
</head>

<body>

    @php
        $settings = \App\Models\Settings::pluck('setting_value', 'setting_key');

        $companyName = $settings['company_name'] ?? '';
        $companyEmail = $settings['company_email'] ?? '';
        $companyPhone = $settings['company_phone'] ?? '';
        $companyAddress = $settings['company_address'] ?? '';
    @endphp

    <div class="wrapper">
        <div class="main">

            <!-- HEADER -->
            <div class="header">
                <img  src="{{ $message->embed(public_path('assets/images/settings/logo.png')) }}" alt="BankPro Logo"
                    style="max-height: 50px; width: auto; display: block; margin: 0 auto;">
            </div>
            <!-- BODY -->
            <div class="content">

                <p>Dear {{ $name ?? 'Customer' }},</p>

                {!! $content !!}

                <div class="divider"></div>

                <p style="margin-bottom: 8px;">For further enquiries, please contact our customer support through the
                    following channels:</p>
                <p style="margin:0;">{{ $companyPhone }}</p>
                <p style="margin:0;">{{ $companyEmail }}</p>

                <p style="margin-top:20px;">
                    Thank you for choosing <strong>{{ $companyName }}</strong>.
                </p>

                <p style="font-weight:600;">
                    {{ $companyName }} Team
                </p>

            </div>

            <!-- FOOTER -->
            <div class="footer">
                &copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.<br>
                {{ $companyAddress }}
            </div>

        </div>
    </div>

</body>

</html>
