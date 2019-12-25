<?php

namespace App\Controller;

class Forget 
{
    public function run()
    {
        $view = new \App\View\Forget();
        $view->render();
    }
}