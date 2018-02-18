<?php

namespace SophrologieBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('SophrologieBundle:Default:index.html.twig');
    }
}
