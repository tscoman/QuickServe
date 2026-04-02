<?php
class QRCodeGenerator {
    public static function generateQR($text, $size = 200, $format = 'url') {
        $qr_url = "https://chart.googleapis.com/chart?chs={$size}x{$size}&chld=M|0&cht=qr&chl=" . urlencode($text);
        if ($format === 'url') return $qr_url;
        if ($format === 'img') return "<img src='$qr_url' alt='QR Code' class='w-full'/>";
        return $qr_url;
    }
    public static function generateToken() { return bin2hex(random_bytes(32)); }
}
?>
