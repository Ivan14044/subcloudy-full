<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'SubCloudy' }}</title>
    <style>
        /* Шрифт SFT Schrifted Sans */
        @font-face {
            font-family: 'SFT Schrifted Sans';
            src: url('{{ config('app.url') }}/fonts/SFTSchriftedSansTRIAL-Regular.ttf') format('truetype');
            font-weight: 400;
            font-style: normal;
        }
        @font-face {
            font-family: 'SFT Schrifted Sans';
            src: url('{{ config('app.url') }}/fonts/SFTSchriftedSansTRIAL-Bold.ttf') format('truetype');
            font-weight: 700;
            font-style: normal;
        }

        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        table { border-collapse: collapse !important; }
        
        body { 
            height: 100% !important; 
            margin: 0 !important; 
            padding: 0 !important; 
            width: 100% !important; 
            background-color: #f8fafc; 
            font-family: 'SFT Schrifted Sans', 'Inter', 'Segoe UI', Helvetica, Arial, sans-serif; 
            color: #1e293b;
        }

        .wrapper { width: 100%; table-layout: fixed; background-color: #f8fafc; padding: 40px 0; }
        .main-container { 
            max-width: 600px; 
            margin: 0 auto; 
            background-color: #ffffff; 
            border-radius: 24px; 
            border: 1px solid rgba(0, 71, 255, 0.08); 
            overflow: hidden; 
            box-shadow: 0 10px 30px rgba(0, 71, 255, 0.05); 
        }
        
        .header { 
            background-color: #ffffff; 
            padding: 35px 30px; 
            text-align: center; 
            border-bottom: 1px solid #f1f5f9; 
        }
        .logo-text { 
            font-size: 32px; 
            font-weight: 700; 
            color: #0047ff; 
            text-decoration: none;
            letter-spacing: -0.5px;
        }
        
        .content { padding: 40px; }
        
        h1 { 
            color: #0f172a; 
            font-size: 26px; 
            font-weight: 700; 
            margin: 0 0 25px 0; 
            line-height: 1.3; 
            text-align: center;
        }
        p { 
            color: #475569; 
            font-size: 16px; 
            line-height: 1.6; 
            margin: 0 0 20px 0; 
            text-align: center; 
        }
        
        /* Баджи */
        .badge { 
            display: inline-block; 
            padding: 8px 20px; 
            border-radius: 50px; 
            font-size: 14px; 
            font-weight: 600; 
            color: #ffffff;
        }
        .badge-success { background-color: #10b981; }
        .badge-info { background-color: #3b82f6; }
        .badge-warning { background-color: #f59e0b; }

        /* Инфо-карточка */
        .info-card { 
            width: 100%; 
            margin: 25px 0; 
            background-color: #ffffff; 
            border-radius: 16px; 
            border: 1px solid #e2e8f0; 
            border-left: 4px solid #0047ff;
        }
        .info-body { padding: 20px 25px; }
        .info-title { 
            color: #0047ff; 
            font-size: 14px; 
            font-weight: 700; 
            margin-bottom: 15px; 
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: left;
        }
        .info-row { border-bottom: 1px solid #f1f5f9; padding: 10px 0; }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #64748b; font-size: 13px; font-weight: 600; text-align: left; }
        .info-value { color: #0f172a; font-size: 14px; font-weight: 700; text-align: right; }

        /* Кнопка */
        .btn-main { 
            background-color: #0047ff;
            color: #ffffff !important; 
            text-decoration: none; 
            padding: 16px 40px; 
            border-radius: 50px; 
            font-weight: 700; 
            font-size: 16px; 
            display: inline-block;
        }

        .footer { 
            padding: 30px; 
            text-align: center; 
            background-color: #f8fafc; 
            border-top: 1px solid #f1f5f9; 
        }
        .footer p { font-size: 13px; color: #94a3b8; margin: 5px 0; }
        .footer a { color: #0047ff; text-decoration: none; font-weight: 600; }

        .list-container { text-align: left; display: inline-block; width: auto; }
        ul { margin: 10px 0; padding-left: 20px; color: #475569; }
        li { margin-bottom: 8px; font-size: 15px; }

        @media only screen and (max-width: 600px) {
            .content { padding: 30px 20px; }
            .main-container { border-radius: 0; border: none; }
        }
    </style>
</head>
<body>
    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="wrapper">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" class="main-container" width="100%" style="max-width: 600px;">
                    <!-- Header -->
                    <tr>
                        <td class="header" align="center">
                            <a href="{{ config('app.url') }}" class="logo-text">SubCloudy</a>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td class="content" align="center">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center">
                                        <h1>{!! $subject !!}</h1>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-bottom: 20px;">
                                        {!! $body !!}
                                    </td>
                                </tr>
                                @if(!isset($button) || $button)
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <a href="{{ config('app.url') }}" class="btn-main">
                                            {{ trans('email.button') ?: 'Перейти на сайт' }}
                                        </a>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="footer" align="center">
                            <p><strong>{{ trans('email.signature') ?: 'С уважением, команда SubCloudy' }}</strong></p>
                            <p><a href="{{ config('app.url') }}">{{ str_replace(['http://', 'https://'], '', config('app.url')) }}</a></p>
                            <p style="font-size: 11px; margin-top: 20px; opacity: 0.6;">
                                {{ trans('email.team') ?: 'Это системное сообщение, отвечать на него не нужно.' }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
