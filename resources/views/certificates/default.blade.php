<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('certificates.title') }}</title>
    <style>
        {{-- dompdf supports only a plain CSS subset -- no Bootstrap, no
             flexbox/grid, table-based layout is the reliable choice. --}}
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #212529;
            margin: 0;
        }
        .certificate {
            border: 6px solid {{ $primaryColor }};
            padding: 60px;
            text-align: center;
        }
        .logo {
            max-height: 80px;
            margin-bottom: 30px;
        }
        h1 {
            font-size: 28px;
            color: {{ $primaryColor }};
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }
        .recipient {
            font-size: 24px;
            margin: 30px 0;
        }
        .course-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 30px;
        }
        .meta {
            font-size: 12px;
            color: #6c757d;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="certificate">
        @if ($logoUrl)
            <img class="logo" src="{{ $logoUrl }}" alt="">
        @endif

        <h1>{{ __('certificates.heading') }}</h1>

        <p>{{ __('certificates.awarded_to') }}</p>
        <div class="recipient">{{ $recipientName }}</div>

        <p>{{ __('certificates.for_completing') }}</p>
        <div class="course-title">{{ $courseTitle }}</div>

        <p>{{ __('certificates.issued_on', ['date' => $issuedAt]) }}</p>

        <div class="meta">{{ __('certificates.verification_code') }}: {{ $verificationCode }}</div>
    </div>
</body>
</html>
