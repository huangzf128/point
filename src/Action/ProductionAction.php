<?php

namespace App\Action;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;

/**
 * Action
 */
final class ProductionAction
{
    protected $container;
    private $productPath = 'images/products/';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * 製品のご案内
     */
    public function product(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getQueryParams();
        $category = $params['category'] ?? null;
 
        $productList = $this->getProduct($category ?? null);
        $categoryList = $this->getCategory();
        
        foreach ($categoryList as $key => $row) {
            $categoryList[$key]['disp_category'] = preg_replace('/\(.+?\)/i', '', $row['category']);
        }

        foreach ($productList as $key => $row) {
            $productList[$key]['disp_imgTitle'] = preg_replace('/\(.+?\)/i', '', $row['imgTitle']);
        }

        $res = ['bodyClass' => '', 'menuSelected' => 'product', 'productList' => $productList, 'categoryList' => $categoryList, 'category' => $category ];
        return $this->container->get('view')->render($response, 'product.html', $res);
    }

    public function productSelect(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();
        $selcategory = $parsedBody["selcategory"] ?? null;

        if ($parsedBody["opt"] ?? 0 == 1) {
            $sql = 'UPDATE product SET 
            category = :newcategory,
            updDt = now()
            WHERE category = :category';
            $statement = $this->container->get('PDO')->prepare($sql);
            $statement->bindparam(':newcategory', $parsedBody["newCategory"]);
            $statement->bindparam(':category', $parsedBody["selcategory"]);
            $statement->execute();
            $selcategory = $parsedBody["newCategory"];
        }

        // 製品検索
        $productList = $this->getProduct($selcategory);
        if (!empty($selcategory) && count($productList) === 0) {
            $productList = $this->getProduct(null);
            $selcategory = "";
        }

        $categoryList = $this->getCategory();

        $rstData = ['bodyClass' => '', 'productList' => $productList, 'categoryList' => $categoryList, 'selcategory' => $selcategory];
        $rstData = array_merge($rstData, $this->getCsrfInfo($request));

        return $this->container->get('view')->render($response, 'productList.html', $rstData);
    }

    public function productInsertInit(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        
        $categoryList = $this->getCategory();
        
        $rstData = ['bodyClass' => '', 'categoryList' => $categoryList];
        $rstData = array_merge($rstData, $this->getCsrfInfo($request));

        return $this->container->get('view')->render($response, 'productInsert.html', $rstData);
    }

    /**
     * 製品登録
     */
    public function productInsert(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $img = $_FILES['image']['name'];
        $category = $_POST['category'];
        $imgTitle = $_POST['imgTitle'];
        $description = $_POST['description'];
        $imgTitleZn = $_POST['imgTitleZn'];
        $descriptionZn = $_POST['descriptionZn'];

        $type = array("jpg", "gif", 'png', 'bmp');
        $ext = explode(".", $img);
        $ext = $ext[count($ext) - 1];
        $imgPath =  $this->productPath. date('mdHis'). "_". $img;
        $sql = 'INSERT INTO product (
            category, img, imgTitle, description, imgTitleZn, descriptionZn, delflag, registDt, updDt
        ) VALUES (
            :category, :img, :imgTitle, :description, :imgTitleZn, :descriptionZn, 0, now(), now()
        )';

        // SQL実行準備
        $statement = $this->container->get('PDO')->prepare($sql);
        $statement->bindparam(':category', $category);
        $statement->bindparam(':img', $imgPath);
        $statement->bindparam(':imgTitle', $imgTitle);
        $statement->bindparam(':description', $description);
        $statement->bindparam(':imgTitleZn', $imgTitleZn);
        $statement->bindparam(':descriptionZn', $descriptionZn);
        // 値を渡して実行
        $result = $statement->execute();

