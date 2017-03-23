<?php

namespace App;

class DefaultPresenter extends BasePresenter
{

    public function renderSendRequest()
    {

    }

    public function actionTutorial()
    {
        $this->redirect("Test:"); //historické přesměrování
    }

    public function actionWs()
    {
        $this->redirect("default"); //historické přesměrování
    }

    public function actionTestsis()
    {
        $this->redirect("Test:"); //historické přesměrování
    }

}
