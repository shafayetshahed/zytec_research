<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Quotation;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Renders quotations, payment receipts and final invoices as PDF.
 * Remote resources are disabled; images load only from public/images.
 */
class PdfDocumentService
{
    public function quotation(Quotation $quotation): string
    {
        return $this->render('documents.quotation', ['quotation' => $quotation]);
    }

    public function receipt(Payment $payment, Quotation $quotation): string
    {
        return $this->render('documents.receipt', ['payment' => $payment, 'quotation' => $quotation]);
    }

    public function invoice(Quotation $quotation): string
    {
        return $this->render('documents.invoice', ['quotation' => $quotation]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function html(string $view, array $data): string
    {
        return view($view, $data + [
            'business' => config('zytecs.business'),
            'logoPath' => public_path('images/brand/logo-480.png'),
        ])->render();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function render(string $view, array $data): string
    {
        $options = new Options;
        $options->setIsRemoteEnabled(false);
        $options->setIsPhpEnabled(false);
        $options->setIsJavascriptEnabled(false);
        $options->setChroot(public_path('images'));
        $options->setDefaultFont('DejaVu Sans');
        $options->setTempDir(storage_path('framework/cache'));
        $options->setFontCache(storage_path('framework/cache'));

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($this->html($view, $data));
        $dompdf->setPaper('A4');
        $dompdf->render();

        return (string) $dompdf->output();
    }
}
