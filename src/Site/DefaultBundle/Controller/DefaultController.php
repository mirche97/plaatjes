<?php

namespace Site\DefaultBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        $param = array("_format"=>"json");
        $response = $this->get("api_service")->send("albummetas",$param);

        $albums  = json_decode($response->getContent());

        return $this->render("SiteDefaultBundle:Default:index.html.twig",array("albums"=>$albums));
    }
}
