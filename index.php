<?php
/*
 * OpenSTAManager: il software gestionale open source per l'assistenza tecnica e la fatturazione
 * Copyright (C) DevCode s.r.l.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$skip_permissions = true;
include_once __DIR__.'/core.php';

$op = filter('op');

// LOGIN
switch ($op) {
    case 'login':
        $username = post('username');
        $password = post('password');

        $user = User::where('username', $username)->first();
        if (!empty($user) && Hash::check($password, $user->getAuthPassword())) {
            auth()->loginUsingId($user->id, true);

        // Rimozione log vecchi
            //$dbo->query('DELETE FROM `zz_operations` WHERE DATE_ADD(`created_at`, INTERVAL 30*24*60*60 SECOND) <= NOW()');
        } else {
            $status = auth()->user();

            //flash()->error(auth()->getStatus()[$status]['message']);

            redirect('/');
        }

        break;

    case 'logout':
        auth()->logout();

        redirect(route('login'));
        break;
}

if (auth()->check() && isset($dbo) && $dbo->isConnected() && $dbo->isInstalled()) {
    $module = 1;

    if (!empty($module)) {
        redirect_legacy(base_url().'/controller.php?id_module='.$module);
    } else {
        redirect_legacy(base_url().'/index.php?op=logout');
    }
    throw new \App\Exceptions\LegacyExitException();
}

// Procedura di installazione
include_once base_dir().'/include/init/configuration.php';

// Procedura di aggiornamento
include_once base_dir().'/include/init/update.php';

// Procedura di inizializzazione
include_once base_dir().'/include/init/init.php';

$pageTitle = tr('Login');

include_once AppLegacy::filepath('include|custom|', 'top.php');

// Controllo se è una beta e in caso mostro un warning
if (Update::isBeta()) {
    echo '
			<div class="clearfix">&nbsp;</div>
			<div class="alert alert-warning alert-dismissable col-md-6 col-md-push-3 text-center fade in">
				<i class="fa fa-warning"></i> <b>'.tr('Attenzione!').'</b> '.tr('Stai utilizzando una versione <b>non stabile</b> di OSM.').'

                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
			</div>';
}

// Controllo se è una beta e in caso mostro un warning
if (false) {
    echo '
            <div class="box box-danger box-center" id="brute">
                <div class="box-header with-border text-center">
                    <h3 class="box-title">'.tr('Attenzione').'</h3>
                </div>

                <div class="box-body text-center">
                <p>'.tr('Sono stati effettuati troppi tentativi di accesso consecutivi!').'</p>
                <p>'.tr('Tempo rimanente (in secondi)').': <span id="brute-timeout">'.(auth()->getBruteTimeout() + 1).'</span></p>
                </div>
            </div>
            <script>
            $(document).ready(function(){
                $(".login-box").fadeOut();
                brute();
            });

            function brute() {
                var value = parseFloat($("#brute-timeout").html()) - 1;
                $("#brute-timeout").html(value);

                if(value > 0){
                    setTimeout("brute()", 1000);
                } else{
                    $("#brute").fadeOut();
                    $(".login-box").fadeIn();
                }
            }
            </script>';
}

if (!empty(flash()->getMessage('error'))) {
    echo '
            <script>
			$(document).ready(function(){
                $(".login-box").effect("shake");
            });
            </script>';
}

echo '
			<form action="?op=login" method="post" class="login-box box" autocomplete="off" >
				<div class="box-header with-border text-center">
					<img src="'.AppLegacy::getPaths()['img'].'/logo.png" class="logo-image" alt="'.tr('OSM Logo').'">
					<h3 class="box-title">'.tr('OpenSTAManager').'</h3>
				</div>

				<div class="login-box-body box-body">
					<div class="form-group input-group">
						<span class="input-group-addon before"><i class="fa fa-user"></i> </span>
						<input type="text" name="username" autocomplete="username" class="form-control" placeholder="'.tr('Nome utente').'"';
if (isset($username)) {
    echo ' value="'.$username.'"';
}
echo ' required>
					</div>

					{[ "type": "password", "name": "password", "autocomplete": "current-password", "placeholder": "'.tr('Password').'", "icon-before": "<i class=\"fa fa-lock\"></i>" ]}

                    <div class="text-right">
                        <small><a href="'.base_url().'/reset.php">'.tr('Password dimenticata?').'</a></small>
                    </div>
				</div>

				<!-- /.box-body -->
				<div class="box-footer">
					<button type="submit" id="login" class="btn btn-danger btn-block">'.tr('Accedi').'</button>
				</div>
				<!-- box-footer -->
			</form>
			<!-- /.box -->

            <script>
            $(document).ready( function(){
                $("#login").click(function(){
                    $("#login").text("'.tr('Autenticazione').'...");
                });

                if( $("input[name=username]").val() == ""){
                    $("input[name=username]").focus();
                }
                else{
                    $("input[name=password]").focus();
                }
            });
            </script>';

include_once AppLegacy::filepath('include|custom|', 'bottom.php');
