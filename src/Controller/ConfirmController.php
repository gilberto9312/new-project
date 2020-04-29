<?php

namespace App\Controller;

use App\Entity\Confirm;
use App\Form\ConfirmType;
use App\Repository\ConfirmRepository;
use App\Repository\ClientsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/confirm")
 */
class ConfirmController extends AbstractController
{

    private $confirmRepository;
    private $clientsRepository;
    public function __construct(ConfirmRepository $confirmRepository,ClientsRepository $clientsRepository)
    {
        $this->confirmRepository = $confirmRepository;
        $this->clientsRepository = $clientsRepository;
    }

    /**
     * @Route("/", name="confirm_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('confirm/index.html.twig', [
            'confirms' => $this->confirmRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="confirm_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $data = json_decode($request->getContent(),true);
        $monto = floatval($data['monto']);
        $client = $data['client'];
        $email = $this->clientsRepository->findOneBy(['id'=>$client]);
        
        $token = sha1(mt_rand(1, 90000) . 'SALT');

        try
        {
            $message = (new \Swift_Message('Confirmación de token'))
                        ->setSubject('Confirmación de token')
                        ->setFrom('example@example.com', 'EXAMPLE')
                        ->setTo($email->getEmail())
                        //->setCc($emailsCc)
                        ->setBody('ingrese el siguiente token '.$token);

            $this->get('mailer')->send($message);
        }
        catch(\Exception $e)
        {
            throw new \Exception('No se puede notificar, compruebe que los E-mails sean correctos. '.$e->getMessage());
        }  
        $this->confirmRepository->save($token, $monto,$client);      
        
        return new JsonResponse(['status' => 'Token created!'], Response::HTTP_CREATED);
    }

    /**
     * @Route("/{id}", name="confirm_show", methods={"GET"})
     */
    public function show(Confirm $confirm): Response
    {
        return $this->render('confirm/show.html.twig', [
            'confirm' => $confirm,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="confirm_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Confirm $confirm): Response
    {
        $form = $this->createForm(ConfirmType::class, $confirm);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('confirm_index');
        }

        return $this->render('confirm/edit.html.twig', [
            'confirm' => $confirm,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="confirm_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Confirm $confirm): Response
    {
        if ($this->isCsrfTokenValid('delete'.$confirm->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($confirm);
            $entityManager->flush();
        }

        return $this->redirectToRoute('confirm_index');
    }
}
