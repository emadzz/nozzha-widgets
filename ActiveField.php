<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace nozzha\widgets;

class ActiveField extends \yii\widgets\ActiveField {

    public function getPublicClientOptions() {
        return $this->getClientOptions();
    }

}
