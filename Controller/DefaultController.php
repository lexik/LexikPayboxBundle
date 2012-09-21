<?php

namespace Lexik\Bundle\PayboxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('LexikPayboxBundle:Default:index.html.twig', array('name' => $name));
    }
}
