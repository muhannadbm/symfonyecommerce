<?php

namespace App\Controller;


use App\Entity\Orderdetail;
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
use App\Repository\Admin\CategoryRepository;
use App\Entity\Admin\Category;
use App\Repository\UserRepository;
use Mgilet\NotificationBundle\Annotation\Notifiable;
use Mgilet\NotificationBundle\NotifiableInterface;
use Mgilet\NotificationBundle\Manager\NotificationManager;

/**
 * @Route("/orders")
 */
class OrdersController extends AbstractController
{
    /**
     * @Route("/", name="orders_index", methods="GET")
     */
    public function index(NotificationManager $manager, OrdersRepository $ordersRepository, CategoryRepository $categoryRepository, Request $request, ShopcartRepository $shopcartRepository): Response
    {

        $user = $this->getUser();
        $userid = $user->getid();

        $em = $this->getDoctrine()->getManager();
        // $sql="SELECT * FROM notifiable_entity WHERE identifier= ".$userid;
        $sql = "SELECT * FROM notifiable_entity RIGHT JOIN notifiable_notification 
        ON notifiable_entity.id = notifiable_notification.notifiable_entity_id WHERE notifiable_entity.identifier= " . $userid;

        $statement = $em->getConnection()->prepare($sql);
        $statement->execute();
        $result = $statement->fetchAll();

        $sql = "UPDATE notifiable_notification LEFT JOIN notifiable_entity 
        ON notifiable_entity.id = notifiable_notification.notifiable_entity_id SET notifiable_notification.seen=1  WHERE notifiable_entity.identifier= " . $userid;

        $statement = $em->getConnection()->prepare($sql);
        $statement->execute();




        if ($user) {

            $shopcount = $shopcartRepository->getUserShopCartCount($userid);
        } else {
            $shopcount = 0;
        }
        $cats = $this->categorytree($parent = 0, $user_tree_array = '', $request);


        // foreach($sellers as $i =>$result){
        //     $sellers= explode(" ",$sellersstring);
        //     }
        if (!in_array("ROLE_SELLER", $user->getRoles())) {
            $orders = $ordersRepository->findBy(['userid' => $userid]);
        } else {

            $em = $this->getDoctrine()->getManager();
            // WHERE sellerids LIKE CONCAT('%',:sellerids,'%')"
            $sql = "SELECT * FROM orders";
            $statement = $em->getConnection()->prepare($sql);
            $statement->bindValue('sellerids', $userid);
            $statement->execute();
            $orders = $statement->fetchAll();
            $myorder = NULL;
            foreach ($orders as $i => $order) {

                $myarray = explode(" ", $order['sellerids']);
                if (in_array($userid, $myarray)) {
                    $myorder[] = $order;
                    $orders = $myorder;
                }
            }
            if ($myorder == NULL) {
                $orders = NULL;
            }
        }


        $cats[0] = '';

        return $this->render('orders/index.html.twig', [
            'shopcount' => $shopcount,
            'orders' => $orders,
            'cats' => $cats,
        ]);
    }






