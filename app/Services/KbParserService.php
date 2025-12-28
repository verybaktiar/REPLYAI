<?php

namespace App\Services;

use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Log;

class KbParserService
{
    /**
     * Parse file PDF atau TXT menjadi teks.
     *
     * @param string $filePath
     * @param string $mimeType
     * @return string
     * @throws \Exception
     */
    public function parseFile(string $filePath, string $mimeType): string
    {
        if ($mimeType === 'application/pdf') {
            return $this->parsePdf($filePath);
        }

        if ($mimeType === 'text/plain') {
            return $this->parseTxt($filePath);
        }

        throw new \Exception("Unsupported mime type: " . $mimeType);
    }

    protected function parsePdf(string $path): string
    {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($path);
            $text = $pdf->getText();
            
            // Basic cleanup
            $text = preg_replace('/\s+/', ' ', $text); // Replace multiple spaces/newlines with single space
            return trim($text);
        } catch (\Exception $e) {
            Log::error("Failed to parse PDF: " . $e->getMessage());
            throw new \Exception("Gagal membaca file PDF.");
        }
    }

    protected function parseTxt(string $path): string
    {
        try {
            $text = file_get_contents($path);
            if ($text === false) {
                throw new \Exception("Cannot read file.");
            }
            return trim($text);
        } catch (\Exception $e) {
            Log::error("Failed to parse TXT: " . $e->getMessage());
            throw new \Exception("Gagal membaca file TXT.");
        }
    }
}