        if ($result) {
            // move_uploaded_file($_FILES["image"]["tmp_name"], $imgPath);
            $this->compressImage($_FILES["image"]["tmp_name"], $imgPath, 50);
        }
        return $response->withHeader('Location', '/productSelect')->withStatus(302);
    }

    private function getImagePath($imageName) {
        return $this->productPath. date('mdHis'). "_".$imageName;
    }

    /**
     * 製品更新
     */
    public function productUpdate(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();
        $id = $parsedBody['id'];

        $newImageName = $_FILES['upfile']['name'] ?? '';
        $newimgPath = $this->getImagePath($newImageName);
        $updImgPath = (empty($newImageName))? $parsedBody['img']: $newimgPath;

        $category = $parsedBody['category'];
        $imgTitle = $parsedBody['imgTitle'];
        $description = $parsedBody['description'];
        $imgTitleZn = $parsedBody['imgTitleZn'];
        $descriptionZn = $parsedBody['descriptionZn'];

        $sql = 'UPDATE product SET 
            img = :img,
            category = :category,
            imgTitle = :imgTitle,
            description = :description,
            imgTitleZn = :imgTitleZn,
            descriptionZn = :descriptionZn,
            updDt = now()
            WHERE id = :id';
        $statement = $this->container->get('PDO')->prepare($sql);
        $statement->bindparam(':img', $updImgPath);
        $statement->bindparam(':category', $category);
        $statement->bindparam(':imgTitle', $imgTitle);
        $statement->bindparam(':description', $description);
        $statement->bindparam(':imgTitleZn', $imgTitleZn);
        $statement->bindparam(':descriptionZn', $descriptionZn);
        $statement->bindparam(':id', $id);

        $statement->execute();

        if (!empty($newImageName)) {
            // move_uploaded_file($_FILES["upfile"]["tmp_name"], $newimgPath);
            $this->compressImage($_FILES["upfile"]["tmp_name"], $newimgPath, 50);
            if(file_exists($parsedBody['img'])) {
                unlink($parsedBody['img']);
            }
        }

        $response->getBody()->write(json_encode($this->getCsrfInfo($request)));
        return $response
                  ->withHeader('Content-Type', 'application/json');
    }

    /**
     * 製品削除
     */
    public function productDelete(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();

        $sql = 'UPDATE product SET delflag = 1, updDt = now() WHERE id = :id';
        $statement = $this->container->get('PDO')->prepare($sql);
        $statement->bindparam(':id', $parsedBody['id']);
        $statement->execute();

        // delete file
        if(file_exists($parsedBody['img'])) {
            unlink($parsedBody['img']);
        }

        $response->getBody()->write(json_encode($this->getCsrfInfo($request)));
        return $response
                  ->withHeader('Content-Type', 'application/json');
    }

    private function getCategory() {
        $sql = 'SELECT category FROM product WHERE delflag = 0 GROUP BY category ORDER BY category ';
        $statement = $this->container->get('PDO')->prepare($sql);

        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getProduct($category) {

        $sql = 'SELECT * FROM product WHERE delFlag = 0 ';
        if ($category != null) $sql .= ' and  category = :category';
        $sql .= " ORDER BY imgTitle, category ";
        
        $statement = $this->container->get('PDO')->prepare($sql);
        if ($category != null) $statement->bindparam(':category', $category);
        
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getCsrfInfo($request) {
        $csrf = $this->container->get('csrf');
        $nameKey = $csrf->getTokenNameKey();
        $valueKey = $csrf->getTokenValueKey();
        $name = $request->getAttribute($nameKey);
        $value = $request->getAttribute($valueKey);

        return ['ck' => $nameKey, 'cv' => $valueKey, 'k' => $name, 'v' => $value];
    }

    // Compress image
    function compressImage($source, $destination, $quality) {

        $info = getimagesize($source);
        
        if ($info['mime'] == 'image/jpeg') 
        $image = imagecreatefromjpeg($source);
    
        elseif ($info['mime'] == 'image/gif') 
        $image = imagecreatefromgif($source);
    
        elseif ($info['mime'] == 'image/png') 
        $image = imagecreatefrompng($source);

        $this->rotateImage($source, $image, $info);

        if ($info[0] > 1000) {
            $rate = $info[0] / 1600;
            $image = $this->imageResize($image, $info[0], $info[1], $rate);
        }

        imagejpeg($image, $destination, $quality);
    }

    function rotateImage($img, &$image, &$info) {
        $exif = exif_read_data( $img );
        if ( isset( $exif["Orientation"] ) ) {
            if ( $exif["Orientation"] == 6 ) {

                // photo needs to be rotated
                $image = imagerotate( $image , -90, 0 );
        
                $newWidth = $info[1];
                $newHeight = $info[0];
        
                $info[0] = $newWidth;
                $info[1] = $newHeight;
            }            
        }
    }

    function imageResize($imageResourceId, $width, $height, $rate) {
        $targetWidth = $width / $rate;
        $targetHeight = $height / $rate;

        $targetLayer = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopyresampled($targetLayer, $imageResourceId, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
    
        return $targetLayer;
    }    
  
}
