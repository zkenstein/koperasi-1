<?php

class Pulsa extends CI_Controller {


	public function __construct()

	{
        parent::__construct();
        // $this->load->model('datacell_model');
        // $this->load->model('datacell_transaction_model');
        $this->load->model('gerai_transaksi_model');
        $this->load->model('gerai_vendor_produk_model');
        $this->load->helper('core_banking_helper');
        $this->load->library('core_banking_api');
        // $this->session->set_userdata('referred_from', current_url());
        if (!is_login()) {
			redirect('login','REFRESH');
		}
	}



	function index(){
		
		/*$this->load->library('datacell_api');
		$data = array(
			'oprcode'		=> 'IR.10',
			'msisdn' 		=> '085624137383',
			'ref_trxid' 	=> '12345',
			'service_user' 	=> '54312',
			);

		$charge = $this->datacell_api->charge($data);
        print_r($charge);*/

		/*$this->load->library('core_banking_api');
		$id_user 			= '91459698104';
		$total_debit 		= '1500';
		$kode_transaksi 	= 'GERAI';
		$jenis_transaksi 	= 'TOPUP PULSA IM3';
		$debet = $this->core_banking_api->debit_virtual_account($id_user,$total_debit,$kode_transaksi,$jenis_transaksi);
		print_r($debet);*/


		/*$this->load->library('core_banking_api');
		$id_user 			= '91459698104';
		$total_point 		= '1000';
		$sumber_dana 		= 'GERAI';
		$jenis_transaksi 	= 'TOPUP PULSA IM3';
		$deposit = $this->core_banking_api->deposit_loyalty_account($id_user,$total_point,$sumber_dana,$jenis_transaksi);
		print_r($deposit);*/
		$session = $this->session->userdata();
	
		if ($session['level']==1 || $session['level']==2) {
			redirect(site_url('gerai/admin/pulsa'));
		}

        $data['page'] = 'pulsa/pulsa_list_view';
        $this->load->view('main_view',$data);
	}


	function topup($provider=NULL){
	$this->load->library('form_validation');

		$this->form_validation->set_rules('nominal', 'nominal', 'required|xss_clean|callback_is_product_valid');
		$this->form_validation->set_rules('phone_number','Nomor Telepon', 'required|xss_clean|numeric|min_length[10]|max_length[12]');

		if($this->form_validation->run() == FALSE){

			if (!is_login()) {
				redirect('login','REFRESH');
			}

			if (!has_koperasi()) {
				$data['page'] = "auth/404_koperasi_view";
			}else{
				$data['page'] = "pulsa/pulsa_topup_form_view";
			}

			if ($provider==NULL) {
				redirect('gerai/pulsa');
			}


			$data['form_action'] = site_url().'gerai/pulsa/topup/'.$this->uri->segment(4);

			switch ($provider) {

				case 'indosat':

					$data['operator_id']	= 'INDOSAT';
					$data['products']		=  $this->get_product($data['operator_id']);


					$data['provider_logo'] 	= base_url('assets/compro/IMAGE/provider').'/'.'indosat.png';
					$data['provider_name'] 	= 'Indosat';

					break;

				case 'telkomsel':

					$data['operator_id']	= 'TELKOMSEL';
					$data['products']		=  $this->get_product($data['operator_id']);


					$data['provider_logo'] 	= base_url('assets/compro/IMAGE/provider').'/'.'telkomsel.png';
					$data['provider_name'] 	= 'Telkomsel';

					break;

				case 'xl':

					$data['operator_id']	= 'XL';
					$data['products']		=  $this->get_product($data['operator_id']);


					$data['provider_logo'] 	= base_url('assets/compro/IMAGE/provider').'/'.'XL.png';
					$data['provider_name'] 	= 'XL';

					break;

				case 'esia':

					$data['operator_id']	= 'ESIA';
					$data['products']		=  $this->get_product($data['operator_id']);


					$data['provider_logo'] 	= base_url('assets/compro/IMAGE/provider').'/'.'esia.png';
					$data['provider_name'] 	= 'Esia';

					break;

				case 'smartfren':

					$data['operator_id']	= 'SMARTFREN';
					$data['products']		=  $this->get_product($data['operator_id']);


					$data['provider_logo'] 	= base_url('assets/compro/IMAGE/provider').'/'.'smartfren.png';
					$data['provider_name'] 	= 'Smartfren';

					break;

				case 'flexi':

					$data['operator_id']	= 'FLEXI';
					$data['products']		=  $this->get_product($data['operator_id']);


					$data['provider_logo'] 	= base_url('assets/compro/IMAGE/provider').'/'.'flexi.png';
					$data['provider_name'] 	= 'Flexi';

					break;

				case 'axis':

					$data['operator_id']	= 'AXIS';
					$data['products']		=  $this->get_product($data['operator_id']);


					$data['provider_logo'] 	= base_url('assets/compro/IMAGE/provider').'/'.'axis.png';
					$data['provider_name'] 	= 'Axis';

					break;

				case 'tri':

					$data['operator_id']	= 'THREE';
					$data['products']		=  $this->get_product($data['operator_id']);


					$data['provider_logo'] 	= base_url('assets/compro/IMAGE/provider').'/'.'3.png';
					$data['provider_name'] 	= 'Tri';


					break;

				case 'ceria':

					$data['operator_id']	= 'CERIA';
					$data['products']		= $this->get_product($data['operator_id']);


					$data['provider_logo'] 	= base_url('assets/compro/IMAGE/provider').'/'.'3.png';
					$data['provider_name'] 	= 'Ceria';

					break;	

				default:

					redirect('gerai/pulsa');

					/*$data['page'] = "under_404";
					$this->load->view('main_view',$data);*/

					break;

			}

			$this->load->view('main_view',$data);

		}
		else {
			$post = $this->input->post();
			$this->session->set_flashdata('pulsa', $post);
			redirect(base_url()."gerai/pulsa/topup_confirm", 'REFRESH');
		}
		
	}


