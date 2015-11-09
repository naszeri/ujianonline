<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Http\Requests\MyProfile\UpdateProfile;
use App\Models\Mst\DataUser;
use Illuminate\Http\Request;

class MyProfileController extends Controller
{
    private $base_view = 'konten.backend.myprofile.';
    private $data_user;

    public function __construct(DataUser $data_user)
    {
        $this->data_user = $data_user;
    	view()->share('base_view', $this->base_view);
    }

    public function index()
    {
    	$backend_myprofile_home = true;
        $jenis_kelamin = DataUser::$jenis_kelamin;
    	$vars = compact('backend_myprofile_home', 'jenis_kelamin');
    	return view($this->base_view.'index', $vars);
    }


    public function update_profile(UpdateProfile $request)
    {
        $du = $this->data_user->find(\Auth::user()->data_user->id);
        $du->nama = $request->nama;
        $du->tempat_lahir = $request->tempat_lahir;
        $du->tgl_lahir = $request->tgl_lahir;
        $du->jenis_kelamin = $request->jenis_kelamin;
        $du->save();
        return 'ok';
    }
}
