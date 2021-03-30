<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\ProductImage;
use App\Models\Variant;
use Illuminate\Http\Request;
use App\Http\Requests\ProductStore;
use App\Http\Requests\ProductUpdate;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index( Request $request )
    {
        $variants = Variant::orderBy('title', 'asc')->get();
        $productVariants = ProductVariant::orderBy('variant', 'asc')->get();
        foreach( $variants as $variant ){
            $productVariantArr = [];
            foreach( $productVariants as $productVariant ){
                if( $productVariant->variant_id == $variant->id ) {
                    $productVariantArr[] = $productVariant;
                }
            }
            $variant->productVariants = $productVariantArr;
        }
        $products = Product::all();
        $productCollection = [];
        foreach( $products as $product ) {
            $productVariantPrices = ProductVariantPrice::where('product_id', $product->id )->get();
            $productVariants = ProductVariant::where('product_id', $product->id )->get();
            $productVariantPriceArr = [];
            foreach( $productVariantPrices as $productVariantPrice ) {
                $variantText = '';
                foreach( $productVariants as $productVariant ) {
                    if( $productVariant->id = $productVariantPrice->product_variant_one ){
                        $variantText .= $productVariant->variant . '/';
                    } else if ( $productVariant->id = $productVariantPrice->product_variant_two ) {
                        $variantText .= $productVariant->variant . '/';
                    } else if ( $productVariant->id = $productVariantPrice->product_variant_three ) {
                        $variantText .= $productVariant->variant;
                    }
                }
                $productVariantPrice->variantText = $variantText;
                $productVariantPriceArr[] = $productVariantPrice;
            }
            $product->productVariantPrices = $productVariantPriceArr;
            $inputDateTime              = date($product->created_at);              // returns Saturday, January 30 10 02:06:34
            $convertInTimestamp         = strtotime($inputDateTime);
            $day                        = date('j', $convertInTimestamp);
            $month                      = date('F', $convertInTimestamp);
            $year                       = date('Y', $convertInTimestamp);
            $product->day     = $day;
            $product->month   = $month;
            $product->year    = $year;
            $productCollection[] = $product;
        }

        $filteredProducts = [];
        $filterProductIds = [];

        $isFilter = false;

        if ($request->has('title') && $request['title'] != null) {
            $title = $request['title'];
            foreach( $productCollection as $product ){
                if( strpos($product->title, $title) ){
                    if( !in_array($product->id, $filterProductIds) ){
                        $filterProductIds[] = $product->id;
                        $filteredProducts[] = $product;
                    }
                    
                }
            }
            $isFilter = true;
        }

        if ($request->has('variant') && $request['variant'] != null) {
            $variant = $request['variant'];
            foreach( $productCollection as $product ){
                if( count( $product->productVariantPrices ) > 0 ) {
                    foreach( $product->productVariantPrices as $productVariantPrice ){
                        if( $productVariantPrice->product_variant_one == $variant || $productVariantPrice->product_variant_two == $variant || $productVariantPrice->product_variant_three == $variant ){
                            if( !in_array($product->id, $filterProductIds) ){
                                $filterProductIds[] = $product->id;
                                $filteredProducts[] = $product;
                            }
                            
                        }
                    }
                }
            }
            $isFilter = true;
        }
        
        if (($request->has('price_from') && $request['price_from'] != null) && ($request->has('price_to') && $request['price_to'] != null) ) {
            $price_from = $request['price_from'];
            $price_to = $request['price_to'];
            foreach( $productCollection as $product ){
                foreach( $product->productVariantPrices as $productVariantPrice ) {
                    if( $productVariantPrice->price >= $price_from && $productVariantPrice->price <= $price_to ) {
                        if( !in_array($product->id, $filterProductIds) ){
                            $filteredProducts[] = $product;
                            $filterProductIds[] = $product->id;
                        }
                    }
                }
            }
            $isFilter = true;
        }

        if ($request->has('date') && $request['date'] != null) {
            $date = strtotime($request['date']);
            foreach( $productCollection as $product ){
                $strtotime = strtotime($product->created_at);
                $created_at = date('Y-m-d', $strtotime);
                if( strtotime($created_at) == $date ){
                    if( !in_array($product->id, $filterProductIds) ){
                        $filterProductIds[] = $product->id;
                        $filteredProducts[] = $product;
                    }
                }
            }
            $isFilter = true;
        }
        
        if( $isFilter ) {
            $products = $this->paginate($filteredProducts);
        } else {
            $products = $this->paginate($productCollection);
        }

        return view('products.index', [
            'products' => $products,
            'variants' => $variants,
        ]);
    }

    public function paginate($items, $perPage = 1, $page = null)
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, ['path' => request()->url(), 'query' => request()->query()]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ProductStore $request)
    {
        $response = [];
        $status = 200;
        try {
            $product = Product::create([
                'title'         => $request->title,
                'sku'           => $request->sku,
                'description'   => $request->description,
            ]);

            $images = $request->product_image;
            foreach( $images as $image ){
                ProductImage::where('id', $image['id'])->update([
                    'product_id' => $product->id,
                ]);
            }

            $productVariants = [];

            $product_variants = $request->product_variant;
            foreach( $product_variants as $product_variant ){
                foreach( $product_variant['tags'] as $tag ) {
                    $productVariant = ProductVariant::create([
                        'variant' => $tag,
                        'variant_id' => $product_variant['option'],
                        'product_id' => $product->id,
                    ]);
                    $productVariants[] = [
                        'product_variant_id' => $productVariant->id,
                        'variant' => $tag,
                        'variant_id' => $product_variant['option'],
                        'product_id' => $product->id,
                    ];
                }
            }

            $product_variant_prices = $request->product_variant_prices;

            foreach( $product_variant_prices as $product_variant_price ){
                $titles = preg_split('@/@', $product_variant_price['title'], NULL, PREG_SPLIT_NO_EMPTY);
                $product_variant_ids = [];
                foreach( $titles as $title ) {
                    foreach( $productVariants as $productVariant ){
                        if( in_array($title, $productVariant) ){
                            $product_variant_ids[] = $productVariant['product_variant_id'];
                        }
                    }
                }
                ProductVariantPrice::create([
                    'product_variant_one' => count($product_variant_ids) > 0 ? $product_variant_ids[0] : null,
                    'product_variant_two' => count($product_variant_ids) > 1 ? $product_variant_ids[1] : null,
                    'product_variant_three' => count($product_variant_ids) > 2 ? $product_variant_ids[2] : null,
                    'price' => $product_variant_price['price'],
                    'stock' => $product_variant_price['stock'],
                    'product_id' => $product->id,
                ]);
            }
            
            $response = [
                'product' => $product,
                'success' => true
            ];
        } catch(\Exception $e){
            if(config('app.env') != 'production')
                $response['getTrace'] = $e->getTrace();
            $response['errors'] = $e->getMessage();
            $status = 500;
        }
        return response()->json($response, $status);
    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $variants = Variant::all();
        $product->images = $product->productImages()->get();

        $productVariantPrices = ProductVariantPrice::where('product_id', $product->id )->get();
        $productVariants = ProductVariant::where('product_id', $product->id )->get();
        $productVariantIds = [];
        foreach( $productVariants as $productVariant ) {
            if( !in_array($productVariant->variant_id, $productVariantIds) ){
                $productVariantIds[] = $productVariant->variant_id;
            }
        }
        $productVariantArr = [];
        foreach( $productVariantIds as $productVariantId ) {
            $productVariantText = [];
            foreach( $productVariants as $productVariant ) {
                if( $productVariant->variant_id == $productVariantId ){
                    $productVariantText[] = $productVariant->variant;
                }
            }
            $productVariantArr[] =[
                'id' => $productVariantId,
                'tags' => $productVariantText
            ];
        }
        $product->productVariants = $productVariantArr;
        $productVariantPriceArr = [];
        foreach( $productVariantPrices as $productVariantPrice ) {
            $variantText = '';
            foreach( $productVariants as $productVariant ) {
                if( $productVariant->id = $productVariantPrice->product_variant_one ){
                    $variantText .= $productVariant->variant . '/';
                } else if ( $productVariant->id = $productVariantPrice->product_variant_two ) {
                    $variantText .= $productVariant->variant . '/';
                } else if ( $productVariant->id = $productVariantPrice->product_variant_three ) {
                    $variantText .= $productVariant->variant;
                }
            }
            $productVariantPrice->variantText = $variantText;
            $productVariantPriceArr[] = $productVariantPrice;
        }
        $product->productVariantPrices = $productVariantPriceArr;

        return view('products.edit', [
            'variants' => $variants,
            'product'  => $product,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(ProductUpdate $request, Product $product)
    {
        $response = [];
        $status = 200;
        try {
            $product->update([
                'title'         => $request->title,
                'sku'           => $request->sku,
                'description'   => $request->description,
            ]);

            $images = $request->product_image;
            foreach( $images as $image ){
                ProductImage::where('id', $image['id'])->update([
                    'product_id' => $product->id,
                ]);
            }

            $productVariants = [];

            $product_variants = $request->product_variant;
            foreach( $product_variants as $product_variant ){
                foreach( $product_variant['tags'] as $tag ) {
                    $checkProductVariant = ProductVariant::where('variant', $tag,)->where('variant_id', $product_variant['option'])->where('product_id', $product->id,)->first();
                    if( !$checkProductVariant ) {
                        $productVariant = ProductVariant::create([
                            'variant' => $tag,
                            'variant_id' => $product_variant['option'],
                            'product_id' => $product->id,
                        ]);
                        $productVariants[] = [
                            'product_variant_id' => $productVariant->id,
                            'variant' => $tag,
                            'variant_id' => $product_variant['option'],
                            'product_id' => $product->id,
                        ];
                    } else {
                        $productVariants[] = [
                            'product_variant_id' => $checkProductVariant->id,
                            'variant' => $tag,
                            'variant_id' => $product_variant['option'],
                            'product_id' => $product->id,
                        ];
                    }
                }
            }

            $product_variant_prices = $request->product_variant_prices;

            // foreach( $product_variant_prices as $product_variant_price ){
            //     $titles = preg_split('@/@', $product_variant_price['title'], NULL, PREG_SPLIT_NO_EMPTY);
            //     $product_variant_ids = [];
            //     foreach( $titles as $title ) {
            //         foreach( $productVariants as $productVariant ){
            //             if( in_array($title, $productVariant) ){
            //                 $product_variant_ids[] = $productVariant['product_variant_id'];
            //             }
            //         }
            //     }
            //     ProductVariantPrice::create([
            //         'product_variant_one' => count($product_variant_ids) > 0 ? $product_variant_ids[0] : null,
            //         'product_variant_two' => count($product_variant_ids) > 1 ? $product_variant_ids[1] : null,
            //         'product_variant_three' => count($product_variant_ids) > 2 ? $product_variant_ids[2] : null,
            //         'price' => $product_variant_price['price'],
            //         'stock' => $product_variant_price['stock'],
            //         'product_id' => $product->id,
            //     ]);
            // }
            
            $response = [
                'product' => $product,
                'success' => true
            ];
        } catch(\Exception $e){
            if(config('app.env') != 'production')
                $response['getTrace'] = $e->getTrace();
            $response['errors'] = $e->getMessage();
            $status = 500;
        }
        return response()->json($response, $status);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