    /**
     * @Route("/new", name="orders_new", methods="GET|POST")
     */
    public function new(ProductRepository $productRepository, NotificationManager $manager, Request $request, ShopcartRepository $shopcartRepository, UserRepository $userRepository): Response
    {

        $cats = $this->categorytree($parent = 0, $user_tree_array = '', $request);


        $cats[0] = '';
        $user = $this->getUser();
        if ($user) {

            $userid = $user->getid();
            $shopcount = $shopcartRepository->getUserShopCartCount($userid);
        } else {
            $shopcount = 0;
        }
        $order = new Orders();
        $form = $this->createForm(OrdersType::class, $order);
        $form->handleRequest($request);
        $user = $this->getUser();
        $userid = $user->getid();

        $total = $shopcartRepository->getUserShopcartTotal($userid);
        $sellers = $shopcartRepository->getUserShopcartSellers($userid);
        $sellersstring = "";
        foreach ($sellers as $i => $result) {
            $sellerelement = implode($sellers[$i]);
            $sellersstring = $sellersstring . " " . $sellerelement;
        }



        $submittedToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('form-order', $submittedToken)) {
            if ($form->isSubmitted()) {
                $em = $this->getDoctrine()->getManager();
                $order->setUserid($userid);
                $order->setAmount($total);
                $order->setSellerids($sellersstring);
                $order->setStatus("جديد");
                $order->setCreated_At(new \DateTime());
                $em->persist($order);
                $em->flush();
                $orderid = $order->getId();
                $shopcart = $shopcartRepository->getUserShopCart($userid);

                $sellerarray = array();
                foreach ($shopcart as $item) {
                    $orderdetail = new OrderDetail();
                    $orderdetail->setOrderid($orderid);
                    $orderdetail->setUserid($user->getid());
                    $orderdetail->setProductid($item["productid"]);
                    $seller = $userRepository->findOneBy(['id' => $item["seller"]]);
                    $myproduct = $productRepository->findOneBy(['id' => $item["productid"]]);
                    $myamount = $myproduct->getAmount();
                    $result = $myamount - $item["quantity"];
                    $myproduct->setAmount($result);
                    $orderdetail->setSeller($seller);
                    $orderdetail->setPrice($item["sellprice"]);
                    $orderdetail->setQuantity($item["quantity"]);
                    $orderdetail->setAmount($item["total"]);
                    $orderdetail->setName($item["title"]);
                    $orderdetail->setStatus("تم طلبه");
                    $em->persist($orderdetail);
                    $em->flush();

                    if (!in_array($seller, $sellerarray)) {
                        $sellerarray[] = $seller;
                    }
                }



                foreach ($sellerarray as $item) {
                    $notif = $manager->createNotification('مبروك');
                    $notif->setMessage('تم استلام طلب جديد');
                    $notif->setLink($orderid);

                    $manager->addNotification(array($item), $notif, true);
                }

                $notif = $manager->createNotification('مبروك');
                $notif->setMessage('تم تقديم طلبك');
                $notif->setLink($orderid);

                $manager->addNotification(array($user), $notif, true);

                $em = $this->getDoctrine()->getManager();
                $query = $em->createQuery('DELETE FROM App\Entity\shopcart s WHERE s.userid= :userid')->setParameter('userid', $userid);
                $query->execute();
                $this->addFlash('success', 'تم استلام طلبك بنجاح');

                return $this->redirectToRoute('home');
            }
        }

