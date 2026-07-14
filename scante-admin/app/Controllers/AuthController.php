<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Usuario;

class AuthController extends Controller {

    public function login(): void {
        if (Auth::check()) {
            $this->redirect(Auth::isAdmin() ? '/admin' : '/empresa');
        }

        if ($this->isPost()) {
            $email = $this->sanitize($this->input('email', ''));
            $senha = $this->input('senha', '');

            $model   = new Usuario();
            $usuario = $model->findByEmail($email);

            if ($usuario && $model->verificarSenha($senha, $usuario['senha'])) {
                Auth::login($usuario);
                $this->redirect($usuario['tipo'] === 'admin' ? '/admin' : '/empresa');
            }

            $erro = 'E-mail ou senha incorretos.';
        }

        $this->view('auth.login', ['erro' => $erro ?? null], 'auth');
    }

    public function logout(): void {
        Auth::logout();
        $this->redirect('/login');
    }
}
