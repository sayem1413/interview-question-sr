<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ProductImageStore;
use App\Models\ProductImage;
use App\Traits\StoresImage;
use DB;

class ProductImageController extends Controller
{
    use StoresImage;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductImageStore $request)
    {
        $response = [];
        $status = 200;
        try {
            if( $request->has('productImage') ){
                $image = $this->verifyAndStoreImage($request, 'productImage', 'productImage', "product-image");
                $productImageName = $request->file('productImage')->getClientOriginalName();
                $productImage = ProductImage::create([
                    "product_id" => 1,
                    "file_path"  => $image,
                ]);
                $response = [
                    'file_path' => $image,
                    'productImage' => $productImage,
                    'success' => true
                ];
            } else {
                return response()->json(["success" => false, "message" => "permission denied"]);
            }
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductImage $productImage)
    {
        
    }
}
