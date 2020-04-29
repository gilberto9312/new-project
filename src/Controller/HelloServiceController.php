<?php
namespace App\Controller;

use App\Service\HelloService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HelloServiceController extends AbstractController
{
    private $helloService;
    public function __construct(HelloService $helloService){
        $this->helloService = $helloService;
    }
    /**
     * @Route("/soap")
     */

    public function index()
    {
        $soapServer = new \SoapServer("test.wsdl");
        $soapServer->setObject($this->helloService,array('name'=>'xsd:string'));

        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml; charset=ISO-8859-1');

        ob_start();
        $soapServer->handle();
        $response->setContent(ob_get_clean());
        
        return $response;
    }
}
