<?php
namespace App\Controllers;

use App\Core\Controller;

class RelayDownloadController extends Controller {

    public function index(): void {
        $this->view('relay.index', [
            'downloadUrl' => APP_URL . '/downloads/ScanTE-Relay-Setup.exe',
        ], 'relay');
    }
}
