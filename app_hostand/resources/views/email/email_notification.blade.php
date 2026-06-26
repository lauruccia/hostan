<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>{{ $data['subject'] ?? __('Email Notification') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f7fa;
            line-height: 1.6;
            color: #333333;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07), 0 1px 3px rgba(0, 0, 0, 0.06);
        }
        
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: #ffffff;
        }
        
        .email-header img {
            max-height: 60px;
            width: auto;
            display: block;
            margin: 0 auto;
        }
        
        .email-body {
            padding: 40px 30px;
        }
        
        .email-content {
            color: #4a5568;
            font-size: 16px;
            line-height: 1.8;
        }
        
        .email-content h1,
        .email-content h2,
        .email-content h3 {
            color: #1a202c;
            margin-bottom: 16px;
            font-weight: 600;
        }
        
        .email-content p {
            margin-bottom: 16px;
        }
        
        .email-content ul,
        .email-content ol {
            margin: 16px 0;
            padding-left: 24px;
        }
        
        .email-content li {
            margin-bottom: 8px;
            color: #4a5568;
        }
        
        .email-content strong {
            color: #2d3748;
            font-weight: 600;
        }
        
        .email-content blockquote {
            border-left: 4px solid #667eea;
            padding-left: 20px;
            margin: 20px 0;
            font-style: italic;
            color: #718096;
        }
        
        .email-footer {
            background-color: #f7fafc;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            color: #718096;
            font-size: 14px;
        }
        
        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #e2e8f0, transparent);
            margin: 30px 0;
        }
        
        @media only screen and (max-width: 600px) {
            .email-wrapper {
                width: 100% !important;
                border-radius: 0 !important;
            }
            
            .email-header,
            .email-body,
            .email-footer {
                padding: 24px 20px !important;
            }
            
            .email-content {
                font-size: 14px !important;
            }
        }
    </style>
</head>

<body style="background-color: #f5f7fa; padding: 20px 0;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f5f7fa;">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <div class="email-wrapper">
                    <!-- Header -->
                    <div class="email-header">
                        @if(!empty($data['logo']))
                            <img src="{{ asset(Storage::url('upload/logo/')) . '/' . $data['logo'] }}" 
                                 alt="{{ $data['company_name'] ?? __('Company Logo') }}" 
                                 style="max-height: 60px; width: auto;">
                        @else
                            <h1 style="margin: 0; font-size: 24px; font-weight: 600;">{{ $data['company_name'] ?? __('Company') }}</h1>
                        @endif
                    </div>
                    
                    <!-- Body -->
                    <div class="email-body">
                        <div class="email-content">
                            {!! $data['message'] !!}
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="email-footer">
                        <p style="margin: 0 0 8px 0;">
                            <strong>{{ $data['company_name'] ?? __('Company') }}</strong>
                        </p>
                        @if(!empty($data['company_email']))
                            <p style="margin: 0; color: #718096; font-size: 13px;">
                                {{ $data['company_email'] }}
                            </p>
                        @endif
                        <div class="divider"></div>
                        <p style="margin: 0; font-size: 12px; color: #a0aec0;">
                            {{ __('This is an automated email. Please do not reply to this message.') }}
                        </p>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>

</html>
