<?php
/*
 * OpenSTAManager: il software gestionale open source per l'assistenza tecnica e la fatturazione
 * Copyright (C) DevCode s.n.c.
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

namespace Controllers\Config;

use Controllers\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Update;

class UpdateController extends Controller
{
    protected static $updateRate = 20;
    protected static $scriptValue = 100;

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $this->permission($request, $response);

        $total = 0;
        $updates = Update::getTodoUpdates();

        if (!Update::isUpdateAvailable()) {
            throw new HttpNotFoundException($request);
        }

        foreach ($updates as $update) {
            if ($update['sql'] && (!empty($update['done']) || is_null($update['done']))) {
                $queries = readSQLFile(DOCROOT.$update['directory'].$update['filename'].'.sql', ';');
                $total += count($queries);

                if (intval($update['done']) > 1) {
                    $total -= intval($update['done']) - 2;
                }
            }

            if ($update['script']) {
                $total += self::$scriptValue;
            }
        }

        // Inizializzazione
        if (Update::isUpdateLocked() && filter('force') === null) {
            $response = $this->twig->render($response, '@resources/config/messages/blocked.twig', $args);
        } else {
            $args = array_merge($args, [
                'installing' => intval(!$this->database->isInstalled()),
                'total_updates' => count($updates),
                'total_count' => $total,
            ]);

            $response = $this->twig->render($response, '@resources/config/update.twig', $args);
        }

        return $response;
    }

    public function updateProgress(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $this->permission($request, $response);

        // Aggiornamento in progresso
        $update = Update::getCurrentUpdate();

        $result = Update::doUpdate(self::$updateRate);

        $args = array_merge($args, [
            'update_name' => $update['name'],
            'update_version' => $update['version'],
            'update_filename' => $update['filename'],
        ]);

        if (!empty($result)) {
            $rate = 0;
            if (is_array($result)) {
                $rate = $result[1] - $result[0];
            } elseif (!empty($update['script'])) {
                $rate = self::$scriptValue;
            }

            $args = array_merge($args, [
                'show_sql' => is_array($result) && $result[1] == $result[2],
                'show_script' => is_bool($result),
                'rate' => $rate,
            ]);
        }

        $args['is_completed'] = false;
        if (is_bool($result)) {
            Update::updateCleanup();

            $args['is_completed'] = count(Update::getTodoUpdates()) == 1;
        }

        $response = $this->twig->render($response, '@resources/config/messages/piece.twig', $args);

        return $response;
    }

    protected function permission($request, $response)
    {
        if (!ConfigurationController::isConfigured() || !Update::isUpdateAvailable()) {
            throw new HttpNotFoundException($request);
        }
    }
}
