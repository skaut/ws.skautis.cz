<?php

namespace App;

class DefaultPresenter extends BasePresenter {

    protected function startup() {
        parent::startup();

//        if($this->user->isLoggedIn())
//            $this->redirect("Accountancy:Default:");
    }

    public function renderSendRequest() {
        
    }

    public function renderFio() {
        $Fio = new Fio();
        $Fio->Account = '2600347808';
        $Fio->UserName = 'baxa.marek';
        $Fio->Password = 'stredisko';
        $Records = $Fio->Import(time() - 3600 * 24 * 31 * 2, time());
//        foreach ($Records as $key => $value) {
//            foreach ($value as $key2 => $val2) {
//                $resIn[$key2] = iconv('WINDOWS-1250', 'UTF-8//IGNORE', $val2);
//            }
//            $res[$key] = $resIn;
//        }
//        unset($res[0]);
//        $this->template->data = $res;
        echo('<table border="1">');
        echo('<tr>');
        echo('<th>Datum</th>');
        echo('<th>Částka</th>');
        echo('<th>Účet protistrany</th>');
        echo('<th>Kód banky</th>');
        echo('<th>KS</th>');
        echo('<th>VS</th>');
        echo('<th>SS</th>');
        echo('<th>Uživatelská identifikace</th>');
        echo('</tr>');
        foreach ($Records as $Record) {
            if ($Record['Type'] == GPC_TYPE_ITEM) {
                echo('<tr>');
                echo('<td>' . date('j.n.Y', $Record['DueDate']) . '</td>');
                echo('<td>' . $Record['Value'] . '</td>');
                echo('<td>' . $Record['OffsetAccount'] . '</td>');
                echo('<td>' . $Record['BankCode'] . '</td>');
                echo('<td>' . $Record['ConstantSymbol'] . '</td>');
                echo('<td>' . $Record['VariableSymbol'] . '</td>');
                echo('<td>' . $Record['SpecificSymbol'] . '</td>');
                echo('<td>' . $Record['ClientName'] . '</td>');
                echo('</tr>');
            }
        }
        echo('</table>');


        die();
    }

}
