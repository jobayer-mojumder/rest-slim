<?php
require 'config.php';
require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
// $app->add('CorsMiddleware');

$app->post('/login', 'login');
$app->post('/signup', 'signup');
$app->post('/productAdd', 'productAdd');

$app->post('/product_price', 'product_by_price');
$app->post('/product_category', 'product_by_category');
$app->post('/seller_info', 'seller_info');

$app->get('/products', 'products');
$app->get('/newProducts', 'newProducts');
$app->get('/topProducts', 'topProducts');
$app->get('/category', 'category');

$app->get('/search(/:text)', function($text){
    try
    {
        $db = getDB();
        $productsData = '';

        $sql = "SELECT * FROM table_product WHERE name like :search or category like :search or description like :search";
        $stmt = $db->prepare($sql);
        //$stmt->bindParam("search", "%".$data->text."%", PDO::PARAM_STR);
        //$stmt->execute();
        $stmt->execute(array(':search' => '%'.$text.'%'));
        $mainCount = $stmt->rowCount();
        $productsData = $stmt->fetchAll(PDO::FETCH_OBJ);

        $db = null;
        if ($productsData)
        {
            $productData = json_encode($productsData);
            echo '{"products": ' . $productData . '}';
        }
        else
        {
            echo '{"error":{"text":"No product found!"}}';
        }
    }
    catch(PDOException $e)
    {
        echo '{"error":{"text":' . $e->getMessage() . '}}';
    }
});


$app->get('/my-products(/:user)', function($user){
    try
    {
        $db = getDB();
        $productsData = '';

        $sql = "SELECT * FROM table_product WHERE seller=:user";
        $stmt = $db->prepare($sql);
        //$stmt->bindParam("search", "%".$data->text."%", PDO::PARAM_STR);
        //$stmt->execute();
        $stmt->execute(array(':user' => $user));
        $mainCount = $stmt->rowCount();
        $productsData = $stmt->fetchAll(PDO::FETCH_OBJ);

        $db = null;
        if ($productsData)
        {
            $productData = json_encode($productsData);
            echo '{"products": ' . $productData . '}';
        }
        else
        {
            echo '{"error":{"text":"No product found!"}}';
        }
    }
    catch(PDOException $e)
    {
        echo '{"error":{"text":' . $e->getMessage() . '}}';
    }
});

$app->get('/product-details(/:id)', function ($id = 0) {
    if ($id==0 || $id=='') {
        echo '{"error":{"text":"Request needs a id."}}';
    } else {
        try{

            $db = getDB();
            $productData = '';
            $sql = "SELECT table_product.*, multi_images.image1, multi_images.image2, multi_images.image3  FROM table_product left join multi_images on table_product.id=multi_images.id WHERE table_product.id=:id limit 1";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $mainCount = $stmt->rowCount();
            $productData = $stmt->fetch(PDO::FETCH_OBJ);

            $db = null;
            if ($productData)
            {
                $productData = json_encode($productData);
                echo '{"product": ' . $productData . '}';
            }
            else
            {
                echo '{"error":{"text":"Warning! No product found"}}';
            }

        }
        catch(PDOException $e)
        {
            echo '{"error":{"text":' . $e->getMessage() . '}}';
        }
    }
    
});



$app->run();


function product_by_price(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    try{
        $max = $data->max;
        $min = $data->min;
        $average = $data->average;

        $db = getDB();
        $productsData = '';

        $sql = "SELECT * FROM table_product WHERE cost>=:min and cost<=:max limit 10";
        $stmt = $db->prepare($sql);
        $stmt->execute(array(':min' => $min, ':max' => $max));
        $mainCount = $stmt->rowCount();
        $productsData = $stmt->fetchAll(PDO::FETCH_OBJ);

        $db = null;
        if ($productsData)
        {
            $productData = json_encode($productsData);
            echo '{"products": ' . $productData . '}';
        }
        else
        {
            echo '{"error":{"text":"Warning! No product found"}}';
        }

    }catch(PDOException $e){

    }
}

