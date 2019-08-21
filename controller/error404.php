<?php

Class error404Controller Extends baseController {

public function index() 
{
        $this->registry->template->blog_heading = 'Erro 404';
        $this->registry->template->show('error404');
}


}
?>