        return $this->render('orders/new.html.twig', [
            'shopcount' => $shopcount,
            'cats' => $cats,
            'order' => $order,
            'total' => $total,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/{notid}", name="orders_show", methods="GET|POST", requirements={"page"="\d+"})
     */
    public function show(Request $request,$notid=0, ShopcartRepository $shopcartRepository, Orders $order, OrderDetailRepository $orderDetailRepository, ProductRepository $productRepository): Response
    {
        $user = $this->getUser();
        if ($user) {

            $userid = $user->getid();
            $shopcount = $shopcartRepository->getUserShopCartCount($userid);
        } else {
            $shopcount = 0;
        }
        $em = $this->getDoctrine()->getManager();
        $sql = "UPDATE notifiable_notification SET notifiable_notification.seen=1  WHERE notifiable_notification.id= " . $notid;

        $statement = $em->getConnection()->prepare($sql);
        $statement->execute();



        $orderid = $order->getId();
        $cats = $this->categorytree($parent = 0, $user_tree_array = '', $request);
        if (!in_array("ROLE_SELLER", $user->getRoles())) {
            $orderdetail = $orderDetailRepository->findBy(['orderid' => $orderid]);
        } else {
            $orderdetail = $orderDetailRepository->findBy(['orderid' => $orderid, 'seller' => $userid]);
        }

        return $this->render('orders/show.html.twig', [
            'shopcount' => $shopcount,
            'order' => $order, 'orderdetail' => $orderdetail, 'cats' => $cats,
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
     * @Route("/{id}/{seller}/{user}/{product}", name="orders_delete", methods="DELETE")
     */
    public function delete(UserRepository $userRepository,ProductRepository $productRepository,NotificationManager $manager, OrderDetailRepository $orderDetailRepository, $id, $seller, $user, $product, Request $request, Orders $order): Response
    {
        
        $orderdetail = $orderDetailRepository->findOneBy(['orderid' => $id, 'seller' => $seller, 'userid' => $user, 'productid' => $product]);
        $sellers = $orderdetail->getSeller();

        if ($this->isCsrfTokenValid('delete' . $order->getId(), $request->request->get('_token'))) {
            // $em = $this->getDoctrine()->getManager();
            // $em->remove($order);
            // $em->flush();
            $em = $this->getDoctrine()->getManager();

            $orderdetail = $orderDetailRepository->findOneBy(['orderid' => $id, 'seller' => $seller, 'userid' => $user, 'productid' => $product]);

            $myproduct = $productRepository->findOneBy(['id' => $product]);

            $orderquant = $orderdetail->getQuantity();

            $myamount = $myproduct->getAmount();
            $result = $myamount + $orderquant;
            $myproduct->setAmount($result);


            $orderdetail->setStatus('تم الإلغاء');
            $em->persist($orderdetail);
            $em->flush();
            $myuser = $orderdetail->getSeller();
            $notif = $manager->createNotification('للأسف');
            $notif->setMessage('تم إلغاء أحد طلباتك');
            $notif->setLink($id);

            $manager->addNotification(array($myuser), $notif, true);
        } elseif ($this->isCsrfTokenValid('sellerdelete' . $order->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();

            $orderdetail = $orderDetailRepository->findOneBy(['orderid' => $id, 'seller' => $seller, 'userid' => $user, 'productid' => $product]);
            $orderdetail->setStatus('تم الإلغاء');
            $em->persist($orderdetail);
            $em->flush();
            $myuserid = $orderdetail->getUserid();
            $myuser=$userRepository->findOneBy(['id' => $myuserid]);
            $notif = $manager->createNotification('للأسف');
            $notif->setMessage('تم إلغاء أحد طلباتك');
            $notif->setLink($id);
            $manager->addNotification(array($myuser), $notif, true);
        }



        return $this->redirectToRoute('orders_show', ['id' => $id]);
    }



 /**
     * @Route("confirm/{id}/{seller}/{user}/{product}", name="orders_confirm", methods="POST")
     */
    public function confirm(UserRepository $userRepository,ProductRepository $productRepository,NotificationManager $manager, OrderDetailRepository $orderDetailRepository, $id, $seller, $user, $product, Request $request, Orders $order): Response
    {
        
        $orderdetail = $orderDetailRepository->findOneBy(['orderid' => $id, 'seller' => $seller, 'userid' => $user, 'productid' => $product]);
        $sellers = $orderdetail->getSeller();

        if ($this->isCsrfTokenValid('sellerconfirm' . $order->getId(), $request->request->get('_token'))) {

            $em = $this->getDoctrine()->getManager();

            $orderdetail = $orderDetailRepository->findOneBy(['orderid' => $id, 'seller' => $seller, 'userid' => $user, 'productid' => $product]);

            $orderdetail->setStatus('تم التأكيد');
            $em->persist($orderdetail);
            $em->flush();
            $myuserid = $orderdetail->getUserid();
            $myuser=$userRepository->findOneBy(['id' => $myuserid]);
            $notif = $manager->createNotification('مبروك');
            $notif->setMessage('تم تأكيد طلبك');
            $notif->setLink($id);
            $manager->addNotification(array($myuser), $notif, true);
        }



        return $this->redirectToRoute('orders_show', ['id' => $id]);
    }




    public function categorytree($parent = 0, $user_tree_array = '', Request $request)
    {

        if (!is_array($user_tree_array))
            $user_tree_array = array();

        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM category WHERE parentid= " . $parent;
        $statement = $em->getConnection()->prepare($sql);
        $statement->execute();
        $result = $statement->fetchAll();
        $locale = $request->getLocale();
        if (count($result) > 0) {
            $user_tree_array[] = "<ul class='dropdown-menu multi'> 	<div class='row'>
            <div class='col-sm-3'>
                <ul class='multi-column-dropdown'>";
            foreach ($result as $row) {

                if ($row["parentid"] == 0) {
                    $user_tree_array[] = "<li class='dropdown '><a  class='dropdown-toggle  hyper' data-toggle='dropdown' ><span>" . $row['title'] . "<b class='caret'></b></span> </a>";
                    $user_tree_array = $this->categorytree($row['id'], $user_tree_array, $request);
                } else {

                    $user_tree_array[] = "<li><a  href='/category/" . $row['id'] . " '><i class='fa fa-angle-right' aria-hidden='true'></i>" . $row['title'] . " </a>";
                    $user_tree_array = $this->categorytree($row['id'], $user_tree_array, $request);
                }
            }
            $user_tree_array[] = "</li></ul>  </div> </ul>";
        }
        return $user_tree_array;
    }
}