function product_by_category(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    try{
        $max = $data->max;
        $min = $data->min;
        $category = $data->category;

        $db = getDB();
        $productsData = '';

        $sql = "SELECT * FROM table_product WHERE cost>=:min and cost<=:max and category=:category limit 10";
        $stmt = $db->prepare($sql);
        $stmt->execute(array(':min' => $min, ':max' => $max, 'category'=>$category));
        $mainCount = $stmt->rowCount();
        $productsData = $stmt->fetchAll(PDO::FETCH_OBJ);

        $db = null;
        if ($productsData)
        {
            $productData = json_encode($productsData);
            echo '{"products": ' . $productData . '}';
        }
        else
        {
            echo '{"error":{"text":"Warning! No product found"}}';
        }

    }catch(PDOException $e){

    }
}


/************************* USER LOGIN *************************************/
function login()
{

    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());

    try{

        $db = getDB();
        $userData = '';
        $sql = "SELECT * FROM user WHERE  email=:email and password=:password ";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("email", $data->email, PDO::PARAM_STR);
        $password = md5($data->password);
        $stmt->bindParam("password", $password, PDO::PARAM_STR);
        $stmt->execute();
        $mainCount = $stmt->rowCount();
        $userData = $stmt->fetch(PDO::FETCH_OBJ);

        if (!empty($userData))
        {
            $user_id = $userData->user_id;
            $userData->token = apiToken($user_id);
        }

        $db = null;
        if ($userData)
        {
            $userData = json_encode($userData);
            echo '{"userData": ' . $userData . '}';
        }
        else
        {
            echo '{"text":"Warning! wrong email or password"}';
        }

    }
    catch(PDOException $e)
    {
        echo '"{"text":' . $e->getMessage() . '}';
    }
}

/* ### User registration ### */
function signup()
{
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());

    
    $firstname = $data->firstname;
    $lastname = $data->lastname;
    $email = $data->email;
    $password = $data->password;
    $phone = $data->phone;
    $city = $data->city;
    $state = $data->state;
    $zip = $data->zip;
    $country = $data->country;
    $verification_code = "123212";
    if ($data->whoami == 1) {
        $is_buyer = 'Y';
        $is_seller = 'N';
    }else{
        $is_buyer = 'N';
        $is_seller = 'Y';
    }

    try{

        if ( strlen($email)>0){
            $db = getDB();
            $userData = '';
            $sql = "SELECT user_id FROM user WHERE email=:email";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("email", $email, PDO::PARAM_STR);
            $stmt->execute();
            $mainCount = $stmt->rowCount();
            $created = time();

            if ($mainCount == 0){

                /*Inserting user values*/
                $sql1 = "INSERT INTO user(firstname,lastname,email,password,phone,city,state,zip,country,is_buyer,is_seller, initial_email, verification_code)VALUES(:firstname,:lastname,:email,:password,:phone,:city,:state,:zip,:country,:is_buyer,:is_seller, :initial_email, :verification_code)";
                $stmt1 = $db->prepare($sql1);
                $stmt1->bindParam("firstname", $firstname, PDO::PARAM_STR);
                $stmt1->bindParam("lastname", $lastname, PDO::PARAM_STR);
                $stmt1->bindParam("email", $email, PDO::PARAM_STR);
                $password = md5($password);
                $stmt1->bindParam("password", $password, PDO::PARAM_STR);
                $stmt1->bindParam("phone", $phone, PDO::PARAM_STR);
                $stmt1->bindParam("city", $city, PDO::PARAM_STR);
                $stmt1->bindParam("state", $state, PDO::PARAM_STR);
                $stmt1->bindParam("zip", $zip, PDO::PARAM_STR);
                $stmt1->bindParam("country", $country, PDO::PARAM_STR);
                $stmt1->bindParam("is_buyer", $is_buyer, PDO::PARAM_STR);
                $stmt1->bindParam("is_seller", $is_seller, PDO::PARAM_STR);
                $stmt1->bindParam("initial_email", $email, PDO::PARAM_STR);
                $stmt1->bindParam("verification_code", $verification_code, PDO::PARAM_STR);
                $stmt1->execute();
                $userData = internalUserDetails($email);

                $db = null;

                if ($userData) {
                    $userData = json_encode($userData);
                    echo '{"userData": ' . $userData . '}';
                } else {
                    echo '{"text":"Enter valid data"}';
                }

            }else{
                echo '{"text":"This email already exists!"}';
                //exit();
            }
        }
        else
        {
            echo '{"text":"Enter valid data!"}';
        }
    }
    catch(PDOException $e)
    {
        echo '{"text":' . $e->getMessage() . '}';
    }
}

