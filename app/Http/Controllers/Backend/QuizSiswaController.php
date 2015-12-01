<?php

/**
 * controller class untuk mengelola quiz yg ada pada level siswa
 */

namespace App\Http\Controllers\Backend;

use App\Helpers\Fungsi;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\Mst\KelasUser;
use App\Models\Mst\PengerjaanSoal;
use App\Models\Mst\Soal;
use App\Models\Mst\TopikSoal;
use App\Models\Ref\Kelas;
use Illuminate\Http\Request;

class QuizSiswaController extends Controller
{
    private $base_view = 'konten.backend.quiz_siswa.';
    protected $kelas_user;
    protected $kelas;
    protected $topik_soal;
    protected $pengerjaan_soal;
    protected $soal;
    protected $fungsi;

    public function __construct( KelasUser $kelas_user,  
    							 Kelas $kelas,  
    							 TopikSoal $topik_soal,
                                 PengerjaanSoal $pengerjaan_soal, 
                                 Soal $soal,
                                 Fungsi $fungsi
    							)
    {
        $this->fungsi           = $fungsi;
        $this->soal             = $soal;
        $this->pengerjaan_soal  = $pengerjaan_soal;
    	$this->topik_soal 	    = $topik_soal;
    	$this->kelas 		    = $kelas;
    	$this->kelas_user	    = $kelas_user;
    	view()->share('backend_quiz_siswa_home', true);
    	view()->share('base_view', $this->base_view);
    }

    /**
     * daftar quiz yg harus siswa kerjakan
     * @return [type] [description]
     */
    public function index()
    {
    	$kelas_user = $this->kelas_user
    					   ->with('ref_kelas')
    					   ->where('mst_user_id', '=', \Auth::user()->id)
    					   ->paginate(10);
    	$vars = compact('kelas_user');
    	return view($this->base_view.'index', $vars);
    }


    /**
     * daftar soal yg ada dalam topik quiz yg dipilih berdasarkan ID
     * @param  [type] $ref_kelas_id [description]
     * @return [type]               [description]
     */
    public function quiz($ref_kelas_id)
    {
    	$kelas = $this->kelas->findOrFail($ref_kelas_id);
    	$topik_soal = $this->topik_soal
    				  ->where('ref_kelas_id', '=', $ref_kelas_id)
    				  ->paginate(10);
    	$vars = compact('topik_soal', 'kelas');
    	return view($this->base_view.'quiz.index', $vars);
    }


    /**
     * GET menampilkan halaman untuk mengerjakan soal bagi level siswa
     * @param  [type] $mst_soal_id [description]
     * @return [type]              [description]
     */
    public function kerjakan_soal($mst_topik_soal_id)
    {
        $topik_soal = $this->topik_soal
        				   ->findOrFail($mst_topik_soal_id);
        $pengerjaan_soal = $this->pengerjaan_soal
        						->where('mst_topik_soal_id', '=', $mst_topik_soal_id)
        						->first();         
        $vars = compact('topik_soal', 'pengerjaan_soal');
        return view($this->base_view.'kerjakan_soal.index', $vars);
    }

    /**
     * update timer soal, saat siswa sedang mengerjakan tugas / quiz
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function update_timer_soal(Request $request)
    {
        if($request->has('mst_topik_soal_id')){
            $check_pengerjaan = $this->pengerjaan_soal
                                     ->where('mst_topik_soal_id', '=', $request->mst_topik_soal_id)
                                     ->first();
            if(count($check_pengerjaan) <= 0){
                $data_pengerjaan = [
                    'mst_topik_soal_id' => $request->mst_topik_soal_id,
                    'waktu_mulai'       => date('Y-m-d H:i:s'),
                    'mst_user_id'       => \Auth::user()->id
                    ];
                $this->pengerjaan_soal->create($data_pengerjaan);
                \Session::put('waktu_pengerjaan', $request->value);                 
            }
        }else{
            \Session::put('waktu_pengerjaan', $request->value);            
        }
        return $request->value;
    }


    /**
     * POST action untuk submit selesai mengerjakan soal
     * @return [type] [description]
     */
    public function selesai_mengerjakan_soal(Request $request)
    {

        $ps = $this->pengerjaan_soal->where('mst_topik_soal_id', '=', $request->mst_topik_soal_id)->first();
        if($ps->waktu_selesai == null){
            $ps->waktu_selesai = date('Y-m-d H:i:s');
            $ps->save();             
        }
        return $ps;
    }

    /**
     * GET halaman untuk menampilkan hasil nilai setelah mengerjakan
     * @param  [type] $mst_topik_soal_id [description]
     * @return [type]                    [description]
     */
    public function lihat_hasil($mst_topik_soal_id)
    {
        $soal = $this->soal
                     ->where('mst_topik_soal_id', '=', $mst_topik_soal_id)
                     ->paginate(10);
        $fungsi = $this->fungsi;
        $topik_soal = $this->topik_soal->findOrFail($mst_topik_soal_id);
        $vars = compact('soal', 'fungsi', 'topik_soal'); 
        return view($this->base_view.'lihat_hasil.index', $vars);
    }





}
