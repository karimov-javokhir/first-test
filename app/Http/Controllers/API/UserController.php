<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
use App\Token;



class UserController extends Controller
{
    public function register(Request $request){

        //validation
        $validate = validator($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'login' => 'required|min:5|unique:users',
            'imei' => 'required',
            'password_confirmation' => 'required|same:password'
        ]);

        if($validate->fails()){
            return ['messages'=>(array)$validate->errors()->all()]+['authorized'=>false];
        }

        $user = new User(['name'=>$request->input('name'),'balance'=>0,'images'=>'[]',  'email'=>$request->input('email'), 'login'=>$request->input('login'),'password'=>\password_hash($request->input('password').'as@',PASSWORD_BCRYPT)]);

        $user->save();

        if($request->hasFile('photo')){

            $path = "/images/user/".$user->id."/";
            $photo = md5('jpg'.microtime().rand(0,1000)).".jpg";
            $pub = public_path($path);
            if(!file_exists($pub)){
                mkdir($pub);
            }
            $request->file('photo')->storeAs($path, $photo, 'public_html');

            $user->addImage(Image::path($path.$photo));
            $user->save();
        }

        $token = Token::where('imei',$request->input('imei'))->first();

        if($token!=null){
            
            $token->delete();
        }

        $token = new Token(['user_id'=>$user->id,'token'=>bcrypt(microtime().'i'.random_int(0,100000)),'imei'=>$request->input('imei'),'description'=>$this->tokenDesc($request)]);

        $token->save();
        $res = [];

        $res = ['authorized'=>true,'token'=>$token->toArray(), 'user'=>$user->toArray()];

        return $res;
    }
    
    public function login(Request $request){

        //validation
        $validate = validator($request->all(), [
            'email' => 'required',
            'password' => 'required',
            'imei' => 'required',
        ]);


        
        if($validate->fails()){
            return ['messages'=>(array)$validate->errors()->all()]+['authorized'=>false];
        }
        
        

        $user = User::where('email',$request->input('email'))->orWhere('login',$request->input('email'))->where('use_google',0)->first();

        if($user==null){
            return ['authorized'=>false,'messages'=>['user not found','']];
        }

        if(!password_verify($request->input('password').'as@',$user->password)){
            return ['authorized'=>false,'password'=>'incorrect!'];
        }

        $token = Token::where('imei',$request->input('imei'))->first();

        if($token!=null){
            
            $token->delete();
        }


        $token = new Token(['user_id'=>$user->id,'token'=>bcrypt(microtime().'i'.random_int(0,100000)),'imei'=>$request->input('imei',12345),'description'=>$this->tokenDesc($request)]);

        $token->save();
        $res = [];

        $res = ['authorized'=>true,'token'=>$token->toArray(), 'user'=>$user->toArray()];
        

        return $res;
    }

    
    public function googleLogin(Request $request){

        //validation
        $validate = validator($request->all(), [
            'google_id'=>'required|integer|between:1,*',
            'imei' => 'required',
        ]);


        
        
        if($validate->fails()){
            return $validate->errors()->all()+['authorized'=>false];
        }
        
        


        $user = User::where('google_id',$request->input('google_id'))->first();

        if($user==null){
            return ['authorized'=>false,'messages'=>['note'=>'user not found']];
        }


        $token = Token::where('imei',$request->input('imei'))->first();

        if($token!=null){
            
            $token->delete();
        }

        $token = new Token(['user_id'=>$user->id,'token'=>bcrypt(microtime().'i'.random_int(0,100000)),'imei'=>$request->input('imei',12345),'description'=>$this->tokenDesc($request)]);

        $token->save();
        $res = [];

        $res = ['authorized'=>true,'token'=>$token->toArray()];

        return $res;
    }

    public function googleRegister(Request $request){

        //validation
        $validate = validator($request->all(), [
            'name' => 'required|max:255',
            'photo_url' => 'required|url',
            'google_id'=>'required|numeric|gte:1|unique:users',
            'email' => 'required|email|unique:users',
            'imei' => 'required',
        ]);
        


        if($validate->fails()){
            return ['messages'=>$validate->errors()->all()]+['authorized'=>false];
        }

        $user = new User(['name'=>$request->input('name'),
         'email'=>$request->input('email'),'password'=>'','google_id'=>$request->input('google_id'),'use_google'=>1]);

        $user->save();

        $token = Token::where('imei',$request->input('imei'))->first();

        if($token!=null){
            
            $token->delete();
        }

        $token = new Token(['user_id'=>$user->id,'token'=>bcrypt(microtime().'i'.random_int(0,100000)),'imei'=>$request->input('imei'),'description'=>$this->tokenDesc($request)]);

        $token->save();
        $res = [];

        $res = ['authorized'=>true,'token'=>$token->toArray()];

        return $res;
    }

    public function tokenDesc(Request $req){
        return json_encode(['version'=>$req->get('version','Unknown'), 'manufactorer'=>$req->get('company','Unknown'), 'ip'=>$req->ip()]);
    }

}
