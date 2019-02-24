<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use UseImage;
    public $fillable = ['id','name','description','address','phone','images'];

    public function roles(){
        return $this->hasMany('App\RolesOfStores','store_id','id');
    }

    public function delete(){

        \DB::table('roles_of_stores')->where('store_id',$this->id)->delete();
        \DB::table('store_subscription')->where('store_id',$this->id)->delete();

        foreach(Product::where('store_id',$this->id)->get() as $pro){
            $pro->delete();
        }
        foreach(Service::where('store_id',$this->id)->get() as $pro){
            $pro->delete();
        }
        
        foreach(Action::where('store_id',$this->id)->get() as $pro){
            $pro->delete();
        }
        foreach(Discount::where('store_id',$this->id)->get() as $pro){
            $pro->delete();
        }
        
        

        $this->removeAllImages();
        
        $path = "/images/store/".$this->id."/";
        try{
            rmdir(public_path($path));
        }catch(\Exception $e){}
        
        return parent::delete();
    }
    
}
