<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Encoders\GifEncoder;
use Intervention\Image\Encoders\BmpEncoder;

class ImageToolController extends Controller
{
    private string $tmpDir;

    public function __construct()
    {
        $this->tmpDir = storage_path('app/image-tools-tmp');
        if (!is_dir($this->tmpDir)) {
            mkdir($this->tmpDir, 0775, true);
        }
    }

    public function index()
    {
        return view('admin.pages.image-tools.index');
    }

    // ═══════════════════════════════════════════════════════════
    // TRANSFORM
    // ═══════════════════════════════════════════════════════════

    public function resize(Request $request)
    {
        $request->validate([
            'image'  => 'required|image|max:20480',
            'width'  => 'nullable|integer|min:1|max:10000',
            'height' => 'nullable|integer|min:1|max:10000',
            'mode'   => 'required|in:exact,fit,scale',
        ]);

        try {
            $file = $request->file('image');
            $img = Image::read($file->getPathname());
            $width = $request->input('width');
            $height = $request->input('height');

            if ($request->mode === 'fit') {
                $img->scaleDown($width, $height);
            } elseif ($request->mode === 'scale') {
                $img->scale($width, $height);
            } else {
                $img->resize($width, $height);
            }

            return $this->downloadResponse($img, $file, 'resized');
        } catch (\Throwable $e) {
            return back()->with('error', 'Resize failed: ' . $e->getMessage());
        }
    }

    public function crop(Request $request)
    {
        $request->validate([
            'image'  => 'required|image|max:20480',
            'width'  => 'required|integer|min:1|max:10000',
            'height' => 'required|integer|min:1|max:10000',
            'x'      => 'nullable|integer|min:0',
            'y'      => 'nullable|integer|min:0',
        ]);

        try {
            $file = $request->file('image');
            $img = Image::read($file->getPathname());
            $img->crop($request->width, $request->height, $request->input('x', 0), $request->input('y', 0));

            return $this->downloadResponse($img, $file, 'cropped');
        } catch (\Throwable $e) {
            return back()->with('error', 'Crop failed: ' . $e->getMessage());
        }
    }

