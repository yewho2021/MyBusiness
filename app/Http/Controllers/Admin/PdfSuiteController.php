<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use setasign\Fpdi\Tcpdf\Fpdi;
use Smalot\PdfParser\Parser as PdfParser;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfSuiteController extends Controller
{
    private string $tmpDir;

    public function __construct()
    {
        $this->tmpDir = storage_path('app/pdf-suite-tmp');
        if (!is_dir($this->tmpDir)) {
            mkdir($this->tmpDir, 0775, true);
        }
    }

    public function index()
    {
        return view('admin.pages.pdf-suite.index');
    }

    /**
     * Check if exec() is available and Ghostscript is installed.
     */
    protected function canExec(): bool
    {
        if (!function_exists('exec')) return false;
        $disabled = explode(',', ini_get('disable_functions'));
        $disabled = array_map('trim', $disabled);
        if (in_array('exec', $disabled)) return false;
        // Check Ghostscript exists
        @exec('which gs 2>/dev/null', $out, $ret);
        return $ret === 0;
    }

    /**
     * Check if Imagick extension is available.
     */
    protected function canImagick(): bool
    {
        return class_exists('Imagick');
    }

    // ═══════════════════════════════════════════════
    // 1. MERGE PDF
    // ═══════════════════════════════════════════════
    public function merge(Request $request)
    {
        $request->validate([
            'pdfs'   => 'required|array|min:2|max:20',
            'pdfs.*' => 'file|mimes:pdf|max:51200',
        ]);

        try {
            $pdf = new Fpdi();

            foreach ($request->file('pdfs') as $file) {
                $pageCount = $pdf->setSourceFile($file->getPathname());
                for ($i = 1; $i <= $pageCount; $i++) {
                    $tpl = $pdf->importPage($i);
                    $size = $pdf->getTemplateSize($tpl);
                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($tpl);
                }
            }

            $outputPath = $this->tmpDir . '/merged_' . time() . '.pdf';
            $pdf->Output($outputPath, 'F');

            return response()->download($outputPath, 'merged_' . date('Y-m-d_His') . '.pdf')
                ->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return back()->with('error', 'Merge failed: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════
    // 2. SPLIT PDF
    // ═══════════════════════════════════════════════
    public function split(Request $request)
    {
        $request->validate([
            'pdf'   => 'required|file|mimes:pdf|max:51200',
            'pages' => 'required|string', // e.g. "1,3,5-8" or "all"
        ]);

        try {
            $file = $request->file('pdf');
            $pagesInput = $request->input('pages');

            // Parse page ranges
            $pdf = new Fpdi();
            $pdf->SetAutoPageBreak(false, 0);
            $sourcePageCount = $pdf->setSourceFile($file->getPathname());
            $selectedPages = $this->parsePageRange($pagesInput, $sourcePageCount);

            if (empty($selectedPages)) {
                return back()->with('error', 'No valid pages selected.');
            }

            foreach ($selectedPages as $pageNo) {
                if ($pageNo < 1 || $pageNo > $sourcePageCount) continue;
                $tpl = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($tpl);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl);
            }

            $outputPath = $this->tmpDir . '/split_' . time() . '.pdf';
            $pdf->Output($outputPath, 'F');

            return response()->download($outputPath, 'split_' . date('Y-m-d_His') . '.pdf')
                ->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return back()->with('error', 'Split failed: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════
    // 3. ROTATE PDF
    // ═══════════════════════════════════════════════
    public function rotate(Request $request)
    {
        $request->validate([
            'pdf'   => 'required|file|mimes:pdf|max:51200',
            'angle' => 'required|in:90,180,270',
            'pages' => 'nullable|string',
        ]);

        try {
            $file = $request->file('pdf');
            $angle = (int) $request->input('angle');
            $pagesInput = $request->input('pages', 'all');

            $pdf = new Fpdi();
            $pdf->SetAutoPageBreak(false, 0);
            $sourcePageCount = $pdf->setSourceFile($file->getPathname());
            $selectedPages = $pagesInput === 'all'
                ? range(1, $sourcePageCount)
                : $this->parsePageRange($pagesInput, $sourcePageCount);

            for ($i = 1; $i <= $sourcePageCount; $i++) {
                $tpl = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($tpl);
                $shouldRotate = in_array($i, $selectedPages);

                if ($shouldRotate && ($angle == 90 || $angle == 270)) {
                    $pdf->AddPage(
                        $size['orientation'] === 'P' ? 'L' : 'P',
                        [$size['height'], $size['width']]
                    );
                    $pdf->StartTransform();
                    if ($angle == 90) {
                        $pdf->Rotate(-90, 0, 0);
                        $pdf->useTemplate($tpl, -$size['height'], 0, $size['width'], $size['height']);
                    } else {
                        $pdf->Rotate(90, 0, 0);
                        $pdf->useTemplate($tpl, 0, -$size['width'], $size['width'], $size['height']);
                    }
                    $pdf->StopTransform();
                } elseif ($shouldRotate && $angle == 180) {
                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->StartTransform();
                    $pdf->Rotate(180, $size['width'] / 2, $size['height'] / 2);
                    $pdf->useTemplate($tpl);
                    $pdf->StopTransform();
                } else {
                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($tpl);
                }
            }

            $outputPath = $this->tmpDir . '/rotated_' . time() . '.pdf';
            $pdf->Output($outputPath, 'F');

            return response()->download($outputPath, 'rotated_' . date('Y-m-d_His') . '.pdf')
                ->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return back()->with('error', 'Rotate failed: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════
    // 4. ADD PAGE NUMBERS
    // ═══════════════════════════════════════════════
    public function pageNumbers(Request $request)
    {
        $request->validate([
            'pdf'      => 'required|file|mimes:pdf|max:51200',
            'position' => 'required|in:bottom-center,bottom-left,bottom-right,top-center,top-left,top-right',
            'format'   => 'nullable|in:number,page-of,dash',
            'start'    => 'nullable|integer|min:1',
        ]);

        try {
            $file = $request->file('pdf');
            $position = $request->input('position', 'bottom-center');
            $format = $request->input('format', 'number');
            $startNum = $request->input('start', 1);

            $pdf = new Fpdi();
            $pdf->SetAutoPageBreak(false, 0);
            $sourcePageCount = $pdf->setSourceFile($file->getPathname());

            for ($i = 1; $i <= $sourcePageCount; $i++) {
                $tpl = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($tpl);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl);

                $pageNum = $startNum + $i - 1;

                // Format the page number text
                $text = match ($format) {
                    'page-of' => "Page {$pageNum} of {$sourcePageCount}",
                    'dash'    => "- {$pageNum} -",
                    default   => (string) $pageNum,
                };

                $pdf->SetFont('helvetica', '', 10);
                $pdf->SetTextColor(100, 100, 100);

                $w = $size['width'];
                $h = $size['height'];
                $textWidth = $pdf->GetStringWidth($text);

                // Calculate position
                [$x, $y] = match ($position) {
                    'bottom-center' => [($w - $textWidth) / 2, $h - 12],
                    'bottom-left'   => [15, $h - 12],
                    'bottom-right'  => [$w - $textWidth - 15, $h - 12],
                    'top-center'    => [($w - $textWidth) / 2, 10],
                    'top-left'      => [15, 10],
                    'top-right'     => [$w - $textWidth - 15, 10],
                };

                $pdf->SetXY($x, $y);
                $pdf->Write(5, $text);
            }

            $outputPath = $this->tmpDir . '/numbered_' . time() . '.pdf';
            $pdf->Output($outputPath, 'F');

            return response()->download($outputPath, 'numbered_' . date('Y-m-d_His') . '.pdf')
                ->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return back()->with('error', 'Page numbers failed: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════
    // 5. WATERMARK PDF (text)
    // ═══════════════════════════════════════════════
    public function watermark(Request $request)
    {
        $request->validate([
            'pdf'      => 'required|file|mimes:pdf|max:51200',
            'text'     => 'required|string|max:100',
            'opacity'  => 'nullable|integer|min:1|max:100',
            'size'     => 'nullable|integer|min:10|max:120',
            'angle'    => 'nullable|integer|min:-90|max:90',
        ]);

        try {
            $file = $request->file('pdf');
            $text = $request->input('text');
            $size = $request->input('size', 40);
            $angle = $request->input('angle', -45);
            $opacity = $request->input('opacity', 30);

            $pdf = new Fpdi();
            $pdf->SetAutoPageBreak(false, 0);
            $sourcePageCount = $pdf->setSourceFile($file->getPathname());

            for ($i = 1; $i <= $sourcePageCount; $i++) {
                $tpl = $pdf->importPage($i);
                $pageSize = $pdf->getTemplateSize($tpl);
                $pdf->AddPage($pageSize['orientation'], [$pageSize['width'], $pageSize['height']]);
                $pdf->useTemplate($tpl);

                // Set watermark
                $pdf->SetFont('helvetica', 'B', $size);
                $alpha = $opacity / 100;
                $pdf->SetAlpha($alpha);
                $pdf->SetTextColor(128, 128, 128);

                $w = $pageSize['width'];
                $h = $pageSize['height'];

                // Center watermark
                $textWidth = $pdf->GetStringWidth($text);
                $x = ($w - $textWidth) / 2;
                $y = $h / 2;

                $pdf->SetXY($x, $y);
                $pdf->Write(0, $text);
                $pdf->SetAlpha(1); // Reset alpha
            }

            $outputPath = $this->tmpDir . '/watermarked_' . time() . '.pdf';
            $pdf->Output($outputPath, 'F');

            return response()->download($outputPath, 'watermarked_' . date('Y-m-d_His') . '.pdf')
                ->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return back()->with('error', 'Watermark failed: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════
    // 6. JPG TO PDF
    // ═══════════════════════════════════════════════
    public function jpgToPdf(Request $request)
    {
        $request->validate([
            'images'   => 'required|array|min:1|max:30',
            'images.*' => 'file|mimes:jpg,jpeg,png,gif,webp,bmp|max:20480',
            'orientation' => 'nullable|in:portrait,landscape,auto',
        ]);

        try {
            $orientation = $request->input('orientation', 'auto');
            $html = '<style>body{margin:0;padding:0;} img{width:100%;height:auto;display:block;page-break-after:always;} img:last-child{page-break-after:avoid;}</style>';

            foreach ($request->file('images') as $image) {
                $data = base64_encode(file_get_contents($image->getPathname()));
                $mime = $image->getMimeType();
                $html .= '<img src="data:' . $mime . ';base64,' . $data . '">';
            }

            $orient = $orientation === 'auto' ? 'portrait' : $orientation;
            $pdf = Pdf::loadHTML($html)->setPaper('a4', $orient);

            return $pdf->download('images_to_pdf_' . date('Y-m-d_His') . '.pdf');

        } catch (\Exception $e) {
            return back()->with('error', 'JPG to PDF failed: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════
    // 7. PDF TO JPG (via Ghostscript)
    // ═══════════════════════════════════════════════
    public function pdfToJpg(Request $request)
    {
        $request->validate([
            'pdf'     => 'required|file|mimes:pdf|max:51200',
            'quality' => 'nullable|integer|min:72|max:300',
        ]);

        try {
            $file = $request->file('pdf');
            $quality = (int) $request->input('quality', 150);
            $inputPath = $file->getPathname();
            $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $outputDir = $this->tmpDir . '/pdf2jpg_' . time();
            mkdir($outputDir, 0775, true);

            if ($this->canExec()) {
                // Ghostscript method
                $cmd = sprintf(
                    'gs -dBATCH -dNOPAUSE -sDEVICE=jpeg -r%d -dJPEGQ=90 -sOutputFile=%s %s 2>&1',
                    $quality,
                    escapeshellarg($outputDir . '/page_%03d.jpg'),
                    escapeshellarg($inputPath)
                );
                exec($cmd, $output, $returnCode);
                if ($returnCode !== 0) {
                    $this->cleanDir($outputDir);
                    return back()->with('error', 'PDF to JPG failed (Ghostscript error).');
                }
            } elseif ($this->canImagick()) {
                // Imagick fallback
                $imagick = new \Imagick();
                $imagick->setResolution($quality, $quality);
                $imagick->readImage($inputPath);
                foreach ($imagick as $i => $page) {
                    $page->setImageFormat('jpg');
                    $page->setImageCompressionQuality(90);
                    $page->writeImage($outputDir . '/page_' . sprintf('%03d', $i + 1) . '.jpg');
                }
                $imagick->destroy();
            } else {
                return back()->with('error', 'PDF to JPG requires Ghostscript or Imagick. Neither is available on this server. Contact your hosting provider.');
            }

            $images = glob($outputDir . '/*.jpg');
            if (count($images) === 0) {
                $this->cleanDir($outputDir);
                return back()->with('error', 'No images generated.');
            }

            if (count($images) === 1) {
                return response()->download($images[0], $baseName . '_page1.jpg')
                    ->deleteFileAfterSend(true);
            }

            $zipPath = $this->tmpDir . '/' . $baseName . '_jpg.zip';
            $zip = new \ZipArchive();
            $zip->open($zipPath, \ZipArchive::CREATE);
            foreach ($images as $img) {
                $zip->addFile($img, basename($img));
            }
            $zip->close();
            $this->cleanDir($outputDir);

            return response()->download($zipPath, $baseName . '_jpg.zip')
                ->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return back()->with('error', 'PDF to JPG failed: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════
    // 8. EXTRACT TEXT
    // ═══════════════════════════════════════════════
    public function extractText(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:51200',
        ]);

        try {
            $parser = new PdfParser();
            $document = $parser->parseFile($request->file('pdf')->getPathname());
            $text = $document->getText();

            $pages = $document->getPages();
            $pageTexts = [];
            foreach ($pages as $i => $page) {
                $pageTexts[] = [
                    'page' => $i + 1,
                    'text' => $page->getText(),
                ];
            }

            $totalChars = mb_strlen($text);
            $totalWords = str_word_count($text);
            $totalPages = count($pages);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'    => true,
                    'text'       => $text,
                    'pages'      => $pageTexts,
                    'stats'      => [
                        'pages' => $totalPages,
                        'words' => $totalWords,
                        'chars' => $totalChars,
                    ],
                ]);
            }

            // Download as .txt
            $filename = 'extracted_text_' . date('Y-m-d_His') . '.txt';
            $header = "Extracted from: " . $request->file('pdf')->getClientOriginalName() . "\n";
            $header .= "Pages: {$totalPages} | Words: {$totalWords} | Characters: {$totalChars}\n";
            $header .= str_repeat('=', 60) . "\n\n";

            $content = $header;
            foreach ($pageTexts as $pt) {
                $content .= "--- Page {$pt['page']} ---\n{$pt['text']}\n\n";
            }

            return response($content)
                ->header('Content-Type', 'text/plain')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Extract failed: ' . $e->getMessage()], 422);
            }
            return back()->with('error', 'Extract text failed: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════
    // 9. COMPRESS PDF (via Ghostscript)
    // ═══════════════════════════════════════════════
    public function compress(Request $request)
    {
        $request->validate([
            'pdf'     => 'required|file|mimes:pdf|max:51200',
            'quality' => 'nullable|in:screen,ebook,printer,prepress',
        ]);

        try {
            $file = $request->file('pdf');
            $quality = $request->input('quality', 'ebook');
            $inputPath = $file->getPathname();
            $outputPath = $this->tmpDir . '/compressed_' . time() . '.pdf';
            $originalSize = $file->getSize();
            $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            if ($this->canExec()) {
                // Ghostscript compression (best quality)
                $cmd = sprintf(
                    'gs -dBATCH -dNOPAUSE -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/%s -sOutputFile=%s %s 2>&1',
                    $quality,
                    escapeshellarg($outputPath),
                    escapeshellarg($inputPath)
                );
                exec($cmd, $output, $returnCode);

                if ($returnCode !== 0 || !file_exists($outputPath)) {
                    return back()->with('error', 'Compression failed (Ghostscript error).');
                }
            } else {
                // FPDI fallback — re-import pages (strips dead objects, reduces size modestly)
                $pdf = new Fpdi();
                $pageCount = $pdf->setSourceFile($inputPath);
                for ($i = 1; $i <= $pageCount; $i++) {
                    $tpl = $pdf->importPage($i);
                    $size = $pdf->getTemplateSize($tpl);
                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($tpl);
                }
                $pdf->Output($outputPath, 'F');
            }

            $compressedSize = filesize($outputPath);
            $saved = $originalSize - $compressedSize;
            $pct = $originalSize > 0 ? round(($saved / $originalSize) * 100) : 0;

            return response()->download($outputPath, $baseName . '_compressed.pdf', [
                'X-Original-Size' => $originalSize,
                'X-Compressed-Size' => $compressedSize,
                'X-Saved-Percent' => $pct,
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return back()->with('error', 'Compression failed: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════
    // 10. PROTECT PDF (add password via Ghostscript)
    // ═══════════════════════════════════════════════
    public function protect(Request $request)
    {
        $request->validate([
            'pdf'            => 'required|file|mimes:pdf|max:51200',
            'user_password'  => 'required|string|min:1|max:50',
            'owner_password' => 'nullable|string|max:50',
        ]);

        try {
            $file = $request->file('pdf');
            $userPwd = $request->input('user_password');
            $ownerPwd = $request->input('owner_password', $userPwd);
            $inputPath = $file->getPathname();
            $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $outputPath = $this->tmpDir . '/protected_' . time() . '.pdf';

            if ($this->canExec()) {
                // Ghostscript method (stronger encryption)
                $cmd = sprintf(
                    'gs -dBATCH -dNOPAUSE -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dEncryptionR=3 -dKeyLength=128 -sUserPassword=%s -sOwnerPassword=%s -sOutputFile=%s %s 2>&1',
                    escapeshellarg($userPwd),
                    escapeshellarg($ownerPwd),
                    escapeshellarg($outputPath),
                    escapeshellarg($inputPath)
                );
                exec($cmd, $output, $returnCode);

                if ($returnCode !== 0 || !file_exists($outputPath)) {
                    return back()->with('error', 'Protection failed (Ghostscript error).');
                }
            } else {
                // FPDI + TCPDF fallback — import pages, apply SetProtection
                $pdf = new Fpdi();
                $pdf->SetProtection(
                    ['print', 'copy'],  // permissions allowed
                    $userPwd,
                    $ownerPwd,
                    0,    // encryption strength: 0=RC4 40bit
                    null
                );

                $pageCount = $pdf->setSourceFile($inputPath);
                for ($i = 1; $i <= $pageCount; $i++) {
                    $tpl = $pdf->importPage($i);
                    $size = $pdf->getTemplateSize($tpl);
                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($tpl);
                }
                $pdf->Output($outputPath, 'F');
            }

            return response()->download($outputPath, $baseName . '_protected.pdf')
                ->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return back()->with('error', 'Protection failed: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════
    // 11. UNLOCK PDF (remove password via Ghostscript)
    // ═══════════════════════════════════════════════
    public function unlock(Request $request)
    {
        $request->validate([
            'pdf'      => 'required|file|mimes:pdf|max:51200',
            'password' => 'required|string|max:50',
        ]);

        try {
            $file = $request->file('pdf');
            $password = $request->input('password');
            $inputPath = $file->getPathname();
            $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $outputPath = $this->tmpDir . '/unlocked_' . time() . '.pdf';

            if (!$this->canExec()) {
                return back()->with('error', 'Unlock PDF requires Ghostscript which is not available on this server. Contact your hosting provider to enable exec() and install Ghostscript.');
            }

            $cmd = sprintf(
                'gs -dBATCH -dNOPAUSE -sDEVICE=pdfwrite -sPDFPassword=%s -sOutputFile=%s %s 2>&1',
                escapeshellarg($password),
                escapeshellarg($outputPath),
                escapeshellarg($inputPath)
            );

            exec($cmd, $output, $returnCode);

            if ($returnCode !== 0 || !file_exists($outputPath)) {
                return back()->with('error', 'Unlock failed — wrong password or unsupported encryption.');
            }

            return response()->download($outputPath, $baseName . '_unlocked.pdf')
                ->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return back()->with('error', 'Unlock failed: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════
    // PDF INFO (AJAX)
    // ═══════════════════════════════════════════════
    public function info(Request $request)
    {
        $request->validate(['pdf' => 'required|file|mimes:pdf|max:51200']);

        try {
            $file = $request->file('pdf');
            $parser = new PdfParser();
            $document = $parser->parseFile($file->getPathname());
            $details = $document->getDetails();
            $pages = count($document->getPages());
            $size = $file->getSize();

            $units = ['B', 'KB', 'MB', 'GB'];
            $i = $size > 0 ? floor(log($size, 1024)) : 0;
            $sizeHuman = round($size / pow(1024, $i), 1) . ' ' . $units[$i];

            return response()->json([
                'success'   => true,
                'filename'  => $file->getClientOriginalName(),
                'pages'     => $pages,
                'size'      => $size,
                'size_human'=> $sizeHuman,
                'title'     => $details['Title'] ?? '—',
                'author'    => $details['Author'] ?? '—',
                'creator'   => $details['Creator'] ?? '—',
                'producer'  => $details['Producer'] ?? '—',
                'created'   => $details['CreationDate'] ?? '—',
                'modified'  => $details['ModDate'] ?? '—',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ═══════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════

    private function parsePageRange(string $input, int $totalPages): array
    {
        if (strtolower(trim($input)) === 'all') {
            return range(1, $totalPages);
        }

        $pages = [];
        $parts = explode(',', $input);

        foreach ($parts as $part) {
            $part = trim($part);
            if (str_contains($part, '-')) {
                [$start, $end] = explode('-', $part, 2);
                $start = max(1, (int) $start);
                $end = min($totalPages, (int) $end);
                for ($i = $start; $i <= $end; $i++) {
                    $pages[] = $i;
                }
            } else {
                $p = (int) $part;
                if ($p >= 1 && $p <= $totalPages) {
                    $pages[] = $p;
                }
            }
        }

        return array_unique($pages);
    }

    private function cleanDir(string $dir): void
    {
        if (!is_dir($dir)) return;
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) @unlink($file);
        }
        @rmdir($dir);
    }

    // ═══════════════════════════════════════════════
    // PDF TO PNG (uses Ghostscript like pdfToJpg)
    // ═══════════════════════════════════════════════
    public function pdfToPng(Request $request)
    {
        $request->validate([
            'pdf'     => 'required|file|mimes:pdf|max:51200',
            'quality' => 'required|in:72,150,300',
        ]);

        try {
            $file = $request->file('pdf');
            $dpi = (int) $request->quality;
            $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $inputPath = $file->getPathname();
            $outDir = $this->tmpDir . '/png_' . time();
            mkdir($outDir, 0775, true);

            if ($this->canExec()) {
                // Ghostscript method
                $cmd = sprintf(
                    'gs -dBATCH -dNOPAUSE -sDEVICE=png16m -r%d -sOutputFile=%s %s 2>&1',
                    $dpi,
                    escapeshellarg($outDir . '/page_%03d.png'),
                    escapeshellarg($inputPath)
                );
                exec($cmd, $output, $returnCode);
                if ($returnCode !== 0) {
                    $this->cleanDir($outDir);
                    return back()->with('error', 'PDF to PNG failed (Ghostscript error).');
                }
            } elseif ($this->canImagick()) {
                // Imagick fallback
                $imagick = new \Imagick();
                $imagick->setResolution($dpi, $dpi);
                $imagick->readImage($inputPath);
                foreach ($imagick as $i => $page) {
                    $page->setImageFormat('png');
                    $page->writeImage($outDir . '/page_' . sprintf('%03d', $i + 1) . '.png');
                }
                $imagick->destroy();
            } else {
                $this->cleanDir($outDir);
                return back()->with('error', 'PDF to PNG requires Ghostscript or Imagick. Neither is available on this server.');
            }

            $images = glob($outDir . '/*.png');
            if (count($images) === 0) {
                $this->cleanDir($outDir);
                return back()->with('error', 'No images generated.');
            }

            if (count($images) === 1) {
                return response()->download($images[0], $baseName . '_page1.png')
                    ->deleteFileAfterSend(true);
            }

            $zipFile = $this->tmpDir . '/' . $baseName . '_png.zip';
            $zip = new \ZipArchive();
            $zip->open($zipFile, \ZipArchive::CREATE);
            foreach ($images as $img) {
                $zip->addFile($img, basename($img));
            }
            $zip->close();
            $this->cleanDir($outDir);

            return response()->download($zipFile, $baseName . '_png.zip')
                ->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return back()->with('error', 'PDF to PNG failed: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════
    // REPAIR PDF
    // ═══════════════════════════════════════════════
    public function repair(Request $request)
    {
        $request->validate(['pdf' => 'required|file|mimes:pdf|max:51200']);

        try {
            $file = $request->file('pdf');
            $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $pdf = new Fpdi();

            $pageCount = $pdf->setSourceFile($file->getPathname());
            for ($i = 1; $i <= $pageCount; $i++) {
                $tpl = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($tpl);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl);
            }

            $outFile = $this->tmpDir . '/repaired_' . time() . '.pdf';
            $pdf->Output($outFile, 'F');

            return response()->download($outFile, $baseName . '_repaired.pdf')
                ->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return back()->with('error', 'Repair failed: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════
    // FLATTEN PDF (strips forms/annotations)
    // ═══════════════════════════════════════════════
    public function flatten(Request $request)
    {
        $request->validate(['pdf' => 'required|file|mimes:pdf|max:51200']);

        try {
            $file = $request->file('pdf');
            $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $pdf = new Fpdi();

            $pageCount = $pdf->setSourceFile($file->getPathname());
            for ($i = 1; $i <= $pageCount; $i++) {
                $tpl = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($tpl);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl);
            }

            $outFile = $this->tmpDir . '/flattened_' . time() . '.pdf';
            $pdf->Output($outFile, 'F');

            return response()->download($outFile, $baseName . '_flattened.pdf')
                ->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return back()->with('error', 'Flatten failed: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════
    // HTML TO PDF
    // ═══════════════════════════════════════════════
    public function htmlToPdf(Request $request)
    {
        $request->validate([
            'html'        => 'required|string|max:500000',
            'orientation' => 'required|in:portrait,landscape',
            'paper'       => 'required|in:a4,letter,legal',
        ]);

        try {
            $html = $request->input('html');
            $orientation = $request->input('orientation');
            $paper = $request->input('paper');

            $pdf = Pdf::loadHTML($html)
                ->setPaper($paper, $orientation);

            return $pdf->download('html_to_pdf_' . date('Y-m-d_His') . '.pdf');

        } catch (\Exception $e) {
            return back()->with('error', 'HTML to PDF failed: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════
    // SIGN PDF (overlay signature image)
    // ═══════════════════════════════════════════════
    public function sign(Request $request)
    {
        $request->validate([
            'pdf'       => 'required|file|mimes:pdf|max:51200',
            'signature' => 'required|file|mimes:png,jpg,jpeg|max:5120',
            'page'      => 'required|integer|min:1',
            'position'  => 'required|in:bottom-right,bottom-left,bottom-center,top-right,top-left,top-center,center',
            'width'     => 'required|integer|min:30|max:300',
        ]);

        try {
            $file = $request->file('pdf');
            $sigFile = $request->file('signature');
            $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $targetPage = (int) $request->page;
            $sigWidth = (int) $request->width;

            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($file->getPathname());

            if ($targetPage > $pageCount) {
                return back()->with('error', "Page {$targetPage} doesn't exist. PDF has {$pageCount} pages.");
            }

            for ($i = 1; $i <= $pageCount; $i++) {
                $tpl = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($tpl);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl);

                if ($i === $targetPage) {
                    $pw = $size['width'];
                    $ph = $size['height'];
                    $sigHeight = $sigWidth * 0.4; // approximate aspect ratio
                    $margin = 15;

                    $positions = [
                        'bottom-right'  => [$pw - $sigWidth - $margin, $ph - $sigHeight - $margin],
                        'bottom-left'   => [$margin, $ph - $sigHeight - $margin],
                        'bottom-center' => [($pw - $sigWidth) / 2, $ph - $sigHeight - $margin],
                        'top-right'     => [$pw - $sigWidth - $margin, $margin],
                        'top-left'      => [$margin, $margin],
                        'top-center'    => [($pw - $sigWidth) / 2, $margin],
                        'center'        => [($pw - $sigWidth) / 2, ($ph - $sigHeight) / 2],
                    ];

                    [$x, $y] = $positions[$request->position] ?? $positions['bottom-right'];
                    $pdf->Image($sigFile->getPathname(), $x, $y, $sigWidth);
                }
            }

            $outFile = $this->tmpDir . '/signed_' . time() . '.pdf';
            $pdf->Output($outFile, 'F');

            return response()->download($outFile, $baseName . '_signed.pdf')
                ->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return back()->with('error', 'Sign failed: ' . $e->getMessage());
        }
    }
}