function products()
{
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());

    try
    {
        $db = getDB();
        $productsData = '';

        $sql = "SELECT * FROM table_product";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $mainCount = $stmt->rowCount();
        $productsData = $stmt->fetchAll(PDO::FETCH_OBJ);

        $db = null;
        if ($productsData)
        {
            $productsData = json_encode($productsData);
            echo '{"products": ' . $productsData . '}';
        }
        else
        {
            echo '{"error":{"text":"Warning! No product found."}}';
        }
    }
    catch(PDOException $e)
    {
        echo '{"error":{"text":' . $e->getMessage() . '}}';
    }
}


function newProducts()
{
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());

    try
    {
        $db = getDB();
        $productsData = '';

        $sql = "SELECT * FROM table_product order by id desc limit 6";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $mainCount = $stmt->rowCount();
        $productsData = $stmt->fetchAll(PDO::FETCH_OBJ);

        $db = null;
        if ($productsData)
        {
            $productsData = json_encode($productsData);
            echo '{"products": ' . $productsData . '}';
        }
        else
        {
            echo '{"error":{"text":"Warning! No product found."}}';
        }
    }
    catch(PDOException $e)
    {
        echo '{"error":{"text":' . $e->getMessage() . '}}';
    }
}

function topProducts()
{
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());

    try
    {
        $db = getDB();
        $productsData = '';

        $sql = "SELECT * FROM table_product order by rating desc limit 6";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $mainCount = $stmt->rowCount();
        $productsData = $stmt->fetchAll(PDO::FETCH_OBJ);

        $db = null;
        if ($productsData)
        {
            $productsData = json_encode($productsData);
            echo '{"products": ' . $productsData . '}';
        }
        else
        {
            echo '{"error":{"text":"Warning! No product found."}}';
        }
    }
    catch(PDOException $e)
    {
        echo '{"error":{"text":' . $e->getMessage() . '}}';
    }
}


/* ### internal Username Details ### */
function internalUserDetails($input)
{

    try
    {
        $db = getDB();
        $sql = "SELECT * FROM user WHERE email=:input";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("input", $input, PDO::PARAM_STR);
        $stmt->execute();
        $usernameDetails = $stmt->fetch(PDO::FETCH_OBJ);
        $usernameDetails->token = apiToken($usernameDetails->user_id);
        $db = null;
        return $usernameDetails;

    }
    catch(PDOException $e)
    {
        echo '{"error":{"text":' . $e->getMessage() . '}}';
    }

}


function category()
{
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());

    try
    {
        $db = getDB();
        $catgoryData = '';

        $sql = "SELECT * FROM product_category";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $mainCount = $stmt->rowCount();
        $catgoryData = $stmt->fetchAll(PDO::FETCH_OBJ);

        $db = null;
        if ($catgoryData)
        {
            $catgoryData = json_encode($catgoryData);
            echo '{"category": ' . $catgoryData . '}';
        }
        else
        {
            echo '{"error":{"text":"Warning! No product found."}}';
        }
    }
    catch(PDOException $e)
    {
        echo '{"error":{"text":' . $e->getMessage() . '}}';
    }
}

