<?php

namespace App\Http\Controllers;

use App\Events\DivisionUpdated;
use App\Models\DivisionModel;
use App\Models\UserModel;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function list()
    {
        $divisions = $this->listBase();

        return $this->ok("Listado de Divisiones", $divisions);
    }

    public function listBase()
    {
        $divisions = DivisionModel::all();

        return $divisions;
    }

    public function getCurrentDivision(Request $request)
    {
        $user_id = $request->user_id;

        $user = UserModel::find($user_id);

        if (!$user) {
            return $this->error("Usuario no encontrado");
        }

        $division_id = $user->division_id;

        $division = DivisionModel::find($division_id);

        if (!$division) {
            return $this->error("Division no encontrada");
        }

        broadcast(new DivisionUpdated($user_id, $division))->toOthers();

        return $this->ok("Division actual");
    }
}
