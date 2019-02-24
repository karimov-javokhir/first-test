<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
use App\Token;



class UserController extends Controller
{
    public function __construct(Request $request)
    {
        
        if($request->header('xx-token',false)&&$request->header('xx-user-id',false)){
            $t = Token::where('user_id', $request->header('xx-user-id',false))->where('token',$request->header('xx-token',false))->first();
            if($t!=null){
                $this->user = $t->user;
                if($this->user!=null){
                    $this->authenticated = true;
                    $this->token = $request->get('token');
                    $this->user_id = $request->get('user_id');
                }
            }
        }
    }

    public $authenticated = false;
    public $user = null;
    public $user_id = null;
    public $token = null;

    public function register(Request $request){

        //validation
        $validate = validator($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'imei' => 'required',
            'password_confirmation' => 'required|same:password'
        ]);

        if($validate->fails()){
            return ['messages'=>$validate->errors()->all()]+['authorized'=>false];
        }

        $user = new User(['name'=>$request->get('name'), 'email'=>$request->get('email'),'password'=>\password_hash($request->get('password').'as@',PASSWORD_BCRYPT)]);

        $user->save();

        if($request->hasFile('photo')){

            $path = "/images/user/".$request->id."/";
            $photo = md5('jpg'.microtime().rand(0,1000)).".jpg";
            $pub = public_path($path);
            if(!file_exists($pub)){
                mkdir($pub);
            }
            $request->file('photo')->storeAs($path, $photo, 'public_html');

            $request->addImage(Image::path($path.$photo));
            $request->save();
        }
        
        $token = Token::where('imei',$request->get('imei'))->first();

        if($token!=null){
            
            $token->delete();
        }

        $token = new Token(['user_id'=>$user->id,'token'=>bcrypt(microtime().'i'.random_int(0,100000)),'imei'=>$request->get('imei'),'description'=>$this->tokenDesc($request)]);

        $token->save();
        $res = [];

        $res = ['authorized'=>true,'token'=>$token->toArray()];

        return $res;
    }
    
    public function login(Request $request){

        //validation
        $validate = validator($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'imei' => 'required',
        ]);

        
        if($validate->fails()){
            return ['messages'=>$validate->errors()->all()]+['authorized'=>false];
        }
        
        

        $user = User::where('email',$request->get('email'))->where('use_google',0)->first();

        if($user==null){
            return ['authorized'=>false,'messages'=>['note'=>'user not found']];
        }

        if(!password_verify($request->get('password').'as@',$user->password)){
            return ['authorized'=>false,'password'=>'incorrect!'];
        }

        $token = Token::where('imei',$request->get('imei'))->first();

        if($token!=null){
            
            $token->token = bcrypt(microtime().'i'.random_int(0,100000));
            $token->save();
            $res = [];
            
            $res = ['authorized'=>true, 'token'=>$token->toArray()];
            
            return $res;
        }

        $token = new Token(['user_id'=>$user->id,'token'=>bcrypt(microtime().'i'.random_int(0,100000)),'imei'=>$request->get('imei',12345),'description'=>$this->tokenDesc($request)]);

        $token->save();
        $res = [];

        $res = ['authorized'=>true,'token'=>$token->toArray()];

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
        
        


        $user = User::where('google_id',$request->get('google_id'))->first();

        if($user==null){
            return ['authorized'=>false,'messages'=>['note'=>'user not found']];
        }


        $token = Token::where('imei',$request->get('imei'))->first();

        if($token!=null){
            
            $token->token = bcrypt(microtime().'i'.random_int(0,100000));
            $token->save();
            $res = [];
            
            $res = ['authorized'=>true, 'token'=>$token->toArray()];
            
            return $res;
        }

        $token = new Token(['user_id'=>$user->id,'token'=>bcrypt(microtime().'i'.random_int(0,100000)),'imei'=>$request->get('imei',12345),'description'=>$this->tokenDesc($request)]);

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

        $user = new User(['name'=>$request->get('name'),
         'email'=>$request->get('email'),'password'=>'','google_id'=>$request->get('google_id'),'use_google'=>1]);

        $user->save();

        $token = Token::where('imei',$request->get('imei'))->first();

        if($token!=null){
            
            $token->delete();
        }

        $token = new Token(['user_id'=>$user->id,'token'=>bcrypt(microtime().'i'.random_int(0,100000)),'imei'=>$request->get('imei'),'description'=>$this->tokenDesc($request)]);

        $token->save();
        $res = [];

        $res = ['authorized'=>true,'token'=>$token->toArray()];

        return $res;
    }

    public function tokenDesc(Request $req){
        return json_encode(['version'=>$req->get('version','Unknown'), 'manufactorer'=>$req->get('company','Unknown'), 'ip'=>$req->ip()]);
    }

    public function check(){
        dd($this->user);
    }
}
