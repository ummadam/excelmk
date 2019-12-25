<?php

namespace App\Controller;

class Parse
{
    public function run($data)
    {         
        $content = file_get_contents('uploads/technograd/photos.json');
        $content = json_decode($content);
        $url = [];
        $photos = [];
        foreach($content as $con){
            $con = json_decode($con);
            if(isset($con->pictures)){
                foreach($con->pictures as $p){
                    $url[] =  $p[8]->url;
                    }
                    
                }
                $photos[] = ["id" => $con->id,
                                "url" => $url];
                $url = [];
            
        }
        file_put_contents('uploads/technograd/photosUpdated.json', json_encode($photos)); 
        die();

        $productsFromPrice = $this->getProducts($data); 
        $this->insertProducts($productsFromPrice);
        $this->getSpecifications();
        $this->getPhotos();         
        $this->getNotProcessed();
        $this->getJson();   
       // $this->getSpecsUpdated();
        $this->getPhotosUpdated();    
         
        $pdo = \App\Service\DB::get();
        $stmt1 = $pdo->prepare("
            SELECT
                *
            FROM
                `productss`
            WHERE
                `articul` = 0
        ");
        $stmt1->execute();
        $products = $stmt1->fetchAll(); 
        
        while(count($products)>0){
            $this->insertProductIds($products);
            $stmt1->execute();
            $products = $stmt1->fetchAll();

        }
        
    }

    public function getSpecsUpdated()
    {
        $content = file_get_contents('uploads/technograd/specs.json');
        $content = json_decode($content);
        $specs = [];
        $specs_array = [];
        foreach($content as $con){
            $con = json_decode($con);
            if(isset($con->specifications)){
                $a = $con->specifications;
                foreach($a as $array){
                    //$spec = json_decode($array->subspecs);
                    foreach($array->subspecs as $spec){
                        $specs_array[] = ["name" => $spec->name,
                                          "value" => $spec->value];
                                          
                    }
                                        
                }
                $specs[] = ["id" => $con->id,
                "specs" => $specs_array];

            }           
        }       
        file_put_contents('uploads/technograd/specsUpdated.json', json_encode($specs));

    }

    public function getPhotosUpdated()
    {
        $content = file_get_contents('uploads/technograd/photos.json');
        $content = json_decode($content);
        $url = [];
        $photos = [];
        foreach($content as $con){
            $con = json_decode($con);
            if(isset($con->pictures)){
                foreach($con->pictures as $p){
                    $url[] =  $p[8]->url;
                    }
                $photos[] = ["id" => $con->id,
                                "url" => $url];

            }
            
        }
        file_put_contents('uploads/technograd/photosUpdated.json', json_encode($photos));  

    }

    public function getJson()
    {
        $pdo = \App\Service\DB::get();
        $stmt = $pdo->prepare("
            SELECT
                *
            FROM
                `productss`
               
        ");
        $stmt->execute();
        $products = $stmt->fetchAll(); 
        file_put_contents('uploads/technograd/infoIdsArticuls.json', json_encode($products));
    }

    public function getNotProcessed()
    {
        $content = file_get_contents('uploads/technograd/photos.json');
        $content = json_decode($content);
        $photos = [];
        foreach($content as $con){
            $con = json_decode($con);
            if(!$con->pictures){
               $photos[] = json_encode($con->id);
            }

        }

        $content = file_get_contents('uploads/technograd/specs.json');
        $content = json_decode($content);
        $specs = [];
        foreach($content as $con){
            $con = json_decode($con);
            if(isset($con->specifications) && !$con->specifications){
               $specs[] = json_encode($con->id);
            }

        }
        file_put_contents('uploads/technograd/specsNotProcessed.json', json_encode($specs));
        file_put_contents('uploads/technograd/photosNotProcessed.json', json_encode($photos));

    }

    public function getPhotos()
    {
        $idsFromJson = [];
        $content = file_get_contents('uploads/technograd/photos.json');
        $content = json_decode($content);
        foreach($content as $con){
           $con = json_decode($con);
           if(isset($con->id)){
               $idsFromJson[] = $con->id;
           }
        }
        $idsParsed = $this->getIds();
        $ids = [];
        for($i=0; $i<count($idsParsed); $i++){
            if(!in_array($idsParsed[$i], $idsFromJson)){
                $ids[] = $idsParsed[$i];

            }

        }
        
        
        $key = '1c42cb14d6e779228f2adb753e74fc79';
        $photos = [];
        $ch = curl_init();
        if(count($ids) > 0){
            for($i=0; $i<count($ids); $i++){
                curl_setopt($ch, CURLOPT_URL,"https://market-scanner.ru/api/photos");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS,['id'=>$ids[$i],'key'=>$key]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);  
                $response = json_decode($response);              
                if(!isset($response->error)){
                    $photos[] = json_encode($response);            
                }
                else{
                    $photos[] = json_encode(["id" => $ids[$i],
                                            "pictures" => []
                                 ]);
                }
    
            }
        }
        $newContent = array_merge($content, $photos);
        file_put_contents('uploads/technograd/photos.json', json_encode($newContent));

    }
    public function getSpecifications()
    {
        $idsFromJson = [];
        $content = file_get_contents('uploads/technograd/specs.json');
        $content = json_decode($content);
        foreach($content as $con){
           $con = json_decode($con);
           if(isset($con->id)){
               $idsFromJson[] = $con->id;
           }
        }
        $idsParsed = $this->getIds();
        $ids = [];
        for($i=0; $i<count($idsParsed); $i++){
            if(!in_array($idsParsed[$i], $idsFromJson)){
                
                $ids[] = $idsParsed[$i];

            }

        }
     
        $key = '1c42cb14d6e779228f2adb753e74fc79';
        $specs = [];
        $ch = curl_init();
        if(count($ids) > 0){
            for($i=0; $i<count($ids); $i++){
                curl_setopt($ch, CURLOPT_URL,"https://market-scanner.ru/api/specs");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS,['id'=>$ids[$i],'key'=>$key]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                $response = json_decode($response);
                if(!isset($response->error)){
                    $specs[] = json_encode($response);            
                }
                else{
                    $specs[] = json_encode(["id" => $ids[$i],
                                            "specifications" => []
                                 ]);
                }
               
                            
    
            }
        }

        $newContent = array_merge($content, $specs);
        file_put_contents('uploads/technograd/specs.json', json_encode($newContent));

    } 
    public function getIds()
    {
        $pdo = \App\Service\DB::get();   
        $stmt = $pdo->prepare("
            SELECT
                `articul`
            FROM
                `productss`
            WHERE `articul`  != 0
        ");   
        $stmt->execute();
        $tempProdArticuls = $stmt->fetchAll();
        $productArticuls = [];
        foreach($tempProdArticuls as $tempProdArticul){
            $productArticuls[] = $tempProdArticul['articul'];
        }

        return $productArticuls;
    }

    public function insertProductIds($products)
    {
        
        $pdo = \App\Service\DB::get();
        $stmt2 = $pdo->prepare("
                UPDATE 
                    `productss`
                SET
                    `articul` = :art                     
                WHERE `name` = :name
            ");
        error_reporting(0);
        $i = 0;
        foreach($products as $product){
            if($product['articul'] == 0){
                $productId = $this->getProductId($product['name']);                
                $stmt2->execute([
                    ':art' => $productId ,
                    ':name' => $product['name']                  
                ]);
                $i++;
            }
           
        }
        error_reporting(1);

    }

    public function insertProducts($products)
    {
        $pdo = \App\Service\DB::get();         
        $stmt1 = $pdo->prepare("
            SELECT
                `name`,`id_price`
             FROM
            `productss`
         ");   
        $stmt1->execute();
        $tempProds = $stmt1->fetchAll();
        
        $productNames = [];
        $productIds = [];
        foreach($tempProds as $tempProd){
            $productNames[] = $tempProd['name'];
            $productIds[] = $tempProd['id_price'];
        }
       
        $productNamesNew = [];
        $productIdsNew = [];
        for($i=0; $i<count($products); $i++){           
            if(!in_array($products[0][$i], $productNames) && !in_array($products[1][$i], $productIds)){
                $productNamesNew[] = $products[0][$i];
                $productIdsNew[] = $products[1][$i];
            }
            
        }
        $stmt1 = $pdo->prepare("
            INSERT INTO 
            `productss`(
                `name`               
            ) VALUES (
                :name
            )
        ");
        
        foreach($productNamesNew as $productName){
            $stmt1->execute([
                ':name' => $productName                    
            ]);
        }
        $stmt2 = $pdo->prepare("
            INSERT INTO 
                `productss`(
                    `id_price`               
                ) VALUES (
                    :idp
                )
            
        ");
        
        foreach($productIdsNew as $productId){
            $stmt2->execute([
                ':idp' => $productId                    
            ]);
        }
        
    }
    public function getProductId($prodName)
    {
        date_default_timezone_set('Asia/Almaty');
        //$date = date('H', time());       
        $pattern = '/\/product--[a-z0-9\-]+\/([0-9]+)\?/u';
        $productName = $this->getModifiedProductName($prodName);
        $productId = [];
        $result = [];                 

        $subject = $this->file_get_contents_curl('https://market.yandex.kz/search?text='.$productName);
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $internalErrors = libxml_use_internal_errors(true);
        $doc->loadHTML ( $subject );
        libxml_use_internal_errors($internalErrors);
        $xpath = new \DOMXpath($doc);        
        $titles = $doc->getElementsByTagName('title');  
        foreach ($titles as $title) {
            if($title === 'Ой' ){
                sleep(3600);
            }
            
        } 
        preg_match_all($pattern, $subject, $result); 
        
        if(isset($result[1][0])){
            $productId = $result[1][0];                          
        } 
        
        return $productId;

    }

    public function getModifiedProductName($prodName)
    {
        $patternName = '/([A-Z]{1}[a-zA-Z0-9\s\-\,\(\)\.\/\+\"]+)/u';      
        $pn = [];        
        preg_match_all($patternName,$prodName,$pn); 
        if(isset($pn[1][0])){
            $productName = preg_replace('/\s+/', '%20',$pn[1][0]);          

        }  
        return $productName;
    }

    public function getProducts($data)
    {
        for($i=6; $i<=count($data); $i++){
            if($data[$i]['C'] !== null){
                $prodNames[] = trim($data[$i]['B']); 
                $prodIds[] = $data[$i]['C'];                  
                
            }
        }

        return [$prodNames,$prodIds];

    }

    public function file_get_contents_curl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_MAXREDIRS , 1000);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 30);
        

        if (curl_exec($ch) === FALSE) {
            die("Curl Failed: " . curl_error($ch));
        } else {
            $data = curl_exec($ch);
        }

        curl_close($ch);
        return $data;
    }


}


        