	function get_product($operator_id,$nominal=NULL){
		$parameter_produk	= array(
					'kode_operator'			=> $operator_id,
					'kode_kategori_produk' 	=> 'PULSA',
					'jenis_transaksi' 		=> 'BELI',
					);
		if ($nominal!=NULL) {
			$parameter_produk['nominal_produk'] = $nominal;
		}
		$get_product = $this->gerai_vendor_produk_model->get_nominal($parameter_produk);
		return $get_product;
	}


	function is_pin_valid(){
		$id_user = $this->session->userdata('id_user');
		$pin 	 = $this->input->post('pin');

		$permission = $this->core_banking_api->check_pin($id_user,$pin);
		
		if ($permission['status']==TRUE) {
			return TRUE;
		}else{
			$this->form_validation->set_message('is_pin_valid', $permission['message']);
			return FALSE;
		}
	}


	function is_product_valid(){
		$operator_id = $this->input->post('operator_id');
		$nominal 	 = $this->input->post('nominal');
		$get_product = $this->get_product($operator_id,$nominal);

		if ($get_product==FALSE) {
			$this->form_validation->set_message('is_product_valid', 'Maaf Produk Tidak Ditemukan');
			return FALSE;
		}

		$id_user = $this->session->userdata('id_user');
		$total_debit = $get_product[0]['harga_gerai'];

		$permission = $this->core_banking_api->debit_virtual_account_permission($id_user,$total_debit);
		
		if ($permission['status']==TRUE) {
			return TRUE;
		}else{
			$this->form_validation->set_message('is_product_valid', 'Transaksi Tidak Diizinkan Karena '.$permission['message']);
			return FALSE;
		}

	}

function topup_confirm(){


		$this->load->library('form_validation');
		$this->form_validation->set_rules('pin', 'PIN', 'required|xss_clean|numeric|callback_is_pin_valid');
		$this->form_validation->set_rules('provider_name', 'provider_name', 'required|xss_clean');
		$this->form_validation->set_rules('provider_logo', 'provider_logo', 'required|xss_clean');
		$this->form_validation->set_rules('phone_number', 'phone_number', 'required|xss_clean');
		$this->form_validation->set_rules('operator_id', 'operator_id', 'required|xss_clean');
		$this->form_validation->set_rules('nominal', 'nominal', 'required|xss_clean|callback_is_product_valid');

		if ($this->form_validation->run() == FALSE)
		{
			
			$post = $this->session->flashdata('pulsa');
			$this->session->set_flashdata('pulsa', $post);
			
			if (!isset($post)) {
				$post = $this->input->post();
			}
			
			if (!isset($post['submit'])) {
				redirect('gerai/pulsa');
			}

			if (!isset($post['operator_id']) || !isset($post['nominal']) || !is_login()) {
				redirect('gerai/pulsa');
			}

			$get_product = $this->get_product($post['operator_id'],$post['nominal']);
			
			if ($get_product==FALSE) {
				$data['product'] = NULL;
			}else{
				$data['product'] = $get_product[0];
				$data['product']['phone_number'] 	= $post['phone_number'];
			}

			$data['provider_name'] 	= $post['provider_name'];
			$data['provider_logo'] 	= $post['provider_logo'];

			$data['form_action'] = site_url('gerai/pulsa/topup_confirm');
			$data['page'] 		 = "pulsa/pulsa_topup_confirm_form_view";
			
			$this->load->view('main_view',$data);
			
		}
		else
		{

			$post = $this->input->post();
			if (!isset($post['operator_id']) || !isset($post['nominal']) || !isset($post['pin'])  || !is_login()) {
				redirect('gerai/pulsa');
			}

			$get_product = $this->get_product($post['operator_id'],$post['nominal']);
			if ($get_product==FALSE) {
				redirect('gerai/pulsa');
			}else{
				$get_product = $get_product[0];
			}

			if ($post['nominal']!=$get_product['nominal_produk']) {
				redirect('gerai/admin/pulsa');
			}

			$service_user 	= $this->session->userdata('id_user');
			$service_action = 'INSERT';
			$trasaction_id 	= '13'.time();

			$data_insert = array(
				'no_transaksi' 			=> $trasaction_id,
				'kode_vendor' 			=> $get_product['kode_vendor'],
				'kode_operator' 		=> $get_product['kode_operator'],
				'kode_produk' 			=> $get_product['kode_produk'],
				'kode_kategori_produk' 	=> $get_product['kode_kategori_produk'],
				'nama_produk' 			=> $get_product['nama_produk'],
				'nominal_produk' 		=> $get_product['nominal_produk'],
				'harga_vendor'			=> $get_product['harga_vendor'],
				'harga_gerai'			=> $get_product['harga_gerai'],
				'jenis_transaksi'		=> 'BELI',
				'id_user'				=> $service_user,
				'msisdn'				=> $post['phone_number'],
				'tanggal_transaksi'		=> date('Y-m-d H:i:s'),
				'tanggal'			=> date('d'),
				'bulan'				=> date('m'),
				'tahun'				=> date('Y'),
				'service_time'			=> date('Y-m-d H:i:s'),
				'service_user'			=> $service_user,
				'service_action'		=> $service_action
				);

			$this->load->library('vsi_api');

	        $data = array(
	            'service_user' 	=> $service_user,
	            'produk'   		=> $get_product['kode_produk'],
	            'tujuan'    	=> $post['phone_number'],
	            'memberreff' 	=> $trasaction_id,
	            );

	        $data_report['provider_name'] 	= $post['provider_name'];
			$data_report['provider_logo'] 	= $post['provider_logo'];
	        $data_report['product'] 		= $get_product;
	        $data_report['data_insert'] 	= $data_insert;

	        $permission = $this->core_banking_api->debit_virtual_account_permission($service_user,$get_product['harga_gerai']);
			if ($permission['status']==FALSE) {
				$data_report['flash_msg']        = TRUE;
	            $data_report['flash_msg_type'] 	 = "danger";
	            $data_report['flash_msg_status'] = FALSE;
	            $data_report['flash_msg_text']   = $permission['message'];

	            $data_insert['status'] 			= 'GAGAL';
	            $data_insert['keterangan']  	= $permission['message'];
	            $data_report['data_insert'] 	= $data_insert;

	            $this->session->set_userdata('report',$data_report);
	            redirect('gerai/admin/pulsa/topup_report');
			}

	        // REQUEST KE VSI
	        $request_charge = $this->vsi_api->charge($data);


	        if ($request_charge==FALSE) {
	        	$data_report['flash_msg']        = TRUE;
	            $data_report['flash_msg_type'] 	 = "danger";
	            $data_report['flash_msg_status'] = FALSE;
	            $data_report['flash_msg_text']   = "Transaksi Gagal.Maaf, Internal Server sedang ada gangguan.";
	            $this->session->set_userdata('report',$data_report);

	            $data_insert['keterangan']  = 'Internal Server Error. API Error';
	            $insert = $this->gerai_transaksi_model->insert($data_insert);

	            redirect('gerai/pulsa/topup_report');
            }elseif ($request_charge['status']==FALSE) {
            	$data_report['flash_msg']        = TRUE;
	            $data_report['flash_msg_type'] 	 = "danger";
	            $data_report['flash_msg_status'] = FALSE;
	            $data_report['flash_msg_text']   = "Transaksi Gagal. ".$request_charge['message']['response_message'];
	            $this->session->set_userdata('report',$data_report);

	            $data_insert['ref_trxid']   = $request_charge['message']['trxid'];
	            $data_insert['status'] 		= 'GAGAL';
	            $data_insert['keterangan']  = $request_charge['message']['response_message'];
	            $insert = $this->gerai_transaksi_model->insert($data_insert);
	            redirect('gerai/pulsa/topup_report');
            
	        }else{

	        	//JUST FOR TEST
	        	/*print_r('<pre>');
	        	print_r($request_charge);
	        	print_r('</pre>');*/
	        	$data_insert['ref_trxid']   = $request_charge['message']['trxid'];
	            $data_insert['status'] 		= 'SUKSES';
	            $data_insert['keterangan']  = $request_charge['message']['response_message'];
				$insert = $this->gerai_transaksi_model->insert($data_insert);

	        	$this->load->library('core_banking_api');
				$id_user 			= $this->session->userdata('id_user');
				$total_debit 		= $get_product['harga_gerai'];
				$kode_transaksi 	= 'GERAI';
				$jenis_transaksi 	= 'TOPUP PULSA '.$get_product['nama_produk'];
				$debet_virtual_account = $this->core_banking_api->debit_virtual_account($id_user,$total_debit,$kode_transaksi,$jenis_transaksi,$trasaction_id);
				
				// REQUEST KE DATACELL BERHASIL. DEBET VIRTUAL ACCOUNT
				if ($debet_virtual_account['status']!=FALSE) {
					$total_point 		= $get_product['harga_gerai']-$get_product['harga_vendor'];
					$sumber_dana 		= 'GERAI';
					
					// DEBET VIRTUAL ACCOUNT BERHASIL. DEPOSIT POINT LOYALTI
					$share_profit = $this->core_banking_api->share_profit($id_user,$total_point,$sumber_dana,$jenis_transaksi,$trasaction_id);

					if ($share_profit['status']!=FALSE) {
						
						// DEPOSTI LOYALTI BERHASIL. INSERT TRANSAKSI GERAI
						/*$data_insert['ref_trxid']   = $request_charge['message']['trxid'];
			            $data_insert['status'] 		= 'SUKSES';
			            $data_insert['keterangan']  = $request_charge['message']['response_message'];
						$insert = $this->gerai_transaksi_model->insert($data_insert);*/

				        if ($insert!=FALSE) {
				        	// INSERT TRANSAKSI GERAI BERHASIL
				            $data_report['flash_msg']			= TRUE;
				            $data_report['flash_msg_type'] 		= "success";
				            $data_report['flash_msg_status'] 	= TRUE;
				            $data_report['flash_msg_text']		= "Isi Pulsa Berhasil. ".$debet_virtual_account['message'];
				            $this->session->set_userdata('report',$data_report);
				            redirect('gerai/pulsa/topup_report');

				        } else {
				            $data_report['flash_msg']        = TRUE;
				            $data_report['flash_msg_type'] 	 = "danger";
				            $data_report['flash_msg_status'] = FALSE;
				            $data_report['flash_msg_text']   = "Transaksi Gagal.";
				            $this->session->set_userdata('report',$data_report);
				            redirect('gerai/pulsa/topup_report');
				        }

					}else{

						$data_report['flash_msg']			= TRUE;
			            $data_report['flash_msg_type'] 		= "success";
			            $data_report['flash_msg_status'] 	= TRUE;
			            $data_report['flash_msg_text']		= "Isi Pulsa Berhasil (Not Sharing Profit Payment). ".$debet_virtual_account['message'];
			            $this->session->set_userdata('report',$data_report);
			            redirect('gerai/pulsa/topup_report');

						/*$data_report['flash_msg']        = TRUE;
			            $data_report['flash_msg_type'] 	 = "danger";
			            $data_report['flash_msg_status'] = FALSE;
			            $data_report['flash_msg_text']   = "Transaksi Gagal.".$share_profit['message'];
			            $this->session->set_userdata('report',$data_report);
			            redirect('gerai/pulsa/topup_report');*/
					}
					

				}else{
					$data_report['flash_msg']        = TRUE;
		            $data_report['flash_msg_type'] 	 = "danger";
		            $data_report['flash_msg_status'] = FALSE;
		            $data_report['flash_msg_text']   = "Isi Pulsa Berhasil (Debet Failed) ".$debet_virtual_account['message'];
		            $this->session->set_userdata('report',$data_report);
		            redirect('gerai/pulsa/topup_report');
				}

	        	
	        }

	        $this->cache->clean();
		}

		

	}



	function topup_report(){

		if (!$this->session->has_userdata('report')) {
			redirect('gerai/pulsa');
		}

		$report =  $this->session->userdata('report');
		$this->session->unset_userdata('report');

		$data['report'] = $report;

		$data['page'] 		 = "pulsa/pulsa_topup_report_form_view";
		$this->load->view('main_view',$data);

	}

	

}