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

namespace Modules\Aggiornamenti\Controlli;

use Modules\Fatture\Fattura;
use Util\XML;

class DatiFattureElettroniche extends Controllo
{
    public function getName()
    {
        return tr('Corrispondenze XML FE e Documenti di vendita');
    }

    public function getType($record)
    {
        return 'info';
    }

    public function check()
    {
        $fatture_vendita = Fattura::vendita()
            ->whereNotIn('codice_stato_fe', ['ERR', 'NS', 'EC02', 'ERVAL'])
            ->where('data', '>=', session('period_start'))
            ->where('data', '<=', session('period_end'))
            ->orderBy('data')
            ->get();

        foreach ($fatture_vendita as $fattura_vendita) {
            $this->checkFattura($fattura_vendita);
        }
    }

    public function checkFattura(Fattura $fattura_vendita)
    {
        try {
            $xml = XML::read($fattura_vendita->getXML());
            $totale_documento_xml = null;

            // Totale basato sul campo ImportoTotaleDocumento
            $dati_generali = $xml['FatturaElettronicaBody']['DatiGenerali']['DatiGeneraliDocumento'];
            $dati_anagrafici = $xml['FatturaElettronicaHeader']['CessionarioCommittente']['DatiAnagrafici'];
            if (isset($dati_generali['ImportoTotaleDocumento'])) {
                $totale_documento_indicato = abs(floatval($dati_generali['ImportoTotaleDocumento']));

                // Calcolo del totale basato sui DatiRiepilogo
                if (empty($totale_documento_xml) && empty($dati_generali['ScontoMaggiorazione'])) {
                    $totale_documento_xml = 0;

                    $riepiloghi = $xml['FatturaElettronicaBody']['DatiBeniServizi']['DatiRiepilogo'];
                    if (!empty($riepiloghi) && !isset($riepiloghi[0])) {
                        $riepiloghi = [$riepiloghi];
                    }

                    foreach ($riepiloghi as $riepilogo) {
                        $totale_documento_xml = sum([$totale_documento_xml, $riepilogo['ImponibileImporto'], $riepilogo['Imposta']]);
                    }

                    $totale_documento_xml = abs($totale_documento_xml);
                } else {
                    $totale_documento_xml = $totale_documento_indicato;
                }
            }

            // Se riscontro un'anomalia
            if ($fattura_vendita->anagrafica->piva != $dati_anagrafici['IdFiscaleIVA']['IdCodice'] || $fattura_vendita->anagrafica->codice_fiscale != $dati_anagrafici['CodiceFiscale'] || $fattura_vendita->totale != $totale_documento_xml) {
                $anomalia = [
                    'fattura_vendita' => $fattura_vendita,
                    'codice_fiscale_xml' => !empty($dati_anagrafici['CodiceFiscale']) ? $dati_anagrafici['CodiceFiscale'] : null,
                    'codice_fiscale' => $fattura_vendita->anagrafica->codice_fiscale,
                    'piva_xml' => !empty($dati_anagrafici['IdFiscaleIVA']['IdCodice']) ? $dati_anagrafici['IdFiscaleIVA']['IdCodice'] : null,
                    'piva' => $fattura_vendita->anagrafica->piva,
                    'totale_documento_xml' => moneyFormat($totale_documento_xml, 2),
                    'totale_documento' => moneyFormat($fattura_vendita->totale, 2),
                ];

                $riepilogo_anomalie = '
                            <div>
                                <div class="col-md-3" style="background-color:#efefef;" >Sorgente</div>
                                <div class="col-md-3" style="background-color:#efefef;" >P. Iva</div>
                                <div class="col-md-3" style="background-color:#efefef;" >Cod. fiscale</div>
                                <div class="col-md-3" style="background-color:#efefef;" >Totale</div>
                            </div>
                        
                            <div>
                                <div class="col-md-3" >XML</div>
                                <div class="col-md-3" >'.$anomalia['piva_xml'].'</div>
                                <div class="col-md-3" >'.$anomalia['codice_fiscale_xml'].'</div>
                                <div class="col-md-3" >'.$anomalia['totale_documento_xml'].'</div>
                            </div>

                            <div>
                                <div class="col-md-3">Gestionale</div>
                                <div class="col-md-3">'.$this->htmlDiff($anomalia['piva_xml'], $anomalia['piva']).'</div>
                                <div class="col-md-3">'.$this->htmlDiff($anomalia['codice_fiscale_xml'], $anomalia['codice_fiscale']).'</div>
                                <div class="col-md-3">'.$this->htmlDiff($anomalia['totale_documento_xml'], $anomalia['totale_documento']).'</div>
                            </div>';

                $this->addResult([
                    'id' => $fattura_vendita->id,
                    'nome' => $fattura_vendita->getReference(),
                    'descrizione' => $riepilogo_anomalie,
                ]);
            }
        } catch (\Exception $e) {
            $this->addResult([
                'id' => $fattura_vendita->id,
                'nome' => $fattura_vendita->getReference(),
                'descrizione' => tr("Impossibile verificare l'XML di questa fattura"),
            ]);
        }
    }

    public function execute($record, $params = [])
    {
        return false;
    }

    protected function diff($old, $new)
    {
        $matrix = [];
        $maxlen = 0;
        foreach ($old as $oindex => $ovalue) {
            $nkeys = array_keys($new, $ovalue);
            foreach ($nkeys as $nindex) {
                $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
                    $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
                if ($matrix[$oindex][$nindex] > $maxlen) {
                    $maxlen = $matrix[$oindex][$nindex];
                    $omax = $oindex + 1 - $maxlen;
                    $nmax = $nindex + 1 - $maxlen;
                }
            }
        }
        if ($maxlen == 0) {
            return [['d' => $old, 'i' => $new]];
        }

        return array_merge(
            $this->diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
            array_slice($new, $nmax, $maxlen),
            $this->diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
    }

    protected function htmlDiff($old, $new)
    {
        $ret = '';
        $diff = $this->diff(preg_split("/[\s]+/", $old), preg_split("/[\s]+/", $new));
        foreach ($diff as $k) {
            if (is_array($k)) {
                $ret .= (!empty($k['d']) ? '<del>'.implode(' ', $k['d']).'</del> ' : '').
                    (!empty($k['i']) ? '<span>'.implode(' ', $k['i']).'</span> ' : '');
            } else {
                $ret .= $k.' ';
            }
        }

        return $ret;
    }
}
