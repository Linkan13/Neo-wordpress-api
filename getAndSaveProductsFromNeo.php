<?php
	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "mike";
	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}else{
		echo "Connected Successfully.<br>";
	}

	// If get token then call the insert products
	$GetToken = getToken();
	if($GetToken != ''){
		//add_Categories_SubCategories(getAllCategoriesApi($GetToken), $conn);
		insertAllProducts(getAllProducts($GetToken), $conn);
	}

	// Get token to use Api's
	function getToken(){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://apis.bartolomeconsultores.com/pedidosweb/gettoken.php");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		$data = array(
			'Centro' => '457',
			'APIKey' => 'UmQMhZWAdM+FSAWafXXSVsV/Wqdba8WsTTmg56Gr5YI='
		);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$contents = curl_exec($ch);
		curl_close($ch);
		$contents = json_decode($contents);
		return $contents->token;
	}
	
	// Get all products from Api
	function getAllProducts($GetToken){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://apis.bartolomeconsultores.com/pedidosweb/verarticulos2.php");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		$data = array(
			'token' => $GetToken
		);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$contents = curl_exec($ch);
		curl_close($ch);
		$contents = json_decode($contents);
		return $contents->articulos;
	}

	// Image meta data for media in database
	function imageArrayForUpload($imageUrl){
		$array = array (
			'width' => 2560,
			'height' => 1280,
			'file' => $imageUrl,
			'sizes' => 
			array (
			  'medium' => 
			  array (
				'file' => $imageUrl,
				'width' => 300,
				'height' => 150,
				'mime-type' => 'image/jpeg',
			  ),
			  'large' => 
			  array (
				'file' => $imageUrl,
				'width' => 1024,
				'height' => 512,
				'mime-type' => 'image/jpeg',
			  ),
			  'thumbnail' => 
			  array (
				'file' => $imageUrl,
				'width' => 150,
				'height' => 150,
				'mime-type' => 'image/jpeg',
			  ),
			  'medium_large' => 
			  array (
				'file' => $imageUrl,
				'width' => 768,
				'height' => 384,
				'mime-type' => 'image/jpeg',
			  ),
			  '1536x1536' => 
			  array (
				'file' => $imageUrl,
				'width' => 1536,
				'height' => 768,
				'mime-type' => 'image/jpeg',
			  ),
			  '2048x2048' => 
			  array (
				'file' => $imageUrl,
				'width' => 2048,
				'height' => 1024,
				'mime-type' => 'image/jpeg',
			  ),
			  'post-thumbnail' => 
			  array (
				'file' => $imageUrl,
				'width' => 1568,
				'height' => 784,
				'mime-type' => 'image/jpeg',
			  ),
			  'woocommerce_thumbnail' => 
			  array (
				'file' => $imageUrl,
				'width' => 450,
				'height' => 450,
				'mime-type' => 'image/jpeg',
				'uncropped' => false,
			  ),
			  'woocommerce_single' => 
			  array (
				'file' => $imageUrl,
				'width' => 600,
				'height' => 300,
				'mime-type' => 'image/jpeg',
			  ),
			  'woocommerce_gallery_thumbnail' => 
			  array (
				'file' => $imageUrl,
				'width' => 100,
				'height' => 100,
				'mime-type' => 'image/jpeg',
			  ),
			  'shop_catalog' => 
			  array (
				'file' => $imageUrl,
				'width' => 450,
				'height' => 450,
				'mime-type' => 'image/jpeg',
			  ),
			  'shop_single' => 
			  array (
				'file' => $imageUrl,
				'width' => 600,
				'height' => 300,
				'mime-type' => 'image/jpeg',
			  ),
			  'shop_thumbnail' => 
			  array (
				'file' => $imageUrl,
				'width' => 100,
				'height' => 100,
				'mime-type' => 'image/jpeg',
			  ),
			),
			'image_meta' => 
			array (
			  'aperture' => '0',
			  'credit' => '',
			  'camera' => '',
			  'caption' => '',
			  'created_timestamp' => '0',
			  'copyright' => '',
			  'focal_length' => '0',
			  'iso' => '0',
			  'shutter_speed' => '0',
			  'title' => '',
			  'orientation' => '0',
			  'keywords' => 
			  array (
			  ),
			),
			'original_image' => $imageUrl,
		);
		return serialize($array);
	}

	// get all products
	function insertAllProducts($product_array, $conn){
		echo '<pre>';
		//print_r($product_array);
		if(count($product_array) > 0){
			$sql = "SELECT * FROM wp_postmeta WHERE meta_key = 'product_id_Neo'";
			$result = $conn->query($sql);
			$arrayProductIds = array();
			if ($result->num_rows > 0) {
				while ($row = $result -> fetch_row()) {
					$arrayOfId_Code = array('id' => $row[1], 'code' => $row[3]);
					array_push($arrayProductIds, $arrayOfId_Code);
				}
			}
			for ($i=0; $i < count($product_array); $i++) {
				$Exist_or_Not = 0;
				$product_Query_Id = 0;
				if(count($arrayProductIds) > 0){
					foreach($arrayProductIds as $singleArray){
						if($product_array[$i]->Codigo == $singleArray['code']){
							$Exist_or_Not = 1;
							$product_Query_Id = $singleArray['id'];
							break;
						}
					}
				}
	
				if($Exist_or_Not == 1) {
					//updateExistProduct($product_array[$i], $product_Query_Id, $conn);
					// output data of each row
					echo "Already existed.<br>";
				} else {
					echo "Not existed.<br>";
					// insert New Product in database
					insert_new_product($product_array[$i], $arrayProductIds, $conn);
				}
			}
		}else{
			echo 'No products found.<br>';
		}
	}

	// Update product if already exist
	function updateExistProduct($product, $product_Id, $conn){
		// wp_term_relationships
		$image = $product->Imagen;
		$productStatus = 'draft';
		if($product->Activo == 1){
			$productStatus = 'publish';
		}
		$sql = "UPDATE wp_posts SET post_title = '".$product->Nombre."', post_content= '".$product->Nombre."', post_status= '".$productStatus."' WHERE ID = '".$product_Id."'";
		if (mysqli_query($conn, $sql)) {
			//uploadImage($image, $product_Id, $conn);
			addWooCommerceProductMeta($product_Id, $product, $conn, 'update');
			addProductDetailsMeta($product_Id, $product, $conn, 'update');
			echo "Product updated Successfully.<br>";
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}

	function checkNameExistOrNot($product_slug, $conn){
		$productSlugExist = true;
		$count = 1;
		while($productSlugExist === true){
			$sql = "SELECT * FROM wp_posts WHERE post_name = '".$product_slug."'";
			$result = $conn->query($sql);
			if ($result->num_rows > 0) {
				$product_slug = $product_slug.'-'.$count;					
			}else{
				$productSlugExist = false;
			}
			$count++;
		}
		return $product_slug;
	}

	// Insert single product
	function insert_new_product($product, $arrayProductIds, $conn){
		$image = $product->Imagen;
		$date = date('Y-m-d H:i:s');
		$productStatus = 'draft';
		if($product->Activo == 1){
			$productStatus = 'publish';
		}
		$productName = $product->Nombre;
		$product_slug = str_replace(" ","-", $productName);
		$product_slug = checkNameExistOrNot($product_slug, $conn);
		echo $check_If_Variant_Or_Not = check_If_Variant_Or_Not($product);
		if($check_If_Variant_Or_Not === 1){
			$check_If_Variant_Or_Not = 'product_variation';
			$product_parent = get_Parent_Product_Id($product, $conn);
		}else{
			$check_If_Variant_Or_Not = 'product';
			$product_parent = 0;	
		}
		echo $product_parent;
		$sql = "INSERT INTO wp_posts (post_parent, post_name, post_title, post_content, post_status, post_type, post_date, post_date_gmt) VALUES ('".$product_parent."', '".$product_slug."', '".$product->Nombre."', '".$product->Nombre."', '".$productStatus."', '".$check_If_Variant_Or_Not."', '".$date."', '".$date."' )";
		if (mysqli_query($conn, $sql)) {
			$product_Id = $conn->insert_id;
			//uploadImage($image, $product_Id, $conn);
			//addWooCommerceProductMeta($product_Id, $product, $conn, 'create');
			addProductDetailsMeta($product_Id, $product, $conn, 'create');
			//create_Relation_Product_category($product_Id, $product, $conn);
			echo "New Product Created Successfully.<br>";
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}

	// Create relation with product and category
	function create_Relation_Product_category($product_Id, $product, $conn){
		$cat_array = array($product->Grupo, $product->Familia, $product->Subfamilia);
		foreach($cat_array as $single_cat){
			$sql = "SELECT * FROM wp_postmeta WHERE meta_key = 'category_Id_Neo' and meta_value = '".$single_cat."' limit 1";
			$result = $conn->query($sql);
			$arrayCategoryIds = array();
			if ($result->num_rows > 0) {
				while ($row = $result -> fetch_row()) {
					$sql = "INSERT INTO wp_term_relationships (object_id, term_taxonomy_id, term_order) VALUES ('".$product_Id."', '".$row[1]."', 0 )";
					if (mysqli_query($conn, $sql)) {
						echo 'success';
					}
				}
			}else {
				echo "Error: " . $sql . "<br>" . mysqli_error($conn);
			}
		}
	}
	
	// Update product image , upload image to media in database
	function uploadImage($image, $Product_id, $conn){
		$imagearray = explode(".",$image);
		$date = date('Y-m-d H:i:s');
		$imageUrl = 'http://localhost:8080/mike/ProductImages'.'/'.$image;
		$imageUrl_WithoutHost = '/ProductImages'.'/'.$image;
		$imageType_Product = 'image/'.$imagearray[1];
 
		$sql = "INSERT INTO wp_posts (post_parent, post_title, post_excerpt, post_status, post_name, post_type, post_mime_type, post_date, post_date_gmt, guid) VALUES ('".$Product_id."', '".$imagearray[0]."', '".$imagearray[0]."', 'inherit', '".$image."', 'attachment', '".$imageType_Product."', '".$date."', '".$date."', '".$imageUrl."' )";
		if (mysqli_query($conn, $sql)) {
			$last_id_Image = $conn->insert_id;
			$sql = "INSERT INTO wp_postmeta (post_id, meta_key, meta_value ) VALUES ('".$last_id_Image."', '_wp_attached_file', '".$imageUrl_WithoutHost."' )";
			if (mysqli_query($conn, $sql)) {
				$metaDataArrayForUImage = imageArrayForUpload($image);
				$sql = "INSERT INTO wp_postmeta (post_id, meta_key, meta_value ) VALUES ('".$last_id_Image."', '_wp_attachment_metadata', '".$metaDataArrayForUImage."' )";
				if (mysqli_query($conn, $sql)) {
					$sql = "INSERT INTO wp_postmeta (post_id, meta_key, meta_value ) VALUES ('".$Product_id."', '_thumbnail_id', '".$last_id_Image."' )";
					if (mysqli_query($conn, $sql)) {
						echo "product image uploaded Successfully.<br>";
					} else {
						echo "Error: " . $sql . "<br>" . mysqli_error($conn);
					}
				} else {
					echo "Error: " . $sql . "<br>" . mysqli_error($conn);
				}
			} else {
				echo "Error: " . $sql . "<br>" . mysqli_error($conn);
			}
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}

	function addWooCommerceProductMeta($productId, $product, $conn, $type){
		$min_Price = $product->Precios[0]->BaseImponible;
		$max_Price = $product->Precios[0]->PrecioConsumoTot;
		$stock = $product->StockActual;
		$stock = explode(".",$stock);
		$stock_Status = 'instock';
		$Stock = $stock[0];
		if($stock[0] < 0){
			$stock_Status = 'outofstock';
			$Stock = -1;
		}
		if($stock[0] == 0){
			$stock_Status = 'outofstock';
		}
		$sql = "INSERT INTO wp_wc_product_meta_lookup (product_id, sku, virtual, downloadable, min_price, max_price, onsale, stock_quantity, stock_status, rating_count, average_rating, total_sales, tax_status, tax_class ) VALUES ('".$productId."', '', '0', '0', '".$min_Price."', '".$max_Price."', '0', $stock[0], 'instock', '0', '0', '0', 'taxable', '' )";
		if($type == 'update'){
			$sql = "UPDATE wp_wc_product_meta_lookup SET min_price = '".$min_Price."', max_price = '".$max_Price."', stock_quantity = '".$Stock."', stock_status = '".$stock_Status."' WHERE ID = '".$productId."'";		
		}
		if (mysqli_query($conn, $sql)) {
			echo "product Meta Added Successfully.<br>";
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}

	function addProductDetailsMeta($product_Id, $product, $conn, $type){
		$min_Price = $product->Precios[0]->BaseImponible;
		$max_Price = $product->Precios[0]->PrecioConsumoTot;
		if($max_Price == $min_Price){
			$min_Price = 0;
		}
		$stock = $product->StockActual;
		$stock = explode(".",$stock);
		$productIdNeo = $product->Codigo;
		$stock_Status = 'instock';
		$Stock = $stock[0];
		if($stock[0] < 0){
			$stock_Status = 'outofstock';
			$Stock = -1;
		}
		if($stock[0] == 0){
			$stock_Status = 'outofstock';
		}

		$product_Variants = get_Product_Variants($product);

		$arrayInsertOptions = array(
			'_edit_lock' 			=> '1633681299:1',
			'_edit_last' 			=> 1,
			'total_sales' 			=> 0,
			'_tax_status' 			=> 'taxable',
			'_tax_class' 			=> '',
			'_manage_stock' 		=> 'yes',
			'_backorders' 			=> 'no',
			'_sold_individually' 	=> 'no',
			'_virtual' 				=> 'no',
			'_downloadable' 		=> 'no',
			'_download_limit' 		=> -1,
			'_download_expiry' 		=> -1,
			'_stock' 				=> $Stock,
			'_stock_status' 		=> $stock_Status,
			'_wc_average_rating' 	=> 0,
			'_wc_review_count' 		=> 0,
			'_product_version' 		=> '5.7.1',
			'_sku' 					=> $productIdNeo,
			'product_id_Neo' 		=> $productIdNeo,
			'_regular_price' 		=> $max_Price,
			'_sale_price' 			=> $min_Price,
			'_price' 				=> $min_Price,
			'_product_attributes'	=> $product_Variants
		);
		
		foreach($arrayInsertOptions as $key => $val) {
			$sql = "INSERT INTO wp_postmeta (post_id, meta_key, meta_value ) VALUES ('".$product_Id."', '".$key."', '".$val."' )";
			if($type == 'update'){
				$sql = "UPDATE wp_postmeta SET meta_key = '".$key."', meta_value= '".$val."' WHERE ID = '".$product_Id."'";
			}
			if (mysqli_query($conn, $sql)) {
				echo "product meta added Successfully.<br>";
			} else {
				echo "Error: " . $sql . "<br>" . mysqli_error($conn);
			}
		}
	}

	// get all categories from Api
	function getAllCategoriesApi($GetToken){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://apis.bartolomeconsultores.com/pedidosweb/veragrupaciones.php");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		$data = array(
			'token' => $GetToken
		);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$contents = curl_exec($ch);
		curl_close($ch);
		$contents = json_decode($contents);
		return $contents->grupos;
	}

	function check_Category_Is_Existed($arrayCategoryIds, $Check_Category_Code, $conn){
		$Exist_or_Not = 0;
		$category_Query_Id = 0;
		if(count($arrayCategoryIds) > 0){
			foreach($arrayCategoryIds as $single){
				if($Check_Category_Code == $single['code']){
					$Exist_or_Not = 1;
					$category_Query_Id = $single['id'];
					break;
				}
			}
		}
		if($Exist_or_Not == 0){
			return 0;
		}else{
			return $category_Query_Id;
		}
	}

	// Check if category exist or not 
	function add_Categories_SubCategories($arrayOfCategories, $conn){
		if(count($arrayOfCategories) > 0){
			$sql = "SELECT * FROM wp_postmeta WHERE meta_key = 'category_Id_Neo'";
			$result = $conn->query($sql);
			$arrayCategoryIds = array();
			if ($result->num_rows > 0) {
				while ($row = $result -> fetch_row()) {
					$arrayOfId_Code = array('id' => $row[1], 'code' => $row[3]);
					array_push($arrayCategoryIds, $arrayOfId_Code);
				}
			}
	
			for ($main_Category=0; $main_Category < count($arrayOfCategories); $main_Category++) {
				$getCategoryId = check_Category_Is_Existed($arrayCategoryIds, $arrayOfCategories[$main_Category]->CodGrupo, $conn);
				if($getCategoryId == 0){
					$parent_Cat_ID = insert_Sub_Categories($arrayOfCategories[$main_Category]->NomGrupo, $conn, 0, 'Category', $arrayOfCategories[$main_Category]->CodGrupo, 'create', $getCategoryId);
				}else{
					$parent_Cat_ID = insert_Sub_Categories($arrayOfCategories[$main_Category]->NomGrupo, $conn, 0, 'Category', $arrayOfCategories[$main_Category]->CodGrupo, 'update', $getCategoryId);
				}
	
				$NewCategories = $arrayOfCategories[$main_Category]->Familias;
				if(count($NewCategories) > 0){
					for ($sub_category=0; $sub_category < count($NewCategories); $sub_category++) {
						$getCategoryId = check_Category_Is_Existed($arrayCategoryIds, $arrayOfCategories[$main_Category]->CodGrupo, $conn);
						if($getCategoryId == 0){
							$Sub_Parent_Cat_ID = insert_Sub_Categories($NewCategories[$sub_category]->NomFamilia, $conn, $parent_Cat_ID, 'SubCategory', $NewCategories[$sub_category]->CodFamilia, 'create', $getCategoryId);
						}else{
							$Sub_Parent_Cat_ID = insert_Sub_Categories($NewCategories[$sub_category]->NomFamilia, $conn, $parent_Cat_ID, 'SubCategory', $NewCategories[$sub_category]->CodFamilia, 'update', $getCategoryId);
						}
							$newSubcategory = $NewCategories[$sub_category]->Subfamilias;
							if(count($newSubcategory) > 0){
								for ($new_sub_category=0; $new_sub_category < count($newSubcategory); $new_sub_category++) {
									$getCategoryId = check_Category_Is_Existed($arrayCategoryIds, $arrayOfCategories[$main_Category]->CodGrupo, $conn);
									if($getCategoryId == 0){
										insert_Sub_Categories($newSubcategory[$new_sub_category]->NomSubfamilia, $conn, $Sub_Parent_Cat_ID, 'SubCategory', $newSubcategory[$new_sub_category]->CodSubfamilia, 'create', $getCategoryId);
									}else{
										insert_Sub_Categories($newSubcategory[$new_sub_category]->NomSubfamilia, $conn, $Sub_Parent_Cat_ID, 'SubCategory', $newSubcategory[$new_sub_category]->CodSubfamilia, 'update', $getCategoryId);
									}
								}
							}
					}
				}			
			}
		}else{
			echo 'No category found.<br>';
		}
	}

	// Save or update category in database
	function insert_Sub_Categories($categoryName, $conn, $parent_Cat_ID, $Type, $Category_Code, $TypeOfSql, $category_Query_Id){
		$category_Id = 0;
		if($TypeOfSql == 'create'){
			$sql = "INSERT INTO wp_terms ( name, slug, term_group ) VALUES ('".$categoryName."', '".$categoryName."', 0 )";
			if (mysqli_query($conn, $sql)) {
				$category_Id = $conn->insert_id;
				if($Type == 'Category'){
					$parent_Cat_ID = 0;
				}
				$sql = "INSERT INTO wp_term_taxonomy ( term_id, taxonomy, parent, count ) VALUES ('".$category_Id."', 'product_cat', '".$parent_Cat_ID."', 0 )";
				if (mysqli_query($conn, $sql)) {
					$sql = "INSERT INTO wp_postmeta (post_id, meta_key, meta_value ) VALUES ('".$category_Id."', 'category_Id_Neo', '".$Category_Code."' )";
					if (mysqli_query($conn, $sql)) {
						echo "Created";
					}else {
						echo "Error: " . $sql . "<br>" . mysqli_error($conn);
					}
				}else {
					echo "Error: " . $sql . "<br>" . mysqli_error($conn);
				}
			}else {
				echo "Error: " . $sql . "<br>" . mysqli_error($conn);
			}
		}else{
			$sql = "UPDATE wp_terms SET name = '".$categoryName."', term_group = 0 WHERE term_id = '".$category_Query_Id."'";
			if (mysqli_query($conn, $sql)) {
				$category_Id = $category_Query_Id;
				if($Type == 'Category'){
					$parent_Cat_ID = 0;
				}
				// $sql = "UPDATE wp_term_taxonomy SET parent = '".$parent_Cat_ID."', count= 0 WHERE term_taxonomy_id = '".$category_Id."'";
				// if (mysqli_query($conn, $sql)) {
				// 	echo "Update";
				// }else {
				// 	echo "Error: " . $sql . "<br>" . mysqli_error($conn);
				// }
			}
		}		
		return $category_Id;		
	}

	function get_Product_Variants($product){
		// Get inventory from wordpress
		// Get inventory from Api
		// 10 Api
		// 10 wordpress - 2
		// 8 Wordpress

		// after cron
		// 5 Api
		// 8 Wordpress

		// total - 10
		//  10-5 + 2
		// 3

		// Another Case
		// 10 Total
		// 10 buy api - 5 buy api

		
		$color_Attr_Array = array(
            'name' => 'Color',
            'value' => 'red | black',
            'position' => 0,
            'is_visible' => 1,
            'is_variation' => 1,
            'is_taxonomy' => 0
        );
		
		$size_Attr_Array = array(
			'name' 			=> 'Size',
			'value' 			=> 'XS | S | L | XL | XXL | M',
			'position' 		=> 0,
			'is_visible' 		=> 1,
			'is_variation' 	=> 1,
			'is_taxonomy' 	=> 0
		);
		
		return $product_Attr_Array = serialize(array( 'color' => $color_Attr_Array, 'size' => $size_Attr_Array ));

	}

	function check_If_Variant_Or_Not($product){
		if($product->CodTalla == ''){
			return 0;
		}else{
			return 1;
		}

	}

	function get_Parent_Product_Id($product, $conn){
		$product_Query_Id = 0;
		$Code = $product->Codigo;
		$removedCode = $product->Color.$product->CodTalla;
		$Code = str_replace($removedCode, "", $Code);

		$sql = "SELECT * FROM wp_postmeta WHERE meta_key = 'product_id_Neo' And meta_value = '".$Code."' limit 1";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			$row = $result -> fetch_row();
			$product_Query_Id = $row[1];
		}

		return $product_Query_Id;
	}