<?php

namespace App\Controller;

use App\Entity\Clients;
use App\Form\ClientsType;
use App\Repository\ClientsRepository;
use App\Repository\WalletRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/clients")
 */
class ClientsController extends AbstractController
{

    private $clientsRepository;
    private $walletRepository;
    public function __construct(ClientsRepository $clientsRepository,WalletRepository $walletRepository)
    {
        $this->clientsRepository = $clientsRepository;
        $this->walletRepository = $walletRepository;
    }

    /**
     * @Route("/", name="clients_index", methods={"GET"})
     */
    public function index(): Response
    {
        $listClietns = $this->clientsRepository->findAll();

        return $this->json($listClietns);
    }

    /**
     * @Route("/new", name="clients_new", methods={"POST"})
     */
    public function new(Request $request): Response
    {        
        $data = json_decode($request->getContent(),true);
        
        $document = $data['document'];
        $name     = $data['name'];
        $email    = $data['email'];
        $phone    = $data['phone'];

        if (empty($document) || empty($name) || empty($email)|| empty($phone)) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }
            
        $this->clientsRepository->save($document, $name ,$email, $phone);      
        
        return new JsonResponse(['status' => 'Client created!'], Response::HTTP_CREATED);
    }

    /**
     * @Route("/view", name="clients_show", methods={"POST"})
     */
    public function view(Request $request): Response
    {
        $data = json_decode($request->getContent(),true);
        $document = $data['document'];
        $phone = $data['phone'];
        if(empty($document) || empty($phone)){
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }
        $client = $this->clientsRepository->findOneBy(['document' => $document,'phone'=>$phone ]);
        $wallet = $this->walletRepository->findOneBy(['client' => $client->getId() ]);
        return $this->json($wallet->getTotal());
    }

    /**
     * @Route("/{id}/edit", name="clients_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Clients $client): Response
    {
        $form = $this->createForm(ClientsType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('clients_index');
        }

        return $this->render('clients/edit.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="clients_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Clients $client): Response
    {
        if ($this->isCsrfTokenValid('delete'.$client->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($client);
            $entityManager->flush();
        }

        return $this->redirectToRoute('clients_index');
    }
}
