<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function ok($message = null, $data = null)
    {

        $array = [
            'success' => true,
        ];

        if ($message) {
            $array['message'] = $message;
        }

        // !is_null para que las listas/colecciones vacías sigan devolviendo data: [].
        if (!is_null($data)) {
            $array['data'] = $data;
        }
        return response()->json($array, 200);
    }

    public function error($message = '', $status = 500)
    {

        $array = [
            'success' => false,
        ];

        if ($message) {
            $array['message'] = $message;
        }

        return response()->json($array,  $status);
    }

    public function internalServerError()
    {
        return $this->error("Ha surgido un error, inténtelo nuevamente.");
    }

    public function error403()
    {
        return $this->error("Acceso denegado. Perfil no autorizado.");
    }
}