    public function rotate(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:20480',
            'angle' => 'nullable|numeric|min:-360|max:360',
            'flip'  => 'nullable|in:h,v,both',
        ]);

        try {
            $file = $request->file('image');
            $img = Image::read($file->getPathname());
            $angle = $request->input('angle', 0);
            $flip = $request->input('flip');

            if ($angle != 0) $img->rotate($angle * -1);
            if ($flip === 'h' || $flip === 'both') $img->flip();
            if ($flip === 'v' || $flip === 'both') $img->flop();

            return $this->downloadResponse($img, $file, 'rotated');
        } catch (\Throwable $e) {
            return back()->with('error', 'Rotate failed: ' . $e->getMessage());
        }
    }

    public function flipImage(Request $request)
    {
        $request->validate([
            'image'     => 'required|image|max:20480',
            'direction' => 'required|in:horizontal,vertical,both',
        ]);

        try {
            $file = $request->file('image');
            $img = Image::read($file->getPathname());

            if ($request->direction === 'horizontal' || $request->direction === 'both') $img->flip();
            if ($request->direction === 'vertical' || $request->direction === 'both') $img->flop();

            return $this->downloadResponse($img, $file, 'flipped');
        } catch (\Throwable $e) {
            return back()->with('error', 'Flip failed: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════
    // OPTIMIZE
    // ═══════════════════════════════════════════════════════════

    public function compress(Request $request)
    {
        $request->validate([
            'image'   => 'required|image|max:20480',
            'quality' => 'required|integer|min:1|max:100',
        ]);

        try {
            $file = $request->file('image');
            $img = Image::read($file->getPathname());
            $ext = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
            if (!in_array($ext, ['jpg', 'jpeg', 'webp'])) $ext = 'jpg';

            $encoded = $img->encode($this->getEncoder($ext, $request->quality));
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . "_compressed.{$ext}";

            return response($encoded->toString())
                ->header('Content-Type', $this->getMimeType($ext))
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
                ->header('X-Original-Size', $file->getSize())
                ->header('X-Compressed-Size', strlen($encoded->toString()));
        } catch (\Throwable $e) {
            return back()->with('error', 'Compress failed: ' . $e->getMessage());
        }
    }

    public function batchResize(Request $request)
    {
        $request->validate([
            'images'   => 'required|array|min:1|max:50',
            'images.*' => 'image|max:20480',
            'width'    => 'required|integer|min:1|max:10000',
            'height'   => 'nullable|integer|min:1|max:10000',
        ]);

        try {
            $outDir = $this->tmpDir . '/batch_' . time();
            mkdir($outDir, 0775, true);
            $width = (int) $request->width;
            $height = $request->filled('height') ? (int) $request->height : null;

            foreach ($request->file('images') as $file) {
                $img = Image::read($file->getPathname());
                $img->scale($width, $height);

                $ext = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
                $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . "_resized.{$ext}";
                $encoded = $img->encode($this->getEncoder($ext));
                file_put_contents($outDir . '/' . $name, $encoded->toString());
            }

            $zipPath = $this->tmpDir . '/batch_resized_' . time() . '.zip';
            $zip = new \ZipArchive();
            $zip->open($zipPath, \ZipArchive::CREATE);
            foreach (glob($outDir . '/*') as $f) {
                $zip->addFile($f, basename($f));
            }
            $zip->close();

            // Cleanup
            array_map('unlink', glob($outDir . '/*'));
            @rmdir($outDir);

            return response()->download($zipPath, 'batch_resized_' . date('Y-m-d_His') . '.zip')
                ->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            return back()->with('error', 'Batch resize failed: ' . $e->getMessage());
        }
    }

    public function stripExif(Request $request)
    {
        $request->validate(['image' => 'required|image|max:20480']);

        try {
            $file = $request->file('image');
            $img = Image::read($file->getPathname());
            // Re-encoding strips all EXIF/IPTC metadata
            $ext = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
            return $this->downloadResponse($img, $file, 'clean');
        } catch (\Throwable $e) {
            return back()->with('error', 'Strip EXIF failed: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════
    // CONVERT
    // ═══════════════════════════════════════════════════════════

    public function convert(Request $request)
    {
        $request->validate([
            'image'  => 'required|image|max:20480',
            'format' => 'required|in:jpg,png,webp,gif,bmp',
        ]);

        try {
            $file = $request->file('image');
            $img = Image::read($file->getPathname());
            $format = $request->input('format');
            $quality = (int) $request->input('quality', 90);
            $encoded = $img->encode($this->getEncoder($format, $quality));
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . ".{$format}";

            return response($encoded->toString())
                ->header('Content-Type', $this->getMimeType($format))
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
        } catch (\Throwable $e) {
            return back()->with('error', 'Convert failed: ' . $e->getMessage());
        }
    }

    public function favicon(Request $request)
    {
        $request->validate(['image' => 'required|image|max:20480']);

        try {
            $file = $request->file('image');
            $outDir = $this->tmpDir . '/favicon_' . time();
            mkdir($outDir, 0775, true);

            $sizes = [
                'favicon-16x16.png' => 16,
                'favicon-32x32.png' => 32,
                'favicon-48x48.png' => 48,
                'apple-touch-icon.png' => 180,
                'android-chrome-192x192.png' => 192,
                'android-chrome-512x512.png' => 512,
            ];

            foreach ($sizes as $name => $size) {
                $img = Image::read($file->getPathname());
                $img->cover($size, $size);
                $encoded = $img->encode(new PngEncoder());
                file_put_contents($outDir . '/' . $name, $encoded->toString());
            }

            // Generate HTML snippet
            $html = "<!-- Favicon HTML -->\n"
                . "<link rel=\"icon\" type=\"image/png\" sizes=\"32x32\" href=\"/favicon-32x32.png\">\n"
                . "<link rel=\"icon\" type=\"image/png\" sizes=\"16x16\" href=\"/favicon-16x16.png\">\n"
                . "<link rel=\"apple-touch-icon\" sizes=\"180x180\" href=\"/apple-touch-icon.png\">\n";
            file_put_contents($outDir . '/favicon-html-snippet.txt', $html);

            $zipPath = $this->tmpDir . '/favicons_' . time() . '.zip';
            $zip = new \ZipArchive();
            $zip->open($zipPath, \ZipArchive::CREATE);
            foreach (glob($outDir . '/*') as $f) {
                $zip->addFile($f, basename($f));
            }
            $zip->close();

            array_map('unlink', glob($outDir . '/*'));
            @rmdir($outDir);

            return response()->download($zipPath, 'favicons.zip')
                ->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            return back()->with('error', 'Favicon generation failed: ' . $e->getMessage());
        }
    }

    public function base64(Request $request)
    {
        $request->validate(['image' => 'required|image|max:5120']);

        try {
            $file = $request->file('image');
            $mime = $file->getMimeType();
            $data = base64_encode(file_get_contents($file->getPathname()));
            $dataUri = "data:{$mime};base64,{$data}";

            return response()->json([
                'success'      => true,
                'data_uri'     => $dataUri,
                'html_tag'     => '<img src="' . $dataUri . '" />',
                'css_bg'       => 'background-image: url(' . $dataUri . ');',
                'original_size'=> $file->getSize(),
                'encoded_size' => strlen($dataUri),
                'filename'     => $file->getClientOriginalName(),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ═══════════════════════════════════════════════════════════
    // ADJUST (color/tone)
    // ═══════════════════════════════════════════════════════════

    public function adjust(Request $request)
    {
        $request->validate([
            'image'      => 'required|image|max:20480',
            'brightness' => 'nullable|integer|min:-100|max:100',
            'contrast'   => 'nullable|integer|min:-100|max:100',
            'gamma'      => 'nullable|numeric|min:0.1|max:9.9',
        ]);

        try {
            $file = $request->file('image');
            $img = Image::read($file->getPathname());

            $b = (int) $request->input('brightness', 0);
            $c = (int) $request->input('contrast', 0);
            $g = (float) $request->input('gamma', 1.0);

            if ($b != 0) $img->brightness($b);
            if ($c != 0) $img->contrast($c);
            if ($g != 1.0) $img->gamma($g);

            return $this->downloadResponse($img, $file, 'adjusted');
        } catch (\Throwable $e) {
            return back()->with('error', 'Adjust failed: ' . $e->getMessage());
        }
    }

    public function greyscale(Request $request)
    {
        $request->validate(['image' => 'required|image|max:20480']);

        try {
            $file = $request->file('image');
            $img = Image::read($file->getPathname());
            $img->greyscale();

            return $this->downloadResponse($img, $file, 'grayscale');
        } catch (\Throwable $e) {
            return back()->with('error', 'Grayscale failed: ' . $e->getMessage());
        }
    }

    public function sepia(Request $request)
    {
        $request->validate(['image' => 'required|image|max:20480']);

        try {
            $file = $request->file('image');
            $img = Image::read($file->getPathname());
            $img->greyscale()->colorize(20, 10, -10);

            return $this->downloadResponse($img, $file, 'sepia');
        } catch (\Throwable $e) {
            return back()->with('error', 'Sepia failed: ' . $e->getMessage());
        }
    }

    public function invert(Request $request)
    {
        $request->validate(['image' => 'required|image|max:20480']);

        try {
            $file = $request->file('image');
            $ext = strtolower($file->getClientOriginalExtension()) ?: 'png';

            // Use GD imagefilter for invert (Intervention v3 doesn't have native invert)
            $img = Image::read($file->getPathname());
            $tmpPath = $this->tmpDir . '/inv_' . time() . '.png';
            $img->encode(new PngEncoder())->save($tmpPath);

            $gd = imagecreatefrompng($tmpPath);
            if (!$gd) throw new \Exception('Could not create GD resource');

            imagefilter($gd, IMG_FILTER_NEGATE);
            imagepng($gd, $tmpPath);
            imagedestroy($gd);

            $result = Image::read($tmpPath);
            @unlink($tmpPath);

            return $this->downloadResponse($result, $file, 'inverted');
        } catch (\Throwable $e) {
            return back()->with('error', 'Invert failed: ' . $e->getMessage());
        }
    }

    public function colorize(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:20480',
            'red'   => 'required|integer|min:-100|max:100',
            'green' => 'required|integer|min:-100|max:100',
            'blue'  => 'required|integer|min:-100|max:100',
        ]);

        try {
            $file = $request->file('image');
            $img = Image::read($file->getPathname());
            $img->colorize($request->red, $request->green, $request->blue);

            return $this->downloadResponse($img, $file, 'colorized');
        } catch (\Throwable $e) {
            return back()->with('error', 'Colorize failed: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════
    // EFFECTS
    // ═══════════════════════════════════════════════════════════

    public function blur(Request $request)
    {
        $request->validate([
            'image'  => 'required|image|max:20480',
            'amount' => 'required|integer|min:1|max:100',
        ]);

        try {
            $file = $request->file('image');
            $img = Image::read($file->getPathname());
            $img->blur($request->amount);

            return $this->downloadResponse($img, $file, 'blurred');
        } catch (\Throwable $e) {
            return back()->with('error', 'Blur failed: ' . $e->getMessage());
        }
    }

    public function sharpen(Request $request)
    {
        $request->validate([
            'image'  => 'required|image|max:20480',
            'amount' => 'required|integer|min:1|max:100',
        ]);

        try {
            $file = $request->file('image');
            $img = Image::read($file->getPathname());
            $img->sharpen($request->amount);

            return $this->downloadResponse($img, $file, 'sharpened');
        } catch (\Throwable $e) {
            return back()->with('error', 'Sharpen failed: ' . $e->getMessage());
        }
    }

    public function pixelate(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:20480',
            'size'  => 'required|integer|min:2|max:100',
        ]);

        try {
            $file = $request->file('image');
            $img = Image::read($file->getPathname());
            $img->pixelate($request->size);

            return $this->downloadResponse($img, $file, 'pixelated');
        } catch (\Throwable $e) {
            return back()->with('error', 'Pixelate failed: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════
    // OVERLAY
    // ═══════════════════════════════════════════════════════════

    public function watermark(Request $request)
    {
        $request->validate([
            'image'    => 'required|image|max:20480',
            'text'     => 'required|string|max:200',
            'position' => 'required|in:top-left,top-right,bottom-left,bottom-right,center',
            'size'     => 'nullable|integer|min:10|max:200',
            'color'    => 'nullable|string|max:20',
            'opacity'  => 'nullable|integer|min:1|max:100',
        ]);

        try {
            $file = $request->file('image');
            $img = Image::read($file->getPathname());
            $text = $request->input('text');
            $position = $request->input('position', 'bottom-right');
            $size = $request->input('size', 36);
            $color = str_replace('#', '', $request->input('color', 'ffffff'));
            $opacity = $request->input('opacity', 50);
            $alphaHex = str_pad(dechex(round($opacity / 100 * 255)), 2, '0', STR_PAD_LEFT);
            $rgba = $color . $alphaHex;

            $img->text($text, $this->getPosX($position, $img->width()), $this->getPosY($position, $img->height()), function ($font) use ($size, $rgba) {
                $font->size($size);
                $font->color($rgba);
            });

            return $this->downloadResponse($img, $file, 'watermarked');
        } catch (\Throwable $e) {
            return back()->with('error', 'Watermark failed: ' . $e->getMessage());
        }
    }

    public function border(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:20480',
            'width' => 'required|integer|min:1|max:200',
            'color' => 'required|string|max:20',
        ]);

        try {
            $file = $request->file('image');
            $img = Image::read($file->getPathname());
            $bw = (int) $request->width;
            $color = $request->input('color', '#000000');

            // Use pad() to add border evenly on all sides
            $img->pad(
                $img->width() + ($bw * 2),
                $img->height() + ($bw * 2),
                $color
            );

            return $this->downloadResponse($img, $file, 'bordered');
        } catch (\Throwable $e) {
            return back()->with('error', 'Border failed: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════
    // INFO
    // ═══════════════════════════════════════════════════════════

    public function info(Request $request)
    {
        $request->validate(['image' => 'required|image|max:20480']);

        try {
            $file = $request->file('image');
            $img = Image::read($file->getPathname());

            $sz = $file->getSize();
            $units = ['B', 'KB', 'MB', 'GB'];
            $i = $sz > 0 ? floor(log($sz, 1024)) : 0;
            $sizeHuman = round($sz / pow(1024, $i), 1) . ' ' . $units[$i];

            // Try to read EXIF (JPG only)
            $exif = [];
            try {
                $exifRaw = @exif_read_data($file->getPathname());
                if ($exifRaw) {
                    if (!empty($exifRaw['Make'])) $exif['Camera'] = trim($exifRaw['Make'] . ' ' . ($exifRaw['Model'] ?? ''));
                    if (!empty($exifRaw['DateTime'])) $exif['Date Taken'] = $exifRaw['DateTime'];
                    if (!empty($exifRaw['ExposureTime'])) $exif['Exposure'] = $exifRaw['ExposureTime'];
                    if (!empty($exifRaw['FNumber'])) $exif['Aperture'] = 'f/' . $exifRaw['FNumber'];
                    if (!empty($exifRaw['ISOSpeedRatings'])) $exif['ISO'] = $exifRaw['ISOSpeedRatings'];
                    if (!empty($exifRaw['FocalLength'])) $exif['Focal Length'] = $exifRaw['FocalLength'] . 'mm';
                    if (!empty($exifRaw['GPSLatitude'])) $exif['GPS'] = 'Present (will be stripped on re-encode)';
                }
            } catch (\Throwable $e) {}

            return response()->json([
                'success'    => true,
                'width'      => $img->width(),
                'height'     => $img->height(),
                'size'       => $sz,
                'size_human' => $sizeHuman,
                'mime_type'  => $file->getMimeType(),
                'extension'  => strtolower($file->getClientOriginalExtension()),
                'filename'   => $file->getClientOriginalName(),
                'exif'       => $exif,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ═══════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════

    /**
     * Standard download response for processed images.
     */
    private function downloadResponse($img, $file, string $suffix)
    {
        $ext = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
        $encoded = $img->encode($this->getEncoder($ext));
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . "_{$suffix}.{$ext}";

        return response($encoded->toString())
            ->header('Content-Type', $this->getMimeType($ext))
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    private function getEncoder(string $format, int $quality = 90)
    {
        return match ($format) {
            'jpg', 'jpeg' => new JpegEncoder($quality),
            'png'         => new PngEncoder(),
            'webp'        => new WebpEncoder($quality),
            'gif'         => new GifEncoder(),
            'bmp'         => new BmpEncoder(),
            default       => new JpegEncoder($quality),
        };
    }

    private function getMimeType(string $format): string
    {
        return match ($format) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'webp'        => 'image/webp',
            'gif'         => 'image/gif',
            'bmp'         => 'image/bmp',
            default       => 'image/jpeg',
        };
    }

    private function getPosX(string $pos, int $w): int
    {
        return match ($pos) { 'top-left', 'bottom-left' => 30, 'center' => intval($w / 2), default => $w - 30 };
    }

    private function getPosY(string $pos, int $h): int
    {
        return match ($pos) { 'top-left', 'top-right' => 40, 'center' => intval($h / 2), default => $h - 30 };
    }
}
