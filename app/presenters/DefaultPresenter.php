<?php

namespace App;

class DefaultPresenter extends BasePresenter {
    public function renderSendRequest() {
        
    }
    
    public function actionWs() {
        $this->redirect("default");//historické přesměrování
    }
}
