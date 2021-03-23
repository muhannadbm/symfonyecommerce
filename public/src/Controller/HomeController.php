<?php

namespace App\Controller;
use App\Entity\Admin\Messages;
use App\Entity\User;
use App\Entity\Admin\Product;
use App\Entity\Admin\Category;
use App\Form\Admin\MessagesType;
use App\Form\UserType;
use App\Form\Admin\ProductType;
use App\Repository\Admin\CategoryRepository;
use App\Repository\Admin\ImageRepository;
use App\Repository\Admin\ProductRepository;
use App\Repository\Admin\SettingRepository;
use App\Repository\ShopcartRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\CityRepository;
use \Doctrine\Common\Collections\Criteria;
use Mgilet\NotificationBundle\Annotation\Notifiable;
use Mgilet\NotificationBundle\NotifiableInterface;
use Mgilet\NotificationBundle\Manager\NotificationManager;




class HomeController extends AbstractController
{




    /**
     * @Route("/", name="home", methods="GET|POST")
     */
    public function index(NotificationManager $manager,PaginatorInterface $paginator, SettingRepository $settingRepository,Request $request, CategoryRepository $categoryRepository,ShopcartRepository $shopcartRepository)
    {



        
        $em=$this->getDoctrine()->getManager();

    $pagination = $paginator->paginate($em->createQuery('SELECT a FROM App\Entity\Admin\Product a ORDER BY a.id DESC'), $request->query->getInt('page', 1)/*page*/, 12/*limit*/);


        $user= $this->getUser();
        if($user) {
      
  


        $userid= $user->getid();
        $shopcount=$shopcartRepository->getUserShopCartCount($userid);
  
    } else { $shopcount=0; }
        $data=$settingRepository->findAll();

        $em=$this->getDoctrine()->getManager();
        $sql="SELECT * FROM product WHERE status='True' ORDER by ID DESC";
        $statement =$em->getConnection()->prepare($sql);

        $statement->execute();
        $slider=$statement->fetchAll();
        

        $sql="SELECT * FROM product  ORDER by ID DESC LIMIT 30";
        $statement =$em->getConnection()->prepare($sql);

        $statement->execute();
        $new=$statement->fetchAll();


        $cats = $this->categorytree($parent=0,$user_tree_array='', $request);
        
        
        $cats[0] = '';

       
        return $this->render('home/index.html.twig', [
            'user' => $user,
            'shopcount' => $shopcount,
            'data' => $data,
            'cats' =>$cats,
            'slider' =>$slider,
            'new' =>$pagination,
  
           
        ]);
    }

   
 /**
     * @Route("/user/products", name="product_index", methods="GET|POST")
     */
    public function userproduct(ProductRepository $productRepository, Request $request, ShopcartRepository $shopcartRepository): Response
    {
        $cats = $this->categorytree($parent=0,$user_tree_array='', $request);
        
        
        $cats[0] = '';
        $user= $this->getUser();
        if($user) {
      
            $userid= $user->getid();
            $shopcount=$shopcartRepository->getUserShopCartCount($userid);
      
        } else { $shopcount=0; }

        $id=$user->getId();
        $em = $this->getDoctrine()->getManager();
        $sql="SELECT * FROM product WHERE seller_id= :id ORDER by ID DESC";
        $statement =$em->getConnection()->prepare($sql);
        $statement ->bindValue('id', $id);
        $statement->execute();
        $new=$statement->fetchAll();

        return $this->render('home/userproduct/userproducts.html.twig', [
            'shopcount' => $shopcount,
            'cats' =>$cats,
            'products' => $new]);
    }


    /**
     * @Route("/user/products/newproduct", name="product_new", methods="GET|POST")
     */
    public function newProduct (Request $request,CategoryRepository $categoryrepository,ShopcartRepository $shopcartRepository): Response
    {
        $product = new Product();

        $cats = $this->categorytree($parent=0,$user_tree_array='', $request);
        
        
        $cats[0] = '';

        $user= $this->getUser();
        if($user) {
      
        $userid= $user->getid();
        $shopcount=$shopcartRepository->getUserShopCartCount($userid);
  
    } else { $shopcount=0; }


    $criteria = new Criteria();
$criteria->where(Criteria::expr()->neq('parentid', 0));

// Find all from the repository matching your criteria
$catlist = $categoryrepository->matching($criteria);
    
        
        
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file=$request->files->get('image');
           
            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
            try{
            $file->move(
              $this->getParameter('images_directory'),$fileName
              );}

            catch (FileException $e) {}
            $product->setImage($fileName);
            $cityname=$user->getCity();
            
            $product->setSeller($user);
            $product->setCity($cityname);

            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('product_index');
        }

