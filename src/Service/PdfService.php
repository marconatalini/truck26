<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;

class PdfService
{

    public function pdfResponse(string $html, string $filename): Response
    {
        // 1. Configura Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true); // Utile per caricare immagini esterne

        $dompdf = new Dompdf($pdfOptions);

        // 3. Carica l'HTML in Dompdf
        $dompdf->loadHtml($html);

        // 4. (Opzionale) Imposta il formato carta
        $dompdf->setPaper('A4', 'portrait');

        // 5. Genera il PDF
        $dompdf->render();

        // 6. Invia il PDF al browser
        return new Response (
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('inline; filename="%s"', $filename)
            ]
        );
    }
}
