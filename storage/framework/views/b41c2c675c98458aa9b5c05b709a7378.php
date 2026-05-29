<?php
    $footerText = \App\Models\Configuration::get('footer_text', '© {year} {portal_name}. All rights reserved.');
    $footerVersion = \App\Models\Configuration::get('footer_version', '1.0.0');
    $portalName = \App\Models\Configuration::get('portal_name', 'Admin Portal');
    $footerText = str_replace(['{year}', '{portal_name}'], [date('Y'), $portalName], $footerText);
?>
<footer class="footer">
    <div><?php echo \App\Services\HtmlSanitizer::sanitizeRichText($footerText); ?></div>
    <div>Version <?php echo e($footerVersion); ?></div>
</footer>
<?php /**PATH /home/mybusiness/office.mybusiness.com.my/resources/views/admin/partials/menu_footer.blade.php ENDPATH**/ ?>