        return $this->render('home/userproduct/newproduct.html.twig', [
            'product' => $product,
            'catlist' =>$catlist,
            'form' => $form->createView(),
            'cats' =>$cats,
            'shopcount' => $shopcount,
        ]);
    }

     /**
     * @Route("/user/product/delete/{id}", name="product_delete", methods="DELETE")
     */
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($product);
            $em->flush();
        }

        return $this->redirectToRoute('product_index');
    }





  

/**
    * @Route("/search", name="ajax_search", methods="GET|POST")
       */
    public function search (Request $request, ProductRepository $productRepository)
    {
        
        $em = $this->getDoctrine()->getManager();

        $requestString = $request->get('q');
  
        $entities =  $em->getRepository('App\Entity\Admin\Product')->findEntitiesByString($requestString);
  
        if(!$entities) {
            $result['entities']['error'] = "لم يتم العثور على نتائج";
        } else {
            $result['entities'] = $this->getRealEntities($entities);
        }
  
        return new Response(json_encode($result));
    }
  
    public function getRealEntities($entities){
  
        foreach ($entities as $entity){
            $realEntities[$entity->getId()] = $entity->getTitle();
        }
  
        return $realEntities;
    }


    /**
     * @Route("/allproducts", name="all_products", methods="GET|POST")
     */
    public function all(ProductRepository $productRepository)
    {

        $em=$this->getDoctrine()->getManager();
        $all=$productRepository->findAll();

        $sql="SELECT * FROM product WHERE status='new' ORDER by ID DESC LIMIT 4";
        $statement =$em->getConnection()->prepare($sql);

        $statement->execute();
        $new=$statement->fetchAll();
        $sql="SELECT * FROM product WHERE status='True' ORDER by ID DESC LIMIT 4";
        $statement =$em->getConnection()->prepare($sql);

        $statement->execute();
        $slider=$statement->fetchAll();


        $cats = $this->categorytree();
        $cats[0] = '';


        return $this->render('home/all.html.twig', [
            'all' => $all,
            'cats' =>$cats,
            'new' =>$new,
            'slider'=>$slider,

        ]);
    }









    /**
     * @Route("/product/{id}", name="product_detail", methods="GET|POST")
     */
    public function ProductDetail (CityRepository $cityRepository,ShopcartRepository $shopcartRepository,$id,ProductRepository $productRepository,Request $request)
    {

        $cats = $this->categorytree($parent=0,$user_tree_array='', $request);
        
        
        $cats[0] = '';

        $user= $this->getUser();
        if($user) {
      
  


        $userid= $user->getid();
        $shopcount=$shopcartRepository->getUserShopCartCount($userid);
  
    } else { $shopcount=0; }
        $data=$productRepository->findBy(['id'=>$id]);



        return $this->render('home/products.html.twig', [
            'data' => $data,
            'cats' =>$cats,
            'shopcount' => $shopcount,
      

        ]);

    }
    
    
    

    
    
      
    
    /**
     * @Route("/error", name="error", methods="GET|POST")
     */
    public function error ()
    {
         
            return $this->render('home/error.html.twig');
           
    
    }
    

    /**
     * @Route("/count", name="count_index", methods="GET")
     */

    public function front(ShopcartRepository $shopcartRepository)
    {

        $user= $this->getUser();
        $userid= $user->getid();
        $shopcount=$shopcartRepository->getUserShopCartCount($userid);



        return $this->render('home/frontbase.html.twig', [
            'shopcount' => $shopcount,
            ]);
    }




    /**
     * @Route("/products/{id}", name="product_details", methods="GET|POST", requirements={"id"="\d+"})
     */
    public function ProductDetails (ShopcartRepository $shopcartRepository,$id,ProductRepository $productRepository,ImageRepository $imageRepository,Request $request)
    {

        $cats = $this->categorytree($parent=0,$user_tree_array='', $request);
        
        
        $cats[0] = '';

        $user= $this->getUser();
        if($user) {
      
  


        $userid= $user->getid();
        $shopcount=$shopcartRepository->getUserShopCartCount($userid);
  
    } else { $shopcount=0; }
      


        $data=$productRepository->findBy(['id'=>$id]);
        $image=$imageRepository->findBy(['product_id'=>$id]);



        return $this->render('home/products_detail.html.twig', [
            'data' => $data,
            'image' => $image,
            'cats' =>$cats,
            'shopcount' => $shopcount,

        ]);

    }







    /**
     * @Route("/category/{catid}/{city}", name="category_products", methods="GET|POST", requirements={"catid"="\d+"})
     */
    public function CategoryProducts (int $city=-1, CityRepository $cityRepository,PaginatorInterface $paginator,$catid,ShopcartRepository $shopcartRepository,CategoryRepository $categoryRepository, Request $request)
    {
        $em=$this->getDoctrine()->getManager();
if($city==-1){
$pagination = $paginator->paginate($em->createQuery('SELECT a FROM App\Entity\Admin\Product a WHERE a.category_id= :catid')->setParameter('catid',$catid), $request->query->getInt('page', 1)/*page*/, 12/*limit*/);

}
else{
    $cityname=$cityRepository->findOneBy(['id'=>$city]);
$pagination = $paginator->paginate($em->createQuery('SELECT a FROM App\Entity\Admin\Product a WHERE a.category_id= :catid and a.city= :city ')->setParameter('catid',$catid)->setParameter('city',$cityname->getName()), $request->query->getInt('page', 1)/*page*/, 12/*limit*/);

        
    }
        $user= $this->getUser();

  
        if($user) {
      
        $userid= $user->getid();
        $shopcount=$shopcartRepository->getUserShopCartCount($userid);
  
    } else { $shopcount=0; }
        $cats=$this->categorytree($parent=0,$user_tree_array='', $request);
        $cats[0] = '';
        $data=$categoryRepository->findBy(['id'=>$catid]);
     
      

      


        $sql="SELECT * FROM product WHERE status=('True' OR'new' OR'link')   AND category_id= :catid";
        $statement =$em->getConnection()->prepare($sql);
        $statement ->bindValue('catid', $catid);
        $statement->execute();
        $products=$statement->fetchAll();

        $sql="SELECT * FROM product WHERE status='new' ORDER by ID ASC LIMIT 4";
        $statement =$em->getConnection()->prepare($sql);

        $statement->execute();
        $new=$statement->fetchAll();

        $sql="SELECT * FROM product WHERE status='True' ORDER by ID ASC LIMIT 4";
        $statement =$em->getConnection()->prepare($sql);

        $statement->execute();
        $slider=$statement->fetchAll();

       
        return $this->render('home/products.html.twig', [
             'shopcount' => $shopcount,
            'data' => $data,
            'new' =>$new,
            'slider' => $slider,
            'products' => $products,
            'cats' =>$cats,
            'new' =>$pagination,
            'cities' => $cityRepository->findAll(),
        ]);

}

