<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<title>{{ __('My data') }}</title>
<style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #18181b; max-width: 720px; margin: 2rem auto; padding: 0 1rem; }
    h1 { font-size: 1.5rem; }
    h2 { font-size: 1.1rem; margin-top: 2rem; border-bottom: 1px solid #e4e4e7; padding-bottom: 0.25rem; }
    table { width: 100%; border-collapse: collapse; margin-top: 0.5rem; }
    th, td { text-align: left; padding: 0.4rem 0.5rem; border-bottom: 1px solid #f4f4f5; font-size: 0.9rem; }
    dl { margin-top: 0.5rem; }
    dt { font-weight: 600; }
    dd { margin: 0 0 0.5rem 0; color: #52525b; }
</style>
</head>
<body>
<h1>{{ __('My data') }}</h1>
<p>{{ __('Everything this platform holds about you, generated on :date.', ['date' => now()->toFormattedDateString()]) }}</p>

<h2>{{ __('Profile') }}</h2>
<dl>
    <dt>{{ __('Name') }}</dt>
    <dd>{{ $data['profile']['name'] }}</dd>
    <dt>{{ __('Email') }}</dt>
    <dd>{{ $data['profile']['email'] }}</dd>
    @if ($data['profile']['reseller'])
        <dt>{{ __('Reseller') }}</dt>
        <dd>{{ $data['profile']['reseller'] }}</dd>
    @endif
    @if ($data['profile']['klant'])
        <dt>{{ __('Klant') }}</dt>
        <dd>{{ $data['profile']['klant'] }}</dd>
    @endif
    <dt>{{ __('Member since') }}</dt>
    <dd>{{ $data['profile']['created_at'] }}</dd>
</dl>

<h2>{{ __('Course assignments') }}</h2>
@if (count($data['course_assignments']) === 0)
    <p>{{ __('None.') }}</p>
@else
    <table>
        <thead><tr><th>{{ __('Course') }}</th><th>{{ __('Assigned') }}</th><th>{{ __('Completed') }}</th><th>{{ __('Revoked') }}</th></tr></thead>
        <tbody>
        @foreach ($data['course_assignments'] as $row)
            <tr>
                <td>{{ $row['course_title'] }}</td>
                <td>{{ $row['assigned_at'] }}</td>
                <td>{{ $row['completed_at'] ?? '—' }}</td>
                <td>{{ $row['revoked_at'] ?? '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

<h2>{{ __('Quiz attempts') }}</h2>
@if (count($data['quiz_attempts']) === 0)
    <p>{{ __('None.') }}</p>
@else
    <table>
        <thead><tr><th>{{ __('Type') }}</th><th>{{ __('Started') }}</th><th>{{ __('Score') }}</th><th>{{ __('Passed') }}</th></tr></thead>
        <tbody>
        @foreach ($data['quiz_attempts'] as $row)
            <tr>
                <td>{{ $row['quiz_type'] }}</td>
                <td>{{ $row['started_at'] }}</td>
                <td>{{ $row['score'] ?? '—' }}</td>
                <td>{{ $row['passed'] === null ? '—' : ($row['passed'] ? __('Yes') : __('No')) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

{{-- "Certificates" alone collides with the resources/lang/*/certificates.php
     translation group file (Laravel treats a dot-less __() key as a group
     name; on a case-insensitive filesystem "Certificates" resolves to that
     file and __() returns the whole array instead of a string), so this
     heading needs a second word to stay a plain string lookup. --}}
<h2>{{ __('Certificates issued') }}</h2>
@if (count($data['certificates']) === 0)
    <p>{{ __('None.') }}</p>
@else
    <table>
        <thead><tr><th>{{ __('Course') }}</th><th>{{ __('Verification code') }}</th><th>{{ __('Issued') }}</th></tr></thead>
        <tbody>
        @foreach ($data['certificates'] as $row)
            <tr>
                <td>{{ $row['course_title'] }}</td>
                <td>{{ $row['verification_code'] }}</td>
                <td>{{ $row['issued_at'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

<h2>{{ __('Notifications') }}</h2>
@if (count($data['notifications']) === 0)
    <p>{{ __('None.') }}</p>
@else
    <table>
        <thead><tr><th>{{ __('Message') }}</th><th>{{ __('Sent') }}</th></tr></thead>
        <tbody>
        @foreach ($data['notifications'] as $row)
            <tr>
                <td>{{ $row['message'] }}</td>
                <td>{{ $row['created_at'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

<h2>{{ __('Account activity') }}</h2>
@if (count($data['activity']) === 0)
    <p>{{ __('None.') }}</p>
@else
    <table>
        <thead><tr><th>{{ __('Description') }}</th><th>{{ __('Date') }}</th></tr></thead>
        <tbody>
        @foreach ($data['activity'] as $row)
            <tr>
                <td>{{ $row['description'] }}</td>
                <td>{{ $row['created_at'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
</body>
</html>
