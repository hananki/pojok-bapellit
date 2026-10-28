<?php

namespace App\Services\Chatbot\Tools;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

/**
 * Helper bersama untuk semua tool: membuat Request internal dan menerjemahkan response controller.
 */
class ToolSupport
{
    /**
     * [FIX] Buat Request internal yang "menyerupai" request user asli
     * (method POST, minta JSON, user & session terpasang) untuk dipakai controller lain.
     */
    public function makeRequest(array $arguments, ?Authenticatable $user): Request
    {
        $request = Request::create('/', 'POST', $arguments);
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(fn () => $user);

        $current = request();
        if ($current->hasSession()) {
            $request->setLaravelSession($current->session());
        }

        return $request;
    }

    /**
     * Buat FormRequest (mis. StoreSuratRequest) dari argumen, lengkap dengan validasi.
     * Dipakai untuk memanggil method controller yang type-hint-nya FormRequest.
     * Melempar ValidationException bila validasi gagal.
     *
     * @template T of FormRequest
     * @param class-string<T> $class
     * @return T
     */
    public function makeFormRequest(string $class, array $arguments, ?Authenticatable $user): FormRequest
    {
        $base = $this->makeRequest($arguments, $user);

        /** @var FormRequest $form */
        $form = $class::createFrom($base);
        $form->setContainer(app())->setRedirector(app('redirect'));
        $form->setUserResolver(fn () => $user);
        $form->validateResolved();

        return $form;
    }

    /**
     * [FIX] Terjemahkan response controller (JsonResponse / RedirectResponse) menjadi teks hasil tool.
     */
    public function summarize(mixed $response, string $successMessage): string
    {
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $status = $response->getStatusCode();
            $data = $response->getData(true);

            if ($status >= 400) {
                $msg = is_array($data)
                    ? ($data['message'] ?? $data['error'] ?? json_encode($data, JSON_UNESCAPED_UNICODE))
                    : (string) $data;

                return 'Gagal: '.(is_string($msg) ? $msg : json_encode($msg, JSON_UNESCAPED_UNICODE));
            }

            return $successMessage;
        }

        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            $session = $response->getSession();
            if ($session && $session->has('error')) {
                $err = $session->get('error');

                return 'Gagal: '.(is_string($err) ? $err : json_encode($err, JSON_UNESCAPED_UNICODE));
            }
        }

        return $successMessage;
    }
}