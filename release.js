// Librerie NPM richieste per l'esecuzione
var del = require('del');
var shell = require('shelljs');
var inquirer = require('inquirer');

// Operazioni per la release
function release() {
    var archiver = require('archiver');
    var fs = require('fs');

    // Rimozione file indesiderati
    del([
        './vendor/tecnickcom/tcpdf/fonts/*',
        '!./vendor/tecnickcom/tcpdf/fonts/*helvetica*',
        './vendor/mpdf/mpdf/tmp/*',
        './vendor/mpdf/mpdf/ttfonts/*',
        '!./vendor/mpdf/mpdf/ttfonts/DejaVuinfo.txt',
        '!./vendor/mpdf/mpdf/ttfonts/DejaVu*Condensed*',
        './vendor/maximebf/debugbar/src/DebugBar/Resources/vendor/*',
        './vendor/respect/validation/tests/*',
    ]);

    // Impostazione dello zip
    var output = fs.createWriteStream('./release.zip');
    var archive = archiver('zip');

    output.on('close', function () {
        console.log('ZIP completato!');
    });

    archive.on('error', function (err) {
        throw err;
    });

    archive.pipe(output);

    // Aggiunta dei file
    archive.glob('**/*', {
        dot: true,
        ignore: [
            '.git/**',
            'node_modules/**',
            'backup/**',
            'files/**',
            'logs/**',
            'config.inc.php',
            '**/*.lock',
            '**/*.phar',
            '**/*.log',
            '**/*.zip',
            '**/*.bak',
            '**/*.jar',
            '**/*.txt',
            '**/~*',
        ]
    });

    // Eccezioni
    archive.file('backup/.htaccess');
    archive.file('files/.htaccess');
    archive.file('files/my_impianti/componente.ini');
    archive.file('logs/.htaccess');

    // Aggiunta del commit corrente nel file REVISION
    archive.append(shell.exec('git rev-parse --short HEAD', {
        silent: true
    }).stdout, {
        name: 'REVISION'
    });

    // Opzioni sulla release
    inquirer.prompt([{
        type: 'input',
        name: 'version',
        message: 'Numero di versione:',
    }, {
        type: 'confirm',
        name: 'beta',
        message: 'Versione beta?',
        default: false,
    }]).then(function (result) {
        version = result.version;

        if (result.beta) {
            version += 'beta';
        }

        archive.append(version, {
            name: 'VERSION'
        });

        // Completamento dello zip
        archive.finalize();
    });
};

release();
