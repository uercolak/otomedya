<?php

namespace App\Controllers;

class PagesController extends BaseController
{
    public function contact()
    {
        return view('pages/contact');
    }

    public function contactPost()
    {
        // Göstermelik: Mail göndermiyoruz (audit için yeterli)
        // İstersen loglayabiliriz:
        log_message('info', 'Contact form submitted: ' . json_encode([
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'message' => $this->request->getPost('message'),
            'ip' => $this->request->getIPAddress(),
        ]));

        return redirect()->to(base_url('/contact'))
            ->with('success', 'Mesajınız alınmıştır. En kısa sürede dönüş yapacağız.');
    }
}
