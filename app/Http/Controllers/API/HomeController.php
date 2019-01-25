<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\Request;
use App\Http\Controllers\Controller;
use App\ServiceCategory;
use App\ProductCategory;
use App\Brand;
use App\Product;
use Illuminate\Support\Facades\URL;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = [];

        $result['brand'] = Brand::all()->toArray();

        $result['service_category'] = ServiceCategory::where('parent_id',0)->get()->toArray();
        
        $result['product_category'] = ProductCategory::where('parent_id',0)->get()->toArray();


        return response($result);
    }


    //this is working with only one depth children
    public function subServiceCategory($id){

        $service = ServiceCategory::find($id);

        $result = [];
        
        if($service != null){
            $result = $service->children()->with('children')->get()->toArray();
        }
        
        return response($result);
    }


    //this is working with only one depth children
    public function subProductCategory($id){

        $product = ProductCategory::find($id);

        $result = [];
        
        if($product != null){
            $result = $product->children()->with('children')->get()->toArray();
        }
        
        return response($result);
    }


    
    public function Products(Request $request){


        $name = $request->get('name');

        $brand_id = $request->get('brand_id');
        
        //this will also determine attribute groups
        $pro_cat_id = @$request->get('product_category_id');
        
        //ids of the attributes
        $attributes = @$request->get('attributes');
        
        //order, might include price, created_at
        $order = @$request->get('order');

        //order_type, one of these: asc or desc
        $order_type = @$request->get('order_type');

        if($order != "price" && $order != "created_at"){
            $order = "id";
        }

        if($order_type!="asc"&&$order_type!="desc"){
            $order_type = "asc";
        }

        $products = Product::orderBy($order, $order_type);

        if(\is_numeric($brand_id)){
            $products->where('brand_id',$brand_id);
        }

        if(is_numeric($pro_cat_id)){
            $products->where('product_category_id',$pro_cat_id);
        }

        if($name!=null){
            $products->where('name',$name);
        }


        if(\is_array($attributes)){
            $products->whereHas('attributes',function($q) use($attributes){
                $q->whereIn('id',$attributes);
            })->orderBy($order, $order_type);
        }


        $products = $products->paginate(20)->toArray();


        $attrs = ['brand_id'=>$brand_id, 'product_category_id'=>$pro_cat_id, 'order'=>$order, 'order_type'=>$order_type, 'attributes'=>$attributes];
        $products['first_page_url'] .= '&'.http_build_query($attrs);

        $products['last_page_url'] .= '&'.http_build_query($attrs);
        
        if($products['next_page_url']!=null){
            $products['next_page_url'] .= '&'.http_build_query($attrs);
        }
        
        if($products['prev_page_url']!=null){
            $products['prev_page_url'] .= '&'.http_build_query($attrs);
        }

        return response($products);


    }


    public function Services(Request $request){

        
        $name = $request->get('name');

        $brand_id = $request->get('brand_id');
        
        //this will also determine attribute groups
        $ser_cat_id = @$request->get('service_category_id');
        
        //ids of the attributes
        $attributes = @$request->get('attributes');
        
        //order, might include price, created_at
        $order = @$request->get('order');

        //order_type, one of these: asc or desc
        $order_type = @$request->get('order_type');

        if($order != "price" && $order != "created_at"){
            $order = "id";
        }

        if($order_type!="asc"&&$order_type!="desc"){
            $order_type = "asc";
        }

        $services = Service::orderBy($order, $order_type);

        if(\is_numeric($brand_id)){
            $services->where('brand_id',$brand_id);
        }
        if($name!=null){
            $services->where('name',$name);
        }

        if(is_numeric($pro_cat_id)){
            $services->where('product_category_id',$ser_cat_id);
        }


        if(\is_array($attributes)){
            $services->whereHas('attributes',function($q) use($attributes){
                $q->whereIn('id',$attributes);
            })->orderBy($order, $order_type);
        }


        $services = $services->paginate(20)->toArray();


        $attrs = ['brand_id'=>$brand_id, 'service_category_id'=>$ser_cat_id, 'order'=>$order, 'order_type'=>$order_type, 'attributes'=>$attributes];
        $services['first_page_url'] .= '&'.http_build_query($attrs);

        $services['last_page_url'] .= '&'.http_build_query($attrs);
        
        if($services['next_page_url']!=null){
            $services['next_page_url'] .= '&'.http_build_query($attrs);
        }
        
        if($services['prev_page_url']!=null){
            $services['prev_page_url'] .= '&'.http_build_query($attrs);
        }

        return response($services);
        
    }

    public function ServiceAttributes($id){

        $groups = ServiceCategory::find($id)->attributesGroups()->with('children')->get();

        return $groups;
    }


    public function ProductAttributes($id){

        $groups = ProductCategory::find($id)->attributesGroups()->with('children')->get();

        return $groups;
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
    public function destroy($id)
    {
        //
    }
}
