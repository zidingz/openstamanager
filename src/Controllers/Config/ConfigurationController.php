<?php

namespace Controllers\Config;

use App;
use Controllers\Controller;
use Database;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ConfigurationController extends Controller
{
    public static function isConfigured()
    {
        $config = App::getContainer()['config'];

        $valid_config = isset($config['db_host']) && isset($config['db_name']) && isset($config['db_username']) && isset($config['db_password']);

        // Gestione del file di configurazione
        if (file_exists(DOCROOT.'/config.inc.php') && $valid_config && database()->isConnected()) {
            return true;
        }

        return false;
    }

    public function configuration(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $this->permission($request, $response);

        $args['license'] = file_get_contents(DOCROOT.'/LICENSE');
        $args['languages'] = [
            'it_IT' => [
                'title' => tr('Italiano'),
                'flag' => 'IT',
            ],
            'en_GB' => [
                'title' => tr('Inglese'),
                'flag' => 'GB',
            ],
        ];

        $response = $this->twig->render($response, 'config\configuration.twig', $args);

        return $response;
    }

    public function configurationSave(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $this->permission($request, $response);

        // Controllo sull'esistenza di nuovi parametri di configurazione
        $host = post('host');
        $database_name = post('database_name');
        $username = post('username');
        $password = post('password');

        $database = new Database($host, $username, $password, $database_name);
        if (!$database->isConnected()) {
            return $this->twig->render($response, 'config\messages\error.twig', $args);
        }

        // Impostazioni di configurazione strettamente necessarie al funzionamento del progetto
        $new_config = file_get_contents(DOCROOT.'/config.example.php');

        $decimals = post('decimal_separator');
        $thousands = post('thousand_separator');
        $decimals = $decimals == 'dot' ? '.' : ',';
        $thousands = $thousands == 'dot' ? '.' : $thousands;
        $thousands = $thousands == 'comma' ? ',' : $thousands;

        $values = [
            '|host|' => $host,
            '|username|' => $username,
            '|password|' => $password,
            '|database|' => $database_name,
            '|lang|' => post('lang'),
            '|timestamp|' => post('timestamp_format'),
            '|date|' => post('date_format'),
            '|time|' => post('time_format'),
            '|decimals|' => $decimals,
            '|thousands|' => $thousands,
        ];
        $new_config = str_replace(array_keys($values), $values, $new_config);

        // Controlla che la scrittura del file di configurazione sia andata a buon fine
        $creation = file_put_contents('config.inc.php', $new_config);

        if (!$creation) {
            $args['database_name'] = $database_name;
            $args['username'] = $username;
            $args['password'] = $password;
            $args['host'] = $host;

            $args['config'] = $new_config;

            $response = $this->twig->render($response, 'config\messages\writing.twig', $args);
        } else {
            // Creazione manifest.json
            $manifest = '{
    "dir" : "ltr",
    "lang" : "it-IT",
    "name" : "OpenSTAManager",
    "scope" : "'.ROOTDIR.'",
    "display" : "fullscreen",
    "start_url" : "'.ROOTDIR.'",
    "short_name" : "OSM",
    "theme_color" : "transparent",
    "description" : "OpenSTAManager",
    "orientation" : "any",
    "background_color" : "transparent",
    "generated" : "true",
    "icons" : [
        {
            "src": "assets/dist/img/logo.png",
            "type": "image/png",
            "sizes": "512x512"
        }
    ]
}';
            file_put_contents('manifest.json', $manifest);

            $response = $response->withRedirect($this->router->pathFor('login'));
        }

        return $response;
    }

    public function configurationTest(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $this->permission($request, $response);

        // Controllo sull'esistenza di nuovi parametri di configurazione
        $host = post('host');
        $database_name = post('database_name');
        $username = post('username');
        $password = post('password');

        // Generazione di una nuova connessione al database
        $database = new Database($host, $username, $password, $database_name);

        // Test della configurazione
        if (!empty($database) && $database->isConnected()) {
            $requirements = [
                'SELECT',
                'INSERT',
                'UPDATE',
                'CREATE',
                'ALTER',
                'DROP',
            ];

            $host = str_replace('_', '\_', $database_name);
            $database_name = str_replace('_', '\_', $database_name);
            $username = str_replace('_', '\_', $database_name);

            $results = $database->fetchArray('SHOW GRANTS FOR CURRENT_USER');
            foreach ($results as $result) {
                $privileges = current($result);

                if (
                    str_contains($privileges, ' ON `'.$database_name.'`.*') ||
                    str_contains($privileges, ' ON *.*')
                ) {
                    $pieces = explode(', ', explode(' ON ', str_replace('GRANT ', '', $privileges))[0]);

                    // Permessi generici sul database
                    if (in_array('ALL', $pieces) || in_array('ALL PRIVILEGES', $pieces)) {
                        $requirements = [];
                        break;
                    }

                    // Permessi specifici sul database
                    foreach ($requirements as $key => $value) {
                        if (in_array($value, $pieces)) {
                            unset($requirements[$key]);
                        }
                    }
                }
            }

            // Permessi insufficienti
            if (!empty($requirements)) {
                $state = 1;
            }

            // Permessi completi
            else {
                $state = 2;
            }
        }

        // Connessione fallita
        else {
            $state = 0;
        }

        $response = $response->write($state);

        return $response;
    }

    protected function permission($request, $response)
    {
        if (self::isConfigured()) {
            throw new \Slim\Exception\NotFoundException($request, $response);
        }
    }
}
