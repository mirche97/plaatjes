<?php

namespace Site\CommonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/hello/{name}")
     * @Template()
     */
    public function indexAction($name)
    {
        $param = array("_format"=>"json");
        $response = $this->get("api_service")->send("albummetas",$param);
        return $this->render('SiteCommonBundle:Default:index.html.twig', array('name'=>$name));
    }
}
