<?php

namespace App\Http\Controllers;

use App\Events\StatsUpdated;
use App\Events\UsersUpdated;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function list(Request $request)
    {
        $this->listBase($request);

        return $this->ok("Listado de usuarios");
    }

    public function listBase(Request $request)
    {
        $user_id = $request->user_id;

        // obtener el id de la division del usuario
        $user = UserModel::find($user_id);

        if (!$user) {
            return $this->error('Usuario no encontrado');
        }

        $division_id = $user->division_id;

        // Top 20 por score
        $usersQuery = UserModel::query()
            ->from('user as u')
            ->leftJoin('division', 'u.division_id', '=', 'division.id')
            ->leftJoin('country', 'u.country_id', '=', 'country.id')
            ->select('u.id', 'u.name', 'u.score', 'u.times_ranked_first', 'country.flag')
            ->orderBy('u.score', 'DESC')
            ->limit(20);

        if ($division_id) {
            $usersQuery->where('division.id', $division_id);
        }

        $users = $usersQuery->get();

        // Verificar si el usuario con id=1 está en los resultados
        $containsUser1 = $users->contains('id', 1);

        if (!$containsUser1) {
            // Obtener el usuario con id=1
            $user1 = UserModel::query()
                ->from('user as u')
                ->leftJoin('division', 'u.division_id', '=', 'division.id')
                ->leftJoin('country', 'u.country_id', '=', 'country.id')
                ->select('u.id', 'u.name', 'u.score', 'u.times_ranked_first', 'country.flag')
                ->where('u.id', 1)
                ->first();

            if ($user1) {
                // Mantener top 19 y agregar al usuario con id=1 al final
                $users = $users->slice(0, 19)->values();
                $users->push($user1);
            }
        }

        $response = ['users' => $users->values() ?? []];
        // resetear índices

        // Emitir evento de actualización de usuarios
        broadcast(new UsersUpdated($user_id, $response))->toOthers();

        return $users->values(); // resetear índices
    }


    public function getUser(Request $request)
    {
        $user_id = $request->user_id;

        $user = UserModel::query()
            ->select('user.id', 'user.name', 'user.coins', 'user.lives', 'user.score', 'user.streak')
            ->where('user.id', $user_id)
            ->first();

        if (!$user) {
            return $this->error('Usuario no encontrado');
        }

        broadcast(new StatsUpdated($user))->toOthers();

        return $this->ok("Usuario");
    }

    public function updateCoins(Request $request)
    {
        DB::beginTransaction();
        try {
            $user_id = $request->user_id;
            $coins = $request->coins;

            // obtener el id de la division del usuario
            $user = UserModel::find($user_id);

            if (!$user) {
                return $this->error('Usuario no encontrado');
            }

            $user->coins += $coins;
            $user->save();

            broadcast(new StatsUpdated($user))->toOthers();

            DB::commit();

            return $this->ok('Coins actualizados');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
            DB::rollBack();
        }
    }

    public function updateLives(Request $request)
    {
        DB::beginTransaction();
        try {
            $user_id = $request->user_id;
            $lives = $request->lives;

            // obtener el id de la division del usuario
            $user = UserModel::find($user_id);

            if (!$user) {
                return $this->error('Usuario no encontrado');
            }

            $user->lives += $lives;
            $user->save();

            broadcast(new StatsUpdated($user))->toOthers();

            DB::commit();

            return $this->ok('Lives actualizados');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
            DB::rollBack();
        }
    }

    public function updateStreak(Request $request)
    {
        DB::beginTransaction();
        try {
            $user_id = $request->user_id;
            $streak = $request->streak;

            // obtener el id de la division del usuario
            $user = UserModel::find($user_id);

            if (!$user) {
                return $this->error('Usuario no encontrado');
            }

            $user->streak += $streak;
            $user->save();

            broadcast(new StatsUpdated($user))->toOthers();

            DB::commit();

            return $this->ok('Streak actualizados');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
            DB::rollBack();
        }
    }
}
