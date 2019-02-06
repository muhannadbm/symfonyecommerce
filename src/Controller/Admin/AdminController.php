<?php

namespace App\Controller\Admin;

use App\Entity\Orders;
use App\Repository\OrderDetailRepository;
use App\Repository\OrdersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{




    /**
     * @Route("/admin", name="admin")
     */
    public function index()
    {
        return $this->redirectToRoute('admin_user');
    }






    /**
     * @Route("/admin/order/update/{id}", name="admin_order_update", methods="POST")
     */
    public function order_update($id,Request $request,Orders $orders): Response
    {
        $shipinfo=$request->get("shipinfo");
        $note=$request->get("note");
        $status=$request->get("status");



        $em=$this->getDoctrine()->getManager();
        $sql="UPDATE orders SET shipinfo=:shipinfo,note=:note,status=:status WHERE id=:id";
        $statement =$em->getConnection()->prepare($sql);
        $statement ->bindValue('shipinfo', $request->request->get('shipinfo'));
        $statement ->bindValue('note', $request->request->get('note'));
        $statement ->bindValue('status', $request->request->get('status'));
        $statement ->bindValue('id', $id);
        $statement->execute();
        $this->addFlash('success', 'Your Order Has Been Updated Successfully');
        return $this->redirectToRoute('admin_order_show' ,array('id'=>$id));

    }



    /**
     * @Route("/admin/order/show/{id}", name="admin_order_show", methods="GET")
     */
    public function show($id,OrderDetailRepository $orderDetailRepository,Orders $orders,OrdersRepository $ordersRepository): Response
    {

        $orderdetail= $orderDetailRepository->findBy(['orderid'=>$id]);

        return $this->render('admin/order/show.html.twig',

            [
                'orderdetail'=>$orderdetail,
                'order' =>$orders,
            ]

        );

    }


    /**
     * @Route("/admin/order/{slug}", name="admin_order_index")
     */
    public function orders($slug,OrdersRepository $ordersRepository)
    {

        $orders= $ordersRepository->findBy(['status'=>$slug]);

        return $this->render('admin/order/frontbase.html.twig',

            [

                'orders' =>$orders,
            ]

        );

    }








}
