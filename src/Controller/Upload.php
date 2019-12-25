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
        
        $providers = ['seakoo'];

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
        var_dump($newSheetData);  
        // $this->insertProducts($newSheetData);
        // $this->insertCategories($newSheetData);
        $this->insertProductCategory($newSheetData);
        

       
    
    }

    
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
        );
        return strtr($string, $converter);
    }
     
    function makeUrlCode($str){
        return trim(preg_replace('~[^-a-z0-9_]+~u', '-', strtolower($this->translit($str))), "-");
    }

    public function insertProducts($products){

        $pdo = \App\Service\DB::get();
        $stmt = $pdo->prepare("
            INSERT INTO 
            `products`(
                `name`,
                `slug`,
                `details`,
                `price`,
                `description`,
                `image`               
            ) VALUES (
                :name,
                :slug,
                :details,
                :price,
                :description,
                :image
            )
        ");
        
        $stmt2 = $pdo->prepare("
            SELECT
                `name`
            FROM
                `products`
        "); 

        foreach($products as $product){
            $stmt2->execute();
            $prods = $stmt2->fetchAll();
            $newProds = []; 
            foreach($prods as $prod){
                $newProds[] = $prod['name'];
            }

            if(!in_array($product['A'], $newProds)){
                $stmt->execute([
                    ':name' => $product['A'],
                    ':slug' => $this->makeUrlCode($product['A']),
                    ':details' => $product['B'],   
                    ':price' => $product['E'],
                    ':description' => $product['D'],
                    ':image' => 'products/dummy/'.$product["G"]               
                ]);

            }
        }
    }

     public function insertProductCategory($products){

            $pdo = \App\Service\DB::get();   
            $stmt = $pdo->prepare("
                SELECT
                    `id`
                FROM
                    `products`
                WHERE `name` = :name
            ");   
            
            $stmt2 = $pdo->prepare("
                INSERT INTO 
                    `category_product`(
                        `product_id`,
                        `category_id`
                                    
                    ) VALUES (
                        :prod_id,
                        :categor_id                       
                    )
            "); 
            
            $stmt3 = $pdo->prepare("
                SELECT
                    `id`
                FROM
                    `category`
                WHERE `name` = :name
            ");  
                

            
            foreach($products as $product){
               
                $stmt->execute([
                    ':name' => $product['A']
                ]);
                $stmt3->execute([
                    ':name' => $product['G']
                ]);
                $categor_id = $stmt3->fetchAll();
                $prod_id = $stmt->fetchAll(); 

               

                if(isset($prod_id[0]['id']) && isset($categor_id[0]['id'])){
                    $stmt2->execute([
                        ':prod_id' => $prod_id[0]['id'],
                        ':categor_id' => $categor_id[0]['id']
                    ]);

                }                                            
            } 

        }

    public function insertCategories($products){

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
        $stmt1 = $pdo->prepare("
                SELECT
                    `id`
                FROM
                    `category`
                WHERE `name` = :name
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
            if(!in_array($product['G'], $newCategors) || !in_array($product['F'], $newCategors)){

                $stmt1->execute([
                    ':name' => $product['F']
                ]);
                $id = $stmt1->fetchAll();  
                    
                   if(isset($id[0]['id'])){  
                        $stmt->execute([
                            ':name' => $product['G'],
                            ':slug' => $this->makeUrlCode($product['G']),
                            ':parent_id' => $id[0]['id']
                            
                        ]);                  
                                    
                    }
                    else{
                        $stmt->execute([
                            ':name' => $product['F'],
                            ':slug' => $this->makeUrlCode($product['F']),
                            ':parent_id' => null
                            
                        ]);
                            
                        }
            }
           
        }            

    }

}
?>