<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QuotationDetails extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $data;
    public $pdf;

    public function __construct($data, $pdf = null)
    {
        $this->data = $data;
        $this->pdf = $pdf;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $quotation_data = $this->data;
        $mail = $this->view('backend.mail.quotation_details', compact('quotation_data'))
                     ->subject('Quotation Details - ' . $quotation_data['reference_no']);
        
        if ($this->pdf) {
            $mail->attachData($this->pdf, 'quotation_' . $quotation_data['reference_no'] . '.pdf', [
                'mime' => 'application/pdf',
            ]);
        }
        
        return $mail;
    }
}
