<?php

namespace App\Controller;


use App\Entity\OrderDetail;
use App\Entity\Orders;
use App\Form\OrdersType;
use App\Repository\Admin\ProductRepository;
use App\Repository\OrderDetailRepository;
use App\Repository\OrdersRepository;
use App\Repository\ShopcartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/orders")
 */
class OrdersController extends AbstractController
{
    /**
     * @Route("/", name="orders_index", methods="GET")
     */
    public function index(OrdersRepository $ordersRepository): Response
    {
        $user =$this->getUser();
        $userid = $user->getid();
        return $this->render('orders/frontbase.html.twig', ['orders' => $ordersRepository->findBy(['userid'=>$userid] )]);
    }

    /**
     * @Route("/new", name="orders_new", methods="GET|POST")
     */
    public function new(Request $request,ShopcartRepository $shopcartRepository): Response
    {
        $order = new Orders();
        $form = $this->createForm(OrdersType::class, $order);
        $form->handleRequest($request);
$user= $this->getUser();
$userid= $user->getid();
$total=$shopcartRepository->getUserShopcartTotal($userid);
$submittedToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('form-order',$submittedToken)) {
        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $order->setUserid($userid);
            $order->setAmount($total);
            $order->setStatus("New");
            $em->persist($order);
            $em->flush();
            $orderid=$order->getId();
            $shopcart=$shopcartRepository->getUserShopCart($userid);
            foreach($shopcart as $item){
            $orderdetail = new OrderDetail();
            $orderdetail->setOrderid($orderid);
                $orderdetail->setUserid($user->getid());
                $orderdetail->setProductid($item["productid"]);
                $orderdetail->setPrice($item["sellprice"]);
                $orderdetail->setQuantity($item["quantity"]);
                $orderdetail->setAmount($item["total"]);
                $orderdetail->setName($item["title"]);
                $orderdetail->setStatus("ordered");
                $em->persist($orderdetail);
                $em->flush();

            }
            $em = $this->getDoctrine()->getManager();
            $query = $em->createQuery('DELETE FROM App\Entity\shopcart s WHERE s.userid= :userid') ->setParameter('userid',$userid);
            $query->execute();
        $this->addFlash('success','Thank You, Your Order Has Been Recieved Successfully');
            dump($submittedToken);
            return $this->redirectToRoute('orders_index');
        }


        }

        return $this->render('orders/new.html.twig', [
            'order' => $order,
            'total' => $total,
            'form' => $form->createView(),
        ]);
    }








    /**
     * @Route("/{id}", name="orders_show", methods="GET|POST")
     */
    public function show(Orders $order,OrderDetailRepository $orderDetailRepository,ProductRepository $productRepository): Response
    {
        $user =$this->getUser();
        $userid =$user->getId();
        $orderid =$order->getId();




        $orderdetail=$orderDetailRepository->findBy(['orderid'=>$orderid]);
        return $this->render('orders/show.html.twig', ['order' => $order
        ,'orderdetail'=>$orderdetail,
          ]);
    }

    /**
     * @Route("/{id}/edit", name="orders_edit", methods="GET|POST")
     */
    public function edit(Request $request, Orders $order): Response
    {
        $form = $this->createForm(OrdersType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('orders_index', ['id' => $order->getId()]);
        }

        return $this->render('orders/edit.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="orders_delete", methods="DELETE")
     */
    public function delete(Request $request, Orders $order): Response
    {
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($order);
            $em->flush();
        }

        return $this->redirectToRoute('orders_index');
    }





}
