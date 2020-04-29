<?php

namespace App\Controller;

use App\Entity\Wallet;
use App\Form\WalletType;
use App\Repository\WalletRepository;
use App\Repository\ConfirmRepository;
use App\Repository\ClientsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/wallet")
 */
class WalletController extends AbstractController
{
    private $walletRepository;
    private $clientsRepository;

    public function __construct(WalletRepository $walletRepository,ClientsRepository $clientsRepository,ConfirmRepository $confirmRepository)
    {
        $this->walletRepository = $walletRepository;
        $this->clientsRepository = $clientsRepository;
        $this->confirmRepository = $confirmRepository;
    }
    /**
     * @Route("/", name="wallet_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('wallet/index.html.twig', [
            'wallets' => $walletRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="wallet_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $wallet = new Wallet();
        $form = $this->createForm(WalletType::class, $wallet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($wallet);
            $entityManager->flush();

            return $this->redirectToRoute('wallet_index');
        }

        return $this->render('wallet/new.html.twig', [
            'wallet' => $wallet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="wallet_show", methods={"GET"})
     */
    public function show(Wallet $wallet): Response
    {
        return $this->render('wallet/show.html.twig', [
            'wallet' => $wallet,
        ]);
    }

    /**
     * @Route("/{id}/{email}/edit", name="wallet_edit", methods={"PUT"})
     */
    public function edit($id,$email,Request $request): Response
    {
        $client = $this->clientsRepository->findOneBy(['document' => $id,'email'=>$email]);
        $wallet = $this->walletRepository->findOneBy(['client' => $client->getId()]);
        $data = json_decode($request->getContent(), true);
        $sum = $data['total'];
        if(empty($sum)){
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }
        $total = floatval($sum) + floatval($wallet->getTotal());
        $wallet->setTotal($total);
        $updatedwallet = $this->walletRepository->update($wallet);
        return new JsonResponse(['status' => 'Wallet updated!'], Response::HTTP_OK);
    }

    /**
     * @Route("/{id}/sub", name="wallet_sub", methods={"PUT"})
     */
    public function sub($id, Request $request): Response
    {
        if($id == null){
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }
        $token = $this->confirmRepository->findOneBy(['token' => $id]);

        if($token == null){
            throw new NotFoundHttpException('Not Found token!');
        }
        $wallet = $this->walletRepository->findOneBy(['client' => $token->getClient()]);
        $saldo = $wallet->getTotal();
        $client =  $token->getClient();
        $monto = $token->getMonto();

        $sub = floatval($saldo) - floatval($monto);

        $wallet->setTotal($sub);
        $updatedwallet = $this->walletRepository->update($wallet);

        return new JsonResponse(['status' => 'Wallet updated!'], Response::HTTP_OK);



        
    }
}
