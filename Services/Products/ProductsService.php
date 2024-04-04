<?php

namespace MelhorEnvio\Services\Products;

use MelhorEnvio\Helpers\DimensionsHelper;
use MelhorEnvio\Models\Product;
use MelhorEnvio\Services\ConfigurationsService;
use MelhorEnvio\Services\WooCommerceBundleProductsService;

class ProductsService {

	const PRODUCT_BUNDLE_TYPE = 'woosb';
	const PRODUCT_COMPOSITE_TYPE = 'composite';

	public $product;

	public static function isCompositeProduct($product): bool
	{
		return $product->get_type() === self::PRODUCT_COMPOSITE_TYPE;
	}

	public static function isBundleProduct($product): bool
	{
		return $product->get_type() === self::PRODUCT_BUNDLE_TYPE;
	}

	public function getValueBase( $products )
	{
		$valueBase = 0;
		foreach ( $products as $product ) {
			if (! empty($product->pricing) &&
				! empty($product->shipping_fee) && 
				( $product->pricing == 'include' || $product->pricing == 'only')
				&& $product->shipping_fee == 'each'
			) {
				$valueBase += wc_get_product($product->id)->get_price();
			}
		}
		return $valueBase;
	}

	/**
	 * @param int      $postId
	 * @param null|int $quantity
	 * @return object
	 */
	public function getProduct( int $postId, int $quantity = null ) {
		$product = wc_get_product( $postId );

		if ( empty( $quantity ) ) {
			$quantity = 1;
		}

		return $this->normalize( $product, $product->get_price(), $quantity );
	}

	/**
	 * Function to obtain the insurance value of one or more products.
	 *
	 * @param array|object $products
	 * @return float
	 */
	public function getInsuranceValue( $products, $valueBase = 0 ) {
		$insuranceValue = $valueBase;
		foreach ( $products as $product ) {
			$product = (object) $product;
			if ( ! empty( $product->unitary_value ) ) {
				$insuranceValue += $product->unitary_value * $product->quantity;
			}
		}

		if ( $insuranceValue == 0 ) {
			$insuranceValue = floatval( 1 );
		}

		return $insuranceValue;
	}

	/**
	 * function to remove the price field from
	 * the product to perform the quote without insurance value
	 *
	 * @param array $products
	 * @return array
	 */
	public function removePrice( $products ) {
		$response = array();
		foreach ( $products as $product ) {
			$response[] = (object) array(
				'id'            => $product->id,
				'name'          => $product->name,
				'quantity'      => $product->quantity,
				'unitary_value' => $product->unitary_value,
				'weight'        => $product->weight,
				'width'         => $product->width,
				'height'        => $product->height,
				'length'        => $product->length,
			);
		}

		return $response;
	}

	/**
	 * Function to filter products to api Melhor Envio.
	 *
	 * @param array $products
	 * @return array
	 */
	public function filter( $data ) {
		$products = array();
		foreach ( $data as $key => $item ) {
			if ( ! empty( $item->shipping_fee ) && $item->shipping_fee == 'whole' ) {
				$item->components = [];
			}

			if ( ! empty( $item->shipping_fee ) && $item->shipping_fee == 'each' && ! empty( $item->components ) ) {
				foreach ($item->components as $component) {
					$products[] = $component;
				}
				continue;
			}

			if ( $this->isObjectProduct( $item ) ) {
				$data       = $item->get_data();
				$product    = $item;
				$products[$key] = $this->normalize(
					$product,
					$product->get_price(),
					$item['quantity']
				);
				continue;
			}

			if ( ! empty( $item->name ) && ! empty( $item->id ) ) {
				$products[$key] = $item;
				continue;
			}

			if ( ! empty( $item['name'] ) && ! empty( $item['id'] ) ) {
				$products[$key] = (object) $item;
				continue;
			}

			$product    = $item['data'];
			$products[$key] = $this->normalize(
				$product,
				$product->get_price(),
				$item['quantity']
			);
		}

		return $products;
	}

	/**
	 * @param object $product
	 * @return bool
	 */
	private function isObjectProduct( $item ) {
		return (
			! is_array( $item ) &&
			(
				get_class( $item ) == WooCommerceBundleProductsService::OBJECT_PRODUCT_SIMPLE ||
				get_class( $item ) == WooCommerceBundleProductsService::OBJECT_WOOCOMMERCE_BUNDLE
			)
		);
	}

	public function getDataByProductCart( $productCart , $items): Product
	{
		$data = self::normalize(
			$productCart['data'],
			$productCart['data']->get_price(),
			$productCart['quantity']
		);

		if (isset($productCart['wooco_parent_id'])){
			$data->parentId = $productCart['wooco_parent_id'];
		}

		if (isset($productCart['woosb_parent_id'])){
			$data->parentId = $productCart['woosb_parent_id'];
		}

		return $data;
	}

	public function getDataByProductOrder( $productOrder, $items)
	{
		$data = self::normalize(
			$productOrder->get_product(),
			$productOrder->get_total(),
			$productOrder->get_quantity()
		);

		if ($productOrder->get_meta('wooco_parent_id', true) !== null){
			$data->parentId = $productOrder->get_meta('wooco_parent_id', true);
		}

		if ($productOrder->get_meta('woosb_parent_id', true) !== null){
			$data->parentId = $productOrder->get_meta('woosb_parent_id', true);
		}

		return $data;
	}
	/**
	 * @param $product
	 * @param $price
	 * @param int $quantity
	 * @return Product
	 */
	public function normalize($product, $price, $quantity = 1): Product
	{
		$this->setDimensions( $product );

		$data = new Product();

		$data->id = $product->get_id();
		$data->name = $product->get_name();
		$data->width = DimensionsHelper::convertUnitDimensionToCentimeter( $product->get_width() );
		$data->height = DimensionsHelper::convertUnitDimensionToCentimeter( $product->get_height() );
		$data->length = DimensionsHelper::convertUnitDimensionToCentimeter( $product->get_length() );
		$data->weight = DimensionsHelper::convertWeightUnit( $product->get_weight() );
		$data->unitary_value = $price;
		$data->insurance_value = $price;
		$data->quantity = $quantity;
		$data->type = $product->get_type();
		$data->is_virtual = $product->get_virtual();

		return $data;
	}

	/**
	 * function to check if prouct has all dimensions.
	 *
	 * @param object $product
	 */
	private function setDimensions( $product ) {
		$dimensionDefault = ( new ConfigurationsService() )->getDimensionDefault();

		if ( empty( $product->get_width() ) ) {
			$product->set_width( $dimensionDefault['width'] );
		}

		if ( empty( $product->get_height() ) ) {
			$product->set_height( $dimensionDefault['height'] );
		}

		if ( empty( $product->get_length() ) ) {
			$product->set_length( $dimensionDefault['length'] );
		}

		if ( empty( $product->get_weight() ) ) {
			$product->set_weight( $dimensionDefault['weight'] );
		}
	}

	/**
	 * function to return a label with the name of products.
	 *
	 * @param array $products
	 * @return string
	 */
	public function createLabelTitleProducts( $products ) {
		$title = '';
		foreach ( $products as $id => $product ) {
			if ( ! empty( $product['data']->get_name() ) ) {
				$title = $title . sprintf(
					"<a href='%s'>%s</a>, ",
					get_edit_post_link( $id ),
					$product['data']->get_name()
				);
			}
		}

		if ( ! empty( $title ) ) {
			$title = substr( $title, 0, -2 );
		}

		return 'Produto(s): ' . $title;
	}
}
