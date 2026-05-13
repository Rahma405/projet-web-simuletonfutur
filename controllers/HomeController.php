<?php

class HomeController extends Controller {

    // GET /  →  Landing page (choose Front or Back office)
    public function landing(): void {
        $this->view('landing');
    }
}
