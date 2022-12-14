<?php

namespace App\Controller;

use App\Entity\MyMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index(MessageBusInterface $bus): Response
    {


        $message = new MyMessage();
        $message->setSender("Samson");
        $message->setReceiver("Tope");
        $message->setContent("Ajakaye");
        $bus->dispatch($message);
        return new Response("Message sent");
    }
}