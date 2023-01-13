<?php 

namespace App\Controller;
require 'vendor/autoload.php';
//require 'Header.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Upload
{
    public function run()
    {        
        
        $providers = ['seakoo','temptools','magkaznu'];

        if(isset($_POST['SubmitButton'])){ //check if form was submitted      
             
            $target_dir = 'uploads/'.$providers[$_POST['provider']].'/';            
            $target_file = $target_dir . 'pricelist.xls';
            $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
            
            move_uploaded_file($_FILES["pricelist"]["tmp_name"], $target_file);
            
            echo 'Файл успешно загружен!';
            
        
        }   
        
        
        $inputFileName = 'uploads/'.$providers[$_POST['provider']].'/'.'pricelist.xls';
        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);        
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load($inputFileName);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        $newSheetData = [];
        for($i=2; $i<=count($sheetData); $i++){
            $newSheetData[] = $sheetData[$i];
            
        }
        
            //  var_dump($newSheetData);
            //  $this->updateProductsEbs($newSheetData);
            // $this->insertProducts($newSheetData);
            $this->insertProductsEbs($newSheetData);
            //  $this->insertAuthor($newSheetData);
            // $this->insertProductCategory($newSheetData);
            //  $this->insertBookPass($sheetData);
            // $this->alterTableProducts($newSheetData); 
            // $this->insertSouvenirs($newSheetData); 
            // $this->insertProductAuthors($sheetData);
        // $this->insertProductSpec($newSheetData);
        // $this->insertSpec($newSheetData);
            // $this->insertCategors($sheetData);
        
        // $this->insertCategories($sheetData);
        // $this->insertISBN($sheetData);
        // $this->insertPublishhouse($newSheetData);
        // $this->updateProducts($newSheetData);
        // $this->updateProdAuthor();
        // $this->updateProdCategor();
        // $this->updatePublishHouses();
        
    } 

    public function updatePublishHouses()
    {
        $pdo = \App\Service\DB::get();
        $stmt = $pdo->prepare("
            SELECT
                *
            FROM
                `product_publish_house`                              
        ");

        $stmt2 = $pdo->prepare("
            UPDATE
                `products`
            SET
                `publish_house` = :publish
            WHERE `id` = :id                             
        ");

        $stmt->execute();
        $publishHouses = $stmt->fetchAll();
        
        foreach($publishHouses as $publish){
            $stmt2->execute([
                ':publish' => $publish['publish_house_id'],
                ':id' => $publish['product_id']
            ]);
        }
    }
    public function updateProdCategor()
    {
        $pdo = \App\Service\DB::get();
        $stmt = $pdo->prepare("
            SELECT
                *
            FROM
                `category_product` 
            WHERE `product_id` > 4100               
        ");

        $stmt2 = $pdo->prepare("
            SELECT
                `category_id`
            FROM
                `category_product` 
            WHERE `product_id` = :prod_id 
            AND `product_id` > 4100 
                      
        ");

        $stmt3 = $pdo->prepare("
            UPDATE
                `products`
            SET 
                `categories` = :categors
            WHERE `id` = :prod_id 
            AND `id` > 4296        
        ");  

        $stmt->execute();
        $categorProds = $stmt->fetchAll();
      
        foreach($categorProds as $categorProd){
            $stmt2->execute([
                ':prod_id' => $categorProd['product_id']
            ]);
            $categorIds = $stmt2->fetchAll();
            $categors = [];
            foreach($categorIds as $categorId){
                $categors[] = $categorId['category_id'];
            }
            $categors = implode(',',$categors);

            $stmt3->execute([
                ':categors' => $categors,
                ':prod_id' => $categorProd['product_id']+196
            ]);
            // var_dump($categors);
        }


        // var_dump( $authorProd);

    }
    public function updateProdAuthor()
    {
        $pdo = \App\Service\DB::get();
        $stmt = $pdo->prepare("
            SELECT
                *
            FROM
                `author_product` 
            WHERE `product_id` > 4100              
        ");

        $stmt2 = $pdo->prepare("
            SELECT
                `author_id`
            FROM
                `author_product` 
            WHERE `product_id` = :prod_id 
            AND `product_id` > 4100 
                      
        ");

        $stmt3 = $pdo->prepare("
            UPDATE
                `products`
            SET 
                `authors` = :authors
            WHERE `id` = :prod_id 
            AND `id` > 4296        
        ");  

        $stmt->execute();
        $authorProds = $stmt->fetchAll();
      
        foreach($authorProds as $authorProd){
            $stmt2->execute([
                ':prod_id' => $authorProd['product_id']
            ]);
            $authorIds = $stmt2->fetchAll();
            $authors = [];
            foreach($authorIds as $authorId){
                $authors[] = $authorId['author_id'];
            }
            $authors = implode(',',$authors);

            $stmt3->execute([
                ':authors' => $authors,
                ':prod_id' => $authorProd['product_id']+196
            ]);
            // var_dump($categors);
        }


        // var_dump( $authorProd);

    }
    public function insertBookPass($products)
    {
        $pdo = \App\Service\DB::get();
        $stmt = $pdo->prepare("
            UPDATE
                `products`
            SET 
                `book` = :book,
                `pass` = :pass
            WHERE `name` like :name            
        ");   
        
        foreach($products as $product){
            $stmt->execute([
                ':book' => 'books/'.$product['H'].'.pdf',
                ':pass' => $product['I'],
                ':name' => $product['B']
            ]);           
        }

       

    } 

    public function insertProductAuthors($products)
    {

        $pdo = \App\Service\DB::get();
        $stmt = $pdo->prepare("
            SELECT
                `id`
            FROM
                `authors`
            WHERE `name_ru` like :name1
            OR `name_kz` like :name2
            OR `name_en` like :name3 
        ");

        $stmt2 = $pdo->prepare("
            INSERT INTO 
                `author_product`(
                    `product_id`,
                    `author_id`                                
                ) VALUES (
                    :product_id,
                    :author_id                    
                )
        ");

        $stmt3 = $pdo->prepare("
            SELECT
                `id`
            FROM
                `products`
            WHERE `name` like :name           
        ");

        $ids = [];
        $idss = [];
        foreach($products as $product){
            $stmt3->execute([
                ':name' => $product['B']
            ]);
            $prodId = $stmt3->fetch();
            // $ids[] = $prodId;
            $authors = [];            
            $authors[] = explode(',',trim($product['A']));    
           
            foreach($authors as $authorAr){
                foreach($authorAr as $author){                   
                    $stmt->execute([
                        ':name1' => '%'.trim($author).'%',
                        ':name2' => '%'.trim($author).'%',
                        ':name3' => '%'.trim($author).'%'
                    ]);                    
                    $authorId = $stmt->fetch()['id'];                  
                    if($authorId == null){
                        $author = explode(' ', trim($author))[0];
                        $stmt->execute([
                                ':name1' => '%'.$author.'%',
                                ':name2' => '%'.$author.'%',
                                ':name3' => '%'.$author.'%'
                        ]);
                        $authorId = $stmt->fetch()['id'];
                        if($authorId == null){
                            $idss[] = $author;
                        }
                    }
                     $ids[] = [
                        'prodId' => $prodId['id'],
                        'authorId' =>  $authorId,
                    ];          
                }  
            } 
           
        }

        foreach($ids as $id){
            $stmt2->execute([
                ':product_id' => $id['prodId'],
                ':author_id' => $id['authorId']                      
            ]);

        }
        var_dump($idss); 
        
        // $authorIds = [];
        // $authorNames = [];
        // foreach($authorsNew as $author){
        //     // $author = explode(' ', $author)[0];
        //     $stmt->execute([
        //         ':name1' => '%'.$author.'%',
        //         ':name2' => '%'.$author.'%',
        //         ':name3' => '%'.$author.'%'
        //     ]);

        //     if($stmt->fetchAll()){
        //         $authorIds[] = $stmt->fetchAll();   
        //     }else{
        //          $author = explode(' ', $author)[0];
        //          $stmt->execute([
        //             ':name1' => '%'.$author.'%',
        //             ':name2' => '%'.$author.'%',
        //             ':name3' => '%'.$author.'%'
        //          ]);

        //         if($stmt->fetchAll()){
        //             $authorIds[] = $stmt->fetchAll();   
        //         }
        //         else{
        //             $authorNames[] = $author;
        //         }
        //     }          
            
        // }
        // var_dump($authorNames);
        // $Ids = [];
        // foreach($products as $product){
        //     $authors = explode(',', trim($product['A']));            
        //     $stmt3->execute([
        //         ':name' => $product['B']
        //     ]);
        //     $prod_ids = $stmt3->fetchAll();
        //     // $Ids[] =  $prod_ids;
          
        //     foreach($authors as $author){
        //         $stmt->execute([
        //             ':name1' => '%'.trim($author).'%',
        //             ':name2' => '%'.trim($author).'%',
        //             ':name3' => '%'.trim($author).'%'
        //         ]);
        //         $author_ids = $stmt->fetchAll();
        //         // $Ids[] = $author_ids;

        //         if($author_ids == null){
        //             $author = explode(' ', $author)[0];
        //             $stmt->execute([
        //                 ':name1' => '%'.trim($author).'%',
        //                 ':name2' => '%'.trim($author).'%',
        //                 ':name3' => '%'.trim($author).'%'
        //             ]);
        //             $author_ids = $stmt->fetchAll();
        //             // $Ids[] = $author_ids;
        //         }
               
        //         // if($author_ids[0]['id'] != null){
        //             $stmt2->execute([
        //                 ':product_id' => $prod_ids[0]['id'],
        //                 ':author_id' => $author_ids[0]['id']                      
        //             ]);
        //         // }
        //     }
        // }
        // var_dump($Ids);

        

    }
    public function insertAuthor($products){

        $pdo = \App\Service\DB::get();
        $stmt = $pdo->prepare("
            INSERT INTO 
            `authors`(
                `name_ru`,
                `name_kz`,
                `name_en`,
                `slug`               
            ) VALUES (
                :name_ru,
                :name_kz,
                :name_en,
                :slug
            )
        ");
       
        $stmt2 = $pdo->prepare("
            SELECT
                `id`
            FROM
                `authors`
            WHERE `name_ru` like :name1
            OR `name_kz` like :name2
            OR `name_en` like :name3 
        "); 

        $stmt3 = $pdo->prepare("
            INSERT INTO 
                `author_product`(
                    `author_id`,
                    `product_id`        
                ) VALUES (
                    :author_id,
                    :product_id
                )
            ");
        
        $stmt4 = $pdo->prepare("
            SELECT
                `id`
            FROM
                `products`
            WHERE `name_ru` like :name1
            OR `name_kz` like :name2
            OR `name_en` like :name3             
        "); 

        foreach($products as $product){
            $stmt4->execute([
                ':name1' => '%'.trim($product['A']).'%',
                ':name2' => '%'.trim($product['B']).'%', 
                ':name3' => '%'.trim($product['C']).'%'               
            ]);
            $id = $stmt4->fetch()['id'];

            $ru = explode(',', $product['D']);
            $kz = explode(',', $product['E']);
            $en = explode(',', $product['F']);

            for($i=0; $i<count($ru); $i++){
                $stmt2->execute([
                    ':name1' => '%'.trim($ru[$i]).'%',
                    ':name2' => '%'.trim($kz[$i]).'%',
                    ':name3' => '%'.trim($en[$i]).'%',
                ]);                
                $authors = $stmt2->fetch();
                if(!empty($authors)){
                    // var_dump($ru[$i]);
                    // var_dump($id);
                    
                    $stmt3->execute([
                        ':author_id' => $authors['id'],
                        ':product_id' => $id
                    ]);

                    // $stmt->execute([
                    //     ':name_ru' => trim($ru[$i]),
                    //     ':name_kz' => trim($kz[$i]),
                    //     ':name_en' => trim($en[$i]),
                    //     ':slug' => $this->makeUrlCode($ru[$i])
                    // ]); 
                }
            }

         
            
            // if(!$stmt2->fetchAll()){
            //     $stmt->execute([
            //         ':name_ru' => $product['authorNameRu'],
            //         ':name_kz' => $product['authorNameRu'],
            //         ':name_en' => $product['authorNameEn'],
            //         ':slug' => $this->makeUrlCode($product['authorNameRu']),
            //         ':description' => null      
            //     ]);
            // }
                       
        }
       
    }
public function updateProducts($products){
    $pdo = \App\Service\DB::get();
            $stmt = $pdo->prepare("
                UPDATE
                    `products`
                SET
                    `name_ru` = :name_ru,
                    `name_kz` = :name_kz,
                    `name_en` = :name_en,
                    `desc_kz` = :desc_kz,
                    `desc_ru` = :desc_ru,
                    `desc_en` = :desc_en,
                    `description` = :desc
                WHERE  `id` = :id
            "); 
          

            // $prods = [];
            // foreach($products as $product){
            //     $stmt2->execute([
            //         ':name' => '%'.trim($product['B']).'%'
            //     ]);
            //     $prods[] = $stmt2->fetchAll();
            // }
            // var_dump($prods);
            foreach($products as $product){            
                $stmt->execute([                   
                    ':name_ru' => $product['C'],
                    ':name_kz' => $product['D'],
                    ':name_en' => $product['E'],
                    ':desc_ru' => $product['M'],
                    ':desc_kz' => $product['N'],
                    ':desc_en' => $product['O'],
                    ':desc' => $product['L'],
                    ':id' => $product['A']                
                ]);            
            }

            
}
    public function updateProductsEbs($products){
        $pdo = \App\Service\DB::get();
                $stmt = $pdo->prepare("
                    UPDATE
                        `products`
                    SET
                        `publish_year` = :p_y                   
                    WHERE  `title` like :title
                ");
                $stmt1 = $pdo->prepare("
                    Select
                        *
                    From
                    products                                         
                    WHERE  `title` like :title
                "); 
                $prods = [];
                foreach($products as $product){
                    $stmt1->execute([
                        ':title' => '%'.trim($product['C']).'%',
                    ]);
                    if(empty($stmt1->fetchAll()) || $stmt1->fetchAll() == null ){
                        $prods[] = $product['C'];
                    }

                }
                    // $stmt1->fetchAll();
                var_dump($prods);

                // foreach($products as $product){            
                //     $stmt->execute([                   
                //         ':title' => '%'.$product['C'].'%',
                //         ':p_y' => '2020'                               
                //     ]);            
                // }
    }     

    public function insertISBN($products){
        $pdo = \App\Service\DB::get();
        $stmt = $pdo->prepare("
                INSERT INTO 
                `isbn_list`(
                    `isbn`,
                    `status`,
                    `created_at`
                                
                ) VALUES (
                    :isbn,
                    :status,
                    :created_at
                   
                )
            ");            
      
        $date = new \DateTime('now');
        foreach($products as $product){            
                $stmt->execute([
                    ':isbn' => $product['A'],
                    ':status' => '0',
                    ':created_at' => $date->format('Y.m.d')
                ]);            
        }

    }
            
        
    
    public function insertPublishhouse($products){
        $pdo = \App\Service\DB::get();
        $stmt = $pdo->prepare("
                INSERT INTO 
                `product_publish_house`(
                    `product_id`,
                    `publish_house_id`
                                
                ) VALUES (
                    :product_id,
                    :publish_house_id
                   
                )
            "); 
            $stmt2 = $pdo->prepare("
                SELECT
                    `id`
                FROM
                    `products`
                WHERE `name_ru` = :name
            ");  
        // $stmt2 = $pdo->prepare("
        //     SELECT
        //         `id`
        //     FROM
        //         `products`
        //     WHERE `publish_house` = :publish_house               
        // ");

        // $stmt3 = $pdo->prepare("
        //     SELECT
        //         `id`,`name_ru`
        //     FROM
        //         `publish_houses`            
        // ");
        // $stmt3->execute();
        // $publish_houses = $stmt3->fetchAll();
        
        foreach($products as $product){
            $stmt2->execute([
                ':name' => $product['A']
            ]);
            $product_id = $stmt2->fetchAll();            
            
                $stmt->execute([
                    ':product_id' => $product_id[0]['id'],
                    ':publish_house_id' => '4'
                ]);
            
        }
            
        
    }
    public function alterTableProducts($products){
        $pdo = \App\Service\DB::get();

        $stmt2 = $pdo->prepare("
            UPDATE
                `products`
            SET   
                `content` = :content
            WHERE `name` = :name 
            AND `content` = 'contents/.pdf'       
            ");

        
        // $prodIds = [];
        // foreach($products as $product){
        //     $stmt->execute();
        //     $prodIds[] = $stmt->fetchAll()[0]['id'];
        // }
        // var_dump($prodIds);
        // die();
        foreach($products as $product){
            $stmt2->execute([
                ':content' => 'contents/'.$product['J'].'.pdf',
                ':name' => $product['B']
            ]);
        }
        

    }
    public function insertSouvenirs($products){
        $pdo = \App\Service\DB::get();
            $stmt = $pdo->prepare("
                INSERT INTO 
                `souvenirs`(
                    `name`,
                    `material`,
                    `works`,
                    `price` ,
                    `image`             
                ) VALUES (
                    :name,
                    :material,
                    :works,
                    :price,
                    :image
                )
            ");            
           
            foreach($products as $product){
                $stmt->execute([
                    ':name' => $product['B'],
                    ':material' => $product['C'],
                    ':works' => $product['D'],
                    ':price' => $product['E'],
                    ':image' => 'souvenirs/'.$product['F'].'.jpg'
                ]);
                
            }
           

    }
    public function insertCategors($products){
        $pdo = \App\Service\DB::get();
            $stmt = $pdo->prepare("
                INSERT INTO 
                `category`(
                    `name`,
                    `slug`,
                    `parent_id`              
                ) VALUES (
                    :name,
                    :slug,
                    :parent_id
                )
            ");
            $stmt2 = $pdo->prepare("
                SELECT
                    `name`
                FROM
                    `category`               
            ");
           
            foreach($products as $product){
               
                
                $stmt2->execute();
                $categors = $stmt2->fetchAll();
                $newCategors = []; 
                foreach($categors as $categor){
                    $newCategors[] = trim($categor['name']);
                }

                if(!in_array(trim($product['D']),$newCategors)){
                    $stmt->execute([
                        ':name' => $product['D'],
                        ':slug' => $this->makeUrlCode($product['D']),
                        ':parent_id' => '69'
                    ]);

                }
            }
           

    }
    public function insertProductAuthor($products){
        $pdo = \App\Service\DB::get();
            $stmt = $pdo->prepare("
                INSERT INTO 
                `author_product`(
                    `product_id`,
                    `author_id`              
                ) VALUES (
                    :product_id,
                    :author_id
                )
            ");

            $stmt2 = $pdo->prepare("
                        SELECT
                            `id`
                        FROM
                            `authors`
                        WHERE `name` = :name
                    ");
            $stmt3 = $pdo->prepare("
                    SELECT
                        `id`
                    FROM
                        `products`
                    WHERE `name` = :name
                ");   
        $authors = [];
        foreach($products as $product){
            $authors[] = explode(',', trim($product['A']));

        }   

        // $authorsNew = [];
        // foreach($authors as $author){
        //     foreach($author as $auth){
        //         $authorsNew[] = trim($auth);
        //     }
        // }
        // $authorsNew = array_unique($authorsNew);

        
        foreach($products as $product){
            $authors = explode(',', trim($product['A']));            
            $stmt3->execute([
                ':name' => $product['B']
            ]);
            $prod_ids = $stmt3->fetchAll();

            foreach($authors as $author){
                $stmt2->execute([
                    ':name' => trim($author)
                ]);
                $author_ids = $stmt2->fetchAll();
               
                if($author_ids[0]['id'] != null){
                    $stmt->execute([
                        ':product_id' => $prod_ids[0]['id'],
                        ':author_id' => $author_ids[0]['id']                      
                    ]);
                }
            }
        }

    }
    
        public function insertProducts($products){
            $pdo = \App\Service\DB::get();
            $stmt = $pdo->prepare("
                INSERT INTO 
                `products`(
                    `name`,
                    `name_ru`,
                    `name_kz`,
                    `name_en`,
                    `slug`,
                    `publish_house`,
                    `publish_date`,
                    `price_soft`,
                    `price_hard`,
                    `image`,
                    `content`,
                    `book`,
                    `pass`,
                    `origin`,
                    `description`,
                    `desc_ru`,
                    `desc_kz`,
                    `desc_en`,
                    `isbn`,
                    `created_at`
                                 
                ) VALUES (
                    :name,
                    :name_ru,
                    :name_kz,
                    :name_en,
                    :slug,
                    :publish_house, 
                    :publish_date,              
                    :price_soft,
                    :price_hard,
                    :image,
                    :content,
                    :book,
                    :pass,
                    :origin,
                    :description,
                    :desc_ru,
                    :desc_kz,
                    :desc_en,
                    :isbn,
                    :date
                )
            ");
            
            $stmt2 = $pdo->prepare("
                SELECT
                    `name_ru`
                FROM
                    `products`
                ");
            
            $stmt3 = $pdo->prepare("
                SELECT
                    `name_kz`
                FROM
                    `products`
                ");

            $stmt4 = $pdo->prepare("
                SELECT
                    `name_en`
                FROM
                    `products`
                ");
    
            foreach($products as $product){
                $stmt2->execute();
                $prods1 = $stmt2->fetchAll();
                $newProds1 = []; 
                foreach($prods1 as $prod){
                    $newProds1[] = trim($prod['name_ru']);
                }

                $stmt3->execute();
                $prods2 = $stmt3->fetchAll();
                foreach($prods2 as $prod){
                    $newProds2[] = trim($prod['name_kz']);
                }

                $stmt4->execute();
                $prods3 = $stmt4->fetchAll();
                foreach($prods3 as $prod){
                    $newProds3[] = trim($prod['name_en']);
                }               
    
                $date = new \DateTime('now');
                if(!in_array(trim($product['A']), $newProds1) && !in_array(trim($product['B']), $newProds2) && !in_array(trim($product['C']), $newProds3)){
                                      
                if($product['Q'] == 'русский'){
                    $stmt->execute([
                        ':name' => $product['A'],                    
                        ':name_ru' => $product['A'],
                        ':name_kz' => $product['B'],
                        ':name_en' => $product['C'],
                        ':slug' => $this->makeUrlCode($product['A']),
                        ':publish_house' => '4',
                        ':publish_date' => $product['K'],
                        ':price_soft' => ($product['M'] == '*') ? null : $product['M'],  
                        ':price_hard' => ($product['N'] == '*') ? null : $product['N'],                     
                        ':image' =>'products/'.$product['O'].'.jpg',
                        ':content' => 'contents/'.$product['P'].'.pdf',  
                        ':book' =>'books/'.$product['R'].'.pdf',
                        ':pass' => $product['S'],
                        ':origin' => '1',
                        ':description' => $product['G'],                                        
                        ':desc_ru' => $product['G'],
                        ':desc_kz' => $product['H'],
                        ':desc_en' => $product['I'],
                        ':isbn' => $product['J'], 
                        ':date' => $date->format('Y.m.d')                        
                    ]);
                }
                elseif($product['R'] == 'казахский'){
                    $stmt->execute([
                        ':name' => $product['B'],                    
                        ':name_ru' => $product['A'],
                        ':name_kz' => $product['B'],
                        ':name_en' => $product['C'],
                        ':slug' => $this->makeUrlCode($product['A']),
                        ':publish_house' => '4',
                        ':publish_date' => $product['K'],
                        ':price_soft' => ($product['M'] == '*') ? null : $product['M'],  
                        ':price_hard' => ($product['N'] == '*') ? null : $product['N'],                     
                        ':image' =>'products/'.$product['O'].'.jpg',
                        ':content' => 'contents/'.$product['P'].'.pdf',  
                        ':book' =>'books/'.$product['R'].'.pdf',
                        ':pass' => $product['S'],
                        ':origin' => '2',
                        ':description' => $product['H'],                                        
                        ':desc_ru' => $product['G'],
                        ':desc_kz' => $product['H'],
                        ':desc_en' => $product['I'],
                        ':isbn' => $product['J'], 
                        ':date' => $date->format('Y.m.d')                 
                    ]);
                }
                else{
                    $stmt->execute([
                        ':name' => $product['C'],                    
                        ':name_ru' => $product['A'],
                        ':name_kz' => $product['B'],
                        ':name_en' => $product['C'],
                        ':slug' => $this->makeUrlCode($product['A']),
                        ':publish_house' => '4',
                        ':publish_date' => $product['K'],
                        ':price_soft' => ($product['M'] == '*') ? null : $product['M'],  
                        ':price_hard' => ($product['N'] == '*') ? null : $product['N'],                     
                        ':image' =>'products/'.$product['O'].'.jpg',
                        ':content' => 'contents/'.$product['P'].'.pdf',  
                        ':book' =>'books/'.$product['R'].'.pdf',
                        ':pass' => $product['S'],
                        ':origin' => '3',
                        ':description' => $product['I'],                                        
                        ':desc_ru' => $product['G'],
                        ':desc_kz' => $product['H'],
                        ':desc_en' => $product['I'],
                        ':isbn' => $product['J'], 
                        ':date' => $date->format('Y.m.d')                     
                    ]);
                }
                    
    
                }
            }
        }
        public function insertProductsEbs($products){
            $pdo = \App\Service\DB::get();
            $stmt = $pdo->prepare("
            INSERT INTO 
            `products`(
                `name`,
                `slug`,
                `publish_date`,
                `image`,
                `book`,
                `pass`,
                `origin`,
                `description`,
                `isbn`,
                `categories`
                             
            ) VALUES (
                :name,
                :slug,
                :publish_date, 
                :image,
                :book,
                :pass,
                :origin,
                :description,
                :isbn,
                :categories
            )
        ");
            
            // $stmt2 = $pdo->prepare("
            //     SELECT
            //         `title`
            //     FROM
            //         `products`
            //     ");            
            
    
            foreach($products as $product){
                // $stmt2->execute();
                // $prods = $stmt2->fetchAll();
                // $newProds = []; 
                // foreach($prods as $prod){
                //     $newProds[] = trim($prod['title']);
                // }

                // $date = new \DateTime('now');
                // if(!in_array(trim($product['B']), $newProds)){                                     
                    if($product['E'] == 'русский'){
                        $stmt->execute([
                            ':name' => $product['B'],
                            ':slug' => $this->makeUrlCode($product['B']),
                            ':publish_date' => $product['F'],                   
                            ':image' => $product['G'].'.jpg',
                            ':book' =>$product['H'].'.pdf',
                            ':pass' => 'pasS1122',
                            ':origin' => '1',
                            ':description' => $product['D'],
                            ':isbn' => $product['C'],
                            ':categories' => '155'                   
                        ]);
                    }
                elseif($product['E'] == 'казахский'){
                    $stmt->execute([
                        ':name' => $product['B'],
                        ':slug' => $this->makeUrlCode($product['B']),
                        ':publish_date' => $product['F'],                   
                        ':image' => $product['G'].'.jpg',
                        ':book' =>$product['H'].'.pdf',
                        ':pass' => 'pasS1122',
                        ':origin' => '2',
                        ':description' => $product['D'],
                        ':isbn' => $product['C'],
                        ':categories' => '155'                       
                    ]);
                }
                else{
                    $stmt->execute([
                        ':name' => $product['B'],
                        ':slug' => $this->makeUrlCode($product['B']),
                        ':publish_date' => $product['F'],                   
                        ':image' => $product['G'].'.jpg',
                        ':book' =>$product['H'].'.pdf',
                        ':pass' => 'pasS1122',
                        ':origin' => '3',
                        ':description' => $product['D'],
                        ':isbn' => $product['C'],
                        ':categories' => '155'                     
                    ]);
                }
                    
    
                // }
            }
        }
    // public function insertProductSpec($specs){
    //     $pdo = \App\Service\DB::get();
    //     $stmt = $pdo->prepare("
    //         INSERT INTO 
    //         `product_specification`(
    //             `product_id`,
    //             `specification_id`,
    //             `value`

    //         ) VALUES (
    //             :product_id,
    //             :specification_id,
    //             :value                
    //         )
    //     ");
    //     $stmt2 = $pdo->prepare("
    //         SELECT
    //             `id`
    //         FROM
    //             `specifications`
    //         WHERE `name` = :name
    //     ");

    //     $stmt3 = $pdo->prepare("
    //         SELECT
    //             `id`
    //         FROM
    //             `products`
    //         WHERE `details` = :details
    //     ");

    //     $spec_ids = [];  
    //     foreach($specs[0] as $key=>$spec){
    //         // echo $spec;
    //         $stmt2->execute([
    //             ':name' => $spec
    //         ]);
    //         $spec_ids[$key] = $stmt2->fetch()['id'];            
    //     }        

      
    //     $newSheetData = [];
    //     for($i=1; $i<count($specs); $i++){
    //         $newSheetData[] = $specs[$i];
            
    //     }
       
    //     $newSpecs = [];
    //     $new = [];
    //     for($i=0; $i<count($newSheetData); $i++){
    //             foreach($newSheetData[$i] as $key=>$spec){
    //                     $newSpecs[$key] = $spec; 
    //             }
    //             $new[] = $newSpecs;
            
    //     }
       
    //     foreach($new as $array){                     
    //         $stmt3->execute([
    //             ':details' => $array['A']
    //         ]);
    //         $prod_id = $stmt3->fetchAll(); 
    //         foreach($array as $key=>$spec){
    //             if($array[$key] != null && $spec_ids[$key] != null){
                    
    //                     $stmt->execute([
    //                     ':product_id' => $prod_id[0]['id'],
    //                     ':specification_id' => $spec_ids[$key],
    //                     ':value' => $spec
    //                 ]);
    //             }

              

    //         }
    //     }

    
        
    // }
    // public function insertSpec($specs){
    //     $pdo = \App\Service\DB::get();
    //     $stmt = $pdo->prepare("
    //         INSERT INTO 
    //         `specifications`(
    //             `name`                              
    //         ) VALUES (
    //             :name                
    //         )
    //     ");

    //     foreach($specs[0] as $spec){
    //         // echo $spec;
    //         $stmt->execute([
    //             ':name' => $spec
    //         ]);
    //     }


    // }
    function translit($string){
        $converter = array(
            'а' => 'a',   'б' => 'b',   'в' => 'v',
            'г' => 'g',   'д' => 'd',   'е' => 'e',
            'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
            'и' => 'i',   'й' => 'y',   'к' => 'k',
            'л' => 'l',   'м' => 'm',   'н' => 'n',
            'о' => 'o',   'п' => 'p',   'р' => 'r',
            'с' => 's',   'т' => 't',   'у' => 'u',
            'ф' => 'f',   'х' => 'h',   'ц' => 'c',
            'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
            'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
            'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
            'ә' => 'а',   'ө' => 'о',   'і' => 'i',
            'ң' => 'n\'',  'ғ' => 'g\'', 'ү' => 'u',
            'ұ' => 'u',   'қ' => 'q',   'һ' => 'h', 
             
            'А' => 'A',   'Б' => 'B',   'В' => 'V',
            'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
            'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
            'И' => 'I',   'Й' => 'Y',   'К' => 'K',
            'Л' => 'L',   'М' => 'M',   'Н' => 'N',
            'О' => 'O',   'П' => 'P',   'Р' => 'R',
            'С' => 'S',   'Т' => 'T',   'У' => 'U',
            'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
            'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
            'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
            'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
            'Ә' => 'A',   'Ө' => 'O',   'І' => 'I',
            'Ғ' => 'G\'', 'Ү' => 'U',   'Қ' => 'Q', 
            'Ұ' => 'U',    
        );
        return strtr($string, $converter);
    }
     
    function makeUrlCode($str){
        return trim(preg_replace('~[^-a-z0-9_]+~u', '-', strtolower($this->translit($str))), "-");
    }

    // public function insertProducts($products){

    //     $pdo = \App\Service\DB::get();
    //     $stmt = $pdo->prepare("
    //         INSERT INTO 
    //         `products`(
    //             `name`,
    //             `slug`,
    //             `details`,
    //             `price`,
    //             `description`,
    //             `image`               
    //         ) VALUES (
    //             :name,
    //             :slug,
    //             :details,
    //             :price,
    //             :description,
    //             :image
    //         )
    //     ");
        
    //     $stmt2 = $pdo->prepare("
    //         SELECT
    //             `name`
    //         FROM
    //             `products`
    //     "); 

    //     foreach($products as $product){
    //         $stmt2->execute();
    //         $prods = $stmt2->fetchAll();
    //         $newProds = []; 
    //         foreach($prods as $prod){
    //             $newProds[] = $prod['name'];
    //         }

    //         if(!in_array($product['A'], $newProds)){
    //             $stmt->execute([
    //                 ':name' => $product['A'],
    //                 ':slug' => $this->makeUrlCode($product['A']),
    //                 ':details' => $product['B'],   
    //                 ':price' => $product['D'],
    //                 ':description' => $product['C'],
    //                 ':image' => 'products/dummy/'.$product["G"]               
    //             ]);

    //         }
    //     }
    // }

     public function insertProductCategory($products){

            $pdo = \App\Service\DB::get();   
            $stmt = $pdo->prepare("
                SELECT
                    `id`
                FROM
                    `products`
                WHERE `name_ru` = :name
            ");   
            
            $stmt2 = $pdo->prepare("
                INSERT INTO 
                    `category_product`(                    
                        `product_id`,
                        `category_id`               
                    ) VALUES (
                        :product_id,
                        :category_id                        
                    )
            "); 
            
            $stmt3 = $pdo->prepare("
                SELECT
                    `id`
                FROM
                    `category`
                WHERE `name_ru` = :nameru
            ");  
                

            
            foreach($products as $product){
               
                $stmt->execute([
                    ':name' => $product['A']
                ]);
                $stmt3->execute([
                    ':nameru' => $product['L']
                ]);
                $categor_id = $stmt3->fetchAll();
                $prod_id = $stmt->fetchAll(); 

             
                // if(isset($prod_id[0]['id'])){
                if(isset($prod_id[0]['id']) && isset($categor_id[0]['id'])){
                    $stmt2->execute([
                        ':product_id' => $prod_id[0]['id'],
                        ':category_id' => $categor_id[0]['id']
                    ]);

                }                                            
            } 

        }

   
    public function insertCategories($products){

        $pdo = \App\Service\DB::get();
        $stmt = $pdo->prepare("
            INSERT INTO 
                `category`(
                    `name_ru`,
                    `slug`, 
                    `parent_id`
                                
                ) VALUES (
                    :name,
                    :slug,
                    :parent_id                       
                )
        "); 
        $stmt1 = $pdo->prepare("
                SELECT
                    `id`
                FROM
                    `category`
                WHERE `name_ru` = :name
            "); 
            
        $stmt2 = $pdo->prepare("
            SELECT
                `name_ru`
            FROM
                `category`
        "); 
        
        foreach($products as $product){
            $stmt2->execute();
            $categors = $stmt2->fetchAll();
            $newCategors = []; 
            foreach($categors as $categor){
                $newCategors[] = trim($categor['name_ru']);
            }

           
            if(!in_array(trim($product['F']), $newCategors)){

                $stmt1->execute([
                    ':name' => $product['E']
                ]);
                $id = $stmt1->fetchAll();
               
                   if(isset($id[0]['id'])){  
                        $stmt->execute([
                            ':name' => $product['F'],
                            ':slug' => $this->makeUrlCode($product['F']),
                            ':parent_id' => $id[0]['id']
                            
                        ]);                  
                                    
                    }
                    else{
                        $stmt->execute([
                            ':name' => $product['E'],
                            ':slug' => $this->makeUrlCode($product['E']),
                            ':parent_id' => null
                            
                        ]);
                            
                        }
            }
            
           
        }            

    }

}
?>