<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GeneratePwaIcons extends Command
{
    protected $signature   = 'pwa:icons';
    protected $description = 'Gera os ícones PNG do PWA a partir do logo SVG usando GD';

    /** Tamanhos exigidos pelo PWA */
    private const SIZES = [72, 96, 128, 144, 152, 192, 384, 512];

    public function handle(): int
    {
        if (!function_exists('imagecreatetruecolor')) {
            $this->error('A extensão GD do PHP não está habilitada.');
            return 1;
        }

        $dir = public_path('icons');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        foreach (self::SIZES as $size) {
            $this->generateIcon($size, $dir);
            $this->info("  ✓ {$size}×{$size} → public/icons/{$size}.png");
        }

        $this->info('Ícones gerados com sucesso!');
        return 0;
    }

    // ── Geração de cada ícone ────────────────────────────────────────────

    private function generateIcon(int $size, string $dir): void
    {
        // O SVG original tem viewBox 0 0 32 32
        $s = $size / 32.0;

        $im = imagecreatetruecolor($size, $size);
        imagesavealpha($im, true);
        imagealphablending($im, false);

        // Fundo transparente
        $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im, 0, 0, $transparent);
        imagealphablending($im, true);

        // Paleta
        $teal  = imagecolorallocate($im, 0x2D, 0xD4, 0xBF); // #2dd4bf
        $black = imagecolorallocate($im, 0x00, 0x00, 0x00);

        // ── Fundo: rect 0 0 32 32 rx=8 ──────────────────────────────────
        $this->filledRoundRect($im, 0, 0, $size - 1, $size - 1, (int)round(8 * $s), $teal);

        // ── Linha 1: rect x=6 y=9 w=14 h=2.5 rx=1.25 ───────────────────
        $this->filledRoundRect($im,
            (int)round(6 * $s),    (int)round(9 * $s),
            (int)round(20 * $s),   (int)round(11.5 * $s),
            max(1, (int)round(1.25 * $s)), $black
        );

        // ── Linha 2: rect x=6 y=14.75 w=10 h=2.5 rx=1.25 ───────────────
        $this->filledRoundRect($im,
            (int)round(6 * $s),    (int)round(14.75 * $s),
            (int)round(16 * $s),   (int)round(17.25 * $s),
            max(1, (int)round(1.25 * $s)), $black
        );

        // ── Linha 3: rect x=6 y=20.5 w=12 h=2.5 rx=1.25 ────────────────
        $this->filledRoundRect($im,
            (int)round(6 * $s),    (int)round(20.5 * $s),
            (int)round(18 * $s),   (int)round(23 * $s),
            max(1, (int)round(1.25 * $s)), $black
        );

        // ── Círculo: cx=24 cy=21 r=4 ────────────────────────────────────
        $cx = (int)round(24 * $s);
        $cy = (int)round(21 * $s);
        $r  = (int)round(4 * $s);
        imagefilledellipse($im, $cx, $cy, $r * 2, $r * 2, $black);

        // ── Check: M22 21 l1.5 1.5 3-3 ─────────────────────────────────
        $sw = max(1, (int)round(1.5 * $s));
        imagesetthickness($im, $sw);
        // (22,21) → (23.5,22.5)
        imageline($im,
            (int)round(22 * $s),   (int)round(21 * $s),
            (int)round(23.5 * $s), (int)round(22.5 * $s),
            $teal
        );
        // (23.5,22.5) → (26.5,19.5)
        imageline($im,
            (int)round(23.5 * $s), (int)round(22.5 * $s),
            (int)round(26.5 * $s), (int)round(19.5 * $s),
            $teal
        );

        imagepng($im, "{$dir}/{$size}.png");
        imagedestroy($im);
    }

    /**
     * Desenha um retângulo preenchido com cantos arredondados usando GD puro.
     */
    private function filledRoundRect($im, int $x1, int $y1, int $x2, int $y2, int $r, $color): void
    {
        if ($r <= 0) {
            imagefilledrectangle($im, $x1, $y1, $x2, $y2, $color);
            return;
        }

        $w = $x2 - $x1;
        $h = $y2 - $y1;
        $r = min($r, (int)($w / 2), (int)($h / 2));

        // Centro horizontal e vertical
        imagefilledrectangle($im, $x1 + $r, $y1, $x2 - $r, $y2, $color);
        imagefilledrectangle($im, $x1, $y1 + $r, $x2, $y2 - $r, $color);

        // Quatro cantos arredondados
        imagefilledellipse($im, $x1 + $r, $y1 + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($im, $x2 - $r, $y1 + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($im, $x1 + $r, $y2 - $r, $r * 2, $r * 2, $color);
        imagefilledellipse($im, $x2 - $r, $y2 - $r, $r * 2, $r * 2, $color);
    }
}