function productAdd(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    
    $name = $data->name;
    $cost = $data->price;
    $category_id = $data->category;
    $stock = $data->stock;
    $description = $data->description;

    $user_id = $data->user_id;
    $token = $data->token;

    $thumbnail = $data->thumb;
    $image1 = $data->image1;
    $image2 = $data->image2;
    $image3 = $data->image3;
    $image4 = $data->image4;

    if (!$user_id) {
        echo '{"error":{"text":"Please login first!"}}';
        exit();
    }

    try {

        $category = "";
        $sku = "";
        $rating = 0;
        $quantity_sold = 0;
        $status = "unapproved";

        $db = getDB();

        $catgoryData = '';

        $sql = "SELECT * FROM product_category where category_id = :category";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("category", $category_id, PDO::PARAM_INT);
        $stmt->execute();
        $mainCount = $stmt->rowCount();
        $catgoryData = $stmt->fetch(PDO::FETCH_OBJ);

        $sku = $catgoryData->sku;
        $category = $catgoryData->category_name;


        $sql1 = "INSERT INTO table_product (sku, name, cost, category, category_id, image, thumbnail, description, stock, seller,rating,quantity_sold,status) VALUES 
        (:sku, :name, :cost, :category, :category_id, :image, :thumbnail, :description, :stock, :seller, :rating, :quantity_sold, :status)";
        $stmt1 = $db->prepare($sql1);
        $stmt1->bindParam("sku", $sku, PDO::PARAM_STR);
        $stmt1->bindParam("name", $name, PDO::PARAM_STR);
        $stmt1->bindParam("cost", $cost, PDO::PARAM_INT);
        $stmt1->bindParam("category", $category, PDO::PARAM_STR);
        $stmt1->bindParam("category_id", $category_id, PDO::PARAM_INT);
        $stmt1->bindParam("image", $image1, PDO::PARAM_STR);
        $stmt1->bindParam("thumbnail", $thumbnail, PDO::PARAM_STR);
        $stmt1->bindParam("description", $description, PDO::PARAM_STR);
        $stmt1->bindParam("stock", $stock, PDO::PARAM_INT);
        $stmt1->bindParam("seller", $user_id, PDO::PARAM_INT);
        $stmt1->bindParam("rating", $rating, PDO::PARAM_INT);
        $stmt1->bindParam("quantity_sold", $quantity_sold, PDO::PARAM_INT);
        $stmt1->bindParam("status", $status, PDO::PARAM_STR);
        
        if ($stmt1->execute()){
            $lastInsertId = $db->lastInsertId();

            $sql2 = "INSERT INTO multi_images (id, image1, image2, image3) VALUES 
            (:id, :image1, :image2, :image3)";
            $stmt2 = $db->prepare($sql2);
            $stmt2->bindParam("id", $lastInsertId, PDO::PARAM_INT);
            $stmt2->bindParam("image1", $image2, PDO::PARAM_STR);
            $stmt2->bindParam("image2", $image3, PDO::PARAM_STR);
            $stmt2->bindParam("image3", $image4, PDO::PARAM_STR);            

            if ($stmt2->execute()){
                $db = null;
                $data = json_encode($data);
                echo '{"product": ' . $data . '}';
            }else{
                $db = null;
                echo '{"error":{"text":"Warning! Product add failed!"}}';
            }

        }else{
            $db = null;
            echo '{"error":{"text":"Warning! Product add failed!"}}';
        }

    } catch (PDOException $e) {
     echo '{"error":{"text":' . $e->getMessage() . '}}';
 }

}


function seller_info(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    
    $seller = $data->seller;
    try {
        $db = getDB();

        $userData = '';

        $sql = "SELECT * FROM user where user_id = :seller";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("seller", $seller, PDO::PARAM_STR);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_OBJ);
        $db = null;
        if ($userData){
            $userData = json_encode($userData);
            echo '{"seller": ' . $userData . '}';
        } else {
            echo '{"text":"Warning! No seller found!"}';
        }

    } catch (PDOException $e) {
     echo '{"error":{"text":' . $e->getMessage() . '}}';
 }

}

?>
