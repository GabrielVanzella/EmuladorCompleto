<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;

class ManualController extends Controller {

    public function index(): void {
        Auth::requireAdmin();
        $this->view('admin.manual.index', [], 'manual');
    }
}
