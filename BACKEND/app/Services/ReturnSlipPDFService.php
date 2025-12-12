<?php

namespace App\Services;

use App\Models\Borrows;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class ReturnSlipPDFService
{
    /**
     * Generate PDF return slip
     *
     * @param Borrows $borrow
     * @return string PDF file path
     */
    public function generate(Borrows $borrow): string
    {
        // Load all relationships needed for PDF
        $borrow->load([
            'borrower.student',
            'borrower.teacher',
            'details.deviceUnit.device',
            'returnedByStaff'
        ]);

        // Generate QR code for verification
        $verificationUrl = config('app.url') . "/verify-return-slip/{$borrow->id}";
        $qrCode = base64_encode(
            QrCode::format('png')
                ->size(150)
                ->generate($verificationUrl)
        );

        // Prepare data for view
        $data = [
            'borrow' => $borrow,
            'qrCode' => $qrCode,
            'generatedAt' => now(),
        ];

        $pdf = Pdf::loadView('pdfs.return-slip', $data)
            ->setPaper('a4', 'portrait');


        $filename = "return_slips/return_{$borrow->id}_" . time() . ".pdf";

 
        Storage::disk('public')->put($filename, $pdf->output());


        $borrow->return_slip_pdf_path = $filename;
        $borrow->return_slip_generated_at = now();
        $borrow->save();

        return $filename;
    }

    /**
     * Get PDF download URL
     *
     * @param string $path
     * @return string
     */
    public function getDownloadUrl(string $path): string
    {
        return Storage::disk('public')->url($path);
    }
}