public function categorytree($parent=0,$user_tree_array='',Request $request) {

    if(!is_array($user_tree_array))
        $user_tree_array=array();

    $em=$this->getDoctrine()->getManager();
    $sql="SELECT * FROM category WHERE parentid= ".$parent;
    $statement =$em->getConnection()->prepare($sql);
    $statement->execute();
    $result=$statement->fetchAll();
    $locale = $request->getLocale();
    if(count($result) > 0){
        $user_tree_array[]="<ul class='dropdown-menu multi'> 	<div class='row'>
        <div class='col-sm-3'>
            <ul class='multi-column-dropdown'>";
        foreach($result as $row) {

            if ($row["parentid"] == 0) {
                $user_tree_array[] = "<li class='dropdown '><a  class='dropdown-toggle  hyper' data-toggle='dropdown' ><span>" . $row['title'] . "<b class='caret'></b></span> </a>";
                $user_tree_array = $this->categorytree($row['id'], $user_tree_array, $request);


            }
            else {

                $user_tree_array[] = "<li><a  href='/category/" . $row['id'] . " '><i class='fa fa-angle-right' aria-hidden='true'></i>" . $row['title'] . " </a>";
                $user_tree_array = $this->categorytree($row['id'], $user_tree_array, $request);
            } }
            $user_tree_array[] = "</li></ul>  </div> </ul>";
        }
        return $user_tree_array;
    }







    /**
     * @Route("/{_locale}/aboutus", name="aboutus", methods="GET|POST")
     */
    public function aboutus(SettingRepository $settingRepository)
    {

        $data=$settingRepository->findAll();
        return $this->render('home/aboutus.html.twig', [
            'data' => $data,
        ]);
    }


    /**
     * @Route("/{_locale}/reference", name="reference", methods="GET|POST")
     */
    public function reference(SettingRepository $settingRepository)
    {

        $data=$settingRepository->findAll();
        return $this->render('home/reference.html.twig', [
            'data' => $data,
        ]);
    }
    
    
       /**
     * @Route("/{_locale}/certificates", name="certificate", methods="GET|POST")
     */
    public function certificate(SettingRepository $settingRepository)
    {

        $data=$settingRepository->findAll();
        return $this->render('home/certificates.html.twig', [
           
        ]);
    }
    
    
      
       /**
     * @Route("/{_locale}/executive", name="exec", methods="GET|POST")
     */
    public function exec(SettingRepository $settingRepository)
    {

        $data=$settingRepository->findAll();
        return $this->render('home/exec.html.twig', [
           
        ]);
    }







    /**
     * @Route("/newuser", name="new_user", methods="GET|POST")
     */
    public function new_user(Request $request, UserRepository $userRepository, CityRepository $cityRepository): Response
    {
        $user= $this->getUser();
        if($user) {
      
        $userid= $user->getid();
        $shopcount=$shopcartRepository->getUserShopCartCount($userid);
  
    } else { $shopcount=0; }
        
        $user= new User();

        $cats = $this->categorytree($parent=0,$user_tree_array='', $request);
  
     

        $cats[0] = '';

        $submittedToken=$request->request->get('token');

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() ) {
            if ($this->isCsrfTokenValid('user-form', $submittedToken))
            {  
                $user->setCreatedAt(new \DateTime());
                $user->setRoles("ROLE_USER");
                
                $emaildata=$userRepository->findBy(['email'=>$user->getEmail()] );
                if($emaildata==null) {
            
              
                    $em = $this->getDoctrine()->getManager();
                   
                    
                    $em->persist($user);
                    $em->flush();
                    $this->addFlash('success','تم إنشاء حساب مشتري بنجاح');
                    return $this->redirectToRoute('app_login'); 
        }
       

        else {  $this->addFlash('error',$user->getEmail()." مسجل مسبقا يرجى المحاولة باستخدام بريد أخر"); 
         } } 
    
    
    
    
    


    else if($this->isCsrfTokenValid('seller-form', $submittedToken)) {           
      
        $user->setCreatedAt(new \DateTime());
        $user->setRoles("ROLE_SELLER");
 
        
        $emaildata=$userRepository->findBy(['email'=>$user->getEmail()] );
        if($emaildata==null) {
    
      
            $em = $this->getDoctrine()->getManager();
           
            
            $em->persist($user);
            $em->flush();
            $this->addFlash('success','تم إنشاء حساب بائع بنجاح');
            return $this->redirectToRoute('app_login'); 
}


else {  $this->addFlash('error',$user->getEmail()." مسجل مسبقا يرجى المحاولة باستخدام بريد أخر"); 
}
    } }

 
        return $this->render('home/signup.html.twig', [
            'shopcount' => $shopcount,
            'user' => $user,'cats' =>$cats,
            'form'=>$form->createView(),
            'cities' => $cityRepository->findAll(),
        ]);
    }







    /**
     * @Route("/{_locale}/contact", name="contact", methods="GET|POST")
     */
    public function contact(SettingRepository $settingRepository, Request $request)
    {

        $message = new Messages();
        $form = $this->createForm(MessagesType::class, $message);
        $form->handleRequest($request);
        $submittedToken=$request->request->get('token');

        if ($form->isSubmitted() ) {
            if($this->isCsrfTokenValid('form-message',$submittedToken))
            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();
            $this->addFlash('success','Your Message Was Recieved');
            return $this->redirectToRoute('contact');
        }



        $data=$settingRepository->findAll();
        return $this->render('home/contact.html.twig', [
            'data' => $data,
            'form'=>$form->createView(),
            'message'=>$message,
        ]);
    }
 

    private function generateUniqueFileName()
    {
        return md5(uniqid());
    }



}