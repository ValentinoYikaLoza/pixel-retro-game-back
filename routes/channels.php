<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Canal para actualizaciones de estadísticas de usuario
Broadcast::channel('user.stats.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal para actualizaciones de misiones de usuario
Broadcast::channel('user.missions.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal para actualizaciones de usuarios
Broadcast::channel('user.users.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
