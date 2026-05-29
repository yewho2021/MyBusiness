@php
    $footerText = \App\Models\Configuration::get('footer_text', '© {year} {portal_name}. All rights reserved.');
    $footerVersion = \App\Models\Configuration::get('footer_version', '1.0.0');
    $portalName = \App\Models\Configuration::get('portal_name', 'Admin Portal');
    $footerText = str_replace(['{year}', '{portal_name}'], [date('Y'), $portalName], $footerText);
@endphp
<footer class="footer">
    <div>{!! \App\Services\HtmlSanitizer::sanitizeRichText($footerText) !!}</div>
    <div>Version {{ $footerVersion }}</div>
</footer>
