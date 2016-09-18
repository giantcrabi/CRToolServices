<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require_once APPPATH . '/libraries/REST_Controller.php';

class Services extends REST_Controller
{
	public function __construct()
	{
		parent::__construct();
        $this->load->helper('url_helper');
        $this->load->model('Services_model');
	}

	public function outlet_get()
	{
		$IDOutlet = $this->get('id');
        $lat = $this->get('lat');
        $lng = $this->get('lng');

		if($IDOutlet === NULL)
        {
            if($lat && $lng)
            {
                $outlets = $this->Services_model->getOutlet(0, $lat, $lng);
            }
            else
            {
                $outlets = $this->Services_model->getOutlet();
            }

			if($outlets)
			{
				$this->response($outlets, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
			} 
			else
            {
                $this->response([[
                    'status' => FALSE,
                    'message' => 'No outlets were found'
                ]]);
            }
		}
		
		$IDOutlet = (int) $IDOutlet;

        if ($IDOutlet <= 0)
        {
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }
        else
        {
        	$outlet = $this->Services_model->getOutlet($IDOutlet);

        	if($outlet)
			{
				$this->response($outlet, REST_Controller::HTTP_OK);
			} 
			else
            {
                $this->response([[
                    'status' => FALSE,
                    'message' => 'The specified outlet were not found'
                ]], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            }
        }
	}

    public function SOreport_get()
    {
        $IDCR = $this->get('id');
        $DateFrom = $this->get('datefrom');
        $DateTo = $this->get('dateto');

        if($IDCR && $DateFrom && $DateTo)
        {
            $reports = $this->Services_model->getReport($IDCR, $DateFrom, $DateTo);

            if($reports)
            {
                $this->response($reports, REST_Controller::HTTP_OK);
            } 
            else
            {
                $this->response([[
                    'status' => FALSE,
                    'message' => 'No reports were found'
                ]]);
            }
        }
        else
        {
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function achievement_get()
    {
        $IDCR = $this->get('id');
        $date = $this->get('date');

        if($IDCR && $date)
        {
            $reports = $this->Services_model->getAchievement($IDCR, $date);

            if($reports)
            {
                $this->response($reports, REST_Controller::HTTP_OK);
            } 
            else
            {
                $this->response([[
                    'status' => FALSE,
                    'message' => 'No achievements were found'
                ]]);
            }
        }
    }

    public function SO_post()
    {
        $IDCR = $this->post('idCR');
        $SN = $this->post('SN');
        $IDOutlet = $this->post('idoutlet');
        $RegDate = $this->post('date');

        if($IDCR && $SN && $IDOutlet && $RegDate){
            $user = $this->Services_model->getUser($IDCR);
            $outlet = $this->Services_model->getOutlet($IDOutlet);
            $searched_SN = $this->Services_model->getSN($SN);

            if($user && $outlet && $searched_SN)
            {
                if($outlet[0]->ChannelID != $searched_SN[0]->BizcardID)
                {
                    $this->response([[
                        'status' => FALSE,
                        'message' => 'SN belong to another Dealer'
                    ]]);
                }

                $submittedSN = $this->Services_model->getSNSubmitted($SN);

                if($submittedSN)
                {
                    if($submittedSN[0]->CreateUserID == $user[0]->ID)
                    {
                        $this->response([[
                            'status' => FALSE,
                            'message' => "SN already submitted by you on ".$submittedSN[0]->RegDate.""
                        ]]);
                    }
                    else
                    {
                        $this->response([[
                            'status' => FALSE,
                            'message' => "SN already submitted by ".$submittedSN[0]->CreateUser." on ".$submittedSN[0]->RegDate.""
                        ]]);
                    }
                }

                $item = $this->Services_model->getItem($searched_SN[0]->ItemID);

                $data = array(
                    'CreateUserID' => $user[0]->ID,
                    'CreateUser' => $user[0]->Name,
                    'SN' => $searched_SN[0]->SN,
                    'OutletID' => $outlet[0]->ID,
                    'OutletName' => $outlet[0]->Name,
                    'RegDate' => $RegDate,
                    'ItemID' => $item[0]->ID,
                    'ItemDesc' => $item[0]->Description,
                    'InctvStatus' => 0,
                    'Status' => 1,
                    'DealerID' => $searched_SN[0]->BizcardID
                );

                $result = $this->Services_model->postSalesOut($data);

                if($result)
                {
                    $this->response([[
                        'status' => TRUE,
                        'message' => 'SN submitted'
                    ]], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
                }
                else
                {
                    $this->response([[
                        'status' => FALSE,
                        'message' => 'Failed to submit SN'
                    ]]);
                }
            }
            else
            {
                $this->response([[
                    'status' => FALSE,
                    'message' => 'SN not found'
                ]]);
            }
        }
        else
        {
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        }
        
    }

    public function profile_get()
    {
        $IDCR = $this->get('idCR');

        if($IDCR)
        {
            $user = $this->Services_model->getProfile($IDCR);

            if($user)
            {
                $this->response($user, REST_Controller::HTTP_OK);
            } 
            else
            {
                $this->response([[
                    'status' => FALSE,
                    'message' => 'No user was found'
                ]]);
            }
        }
        else
        {
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function profile_post()
    {
        $IDCR = $this->post('idCR');
        $namaCR = $this->post('namaCR');
        $nohp = $this->post('nomorhp');
        $email = $this->post('email');
        $namabank = $this->post('namabank');
        $namaakun = $this->post('namaakun');
        $noakun = $this->post('nomorakun');
        $password = $this->post('password');

        if($IDCR && $namaCR && $nohp && $email && $namabank && $namaakun && $noakun)
        {
            $data = array(
                'Name' => $namaCR,
                'Handphone' => $nohp,
                'Email' => $email,
                'BankName' => $namabank,
                'BankAccountName' => $namaakun,
                'BankAccountNo' => $noakun
            );

            $result = $this->Services_model->updateUser($IDCR, NULL, $data);

            if($result)
            {
                $this->response([[
                    'status' => TRUE,
                    'message' => 'Profile updated'
                ]], REST_Controller::HTTP_CREATED);
            }
            else
            {
                $this->response([[
                    'status' => FALSE,
                    'message' => 'Failed to update profile'
                ]]);
            }
        }
        elseif($IDCR && $password)
        {
            $result = $this->Services_model->updateUser($IDCR, $password, NULL);

            if($result)
            {
                $this->response([[
                    'status' => TRUE,
                    'message' => 'Password updated'
                ]], REST_Controller::HTTP_CREATED);
            }
            else
            {
                $this->response([[
                    'status' => FALSE,
                    'message' => 'Failed to update password'
                ]]);
            }
        }
        else
        {
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function login_post()
    {
        $username = $this->post('username');
        $password = $this->post('password');

        if($username && $password)
        {
            $user = $this->Services_model->getUser(0, $username, $password);

            if($user)
            {
                $SourceID = $user[0]->SourceID;
                if($SourceID)
                {
                    $this->response([[
                        'status' => TRUE,
                        'message' => 'Login succeeded',
                        'ID' => $SourceID
                    ]], REST_Controller::HTTP_OK);
                }
                else
                {
                    $this->response([[
                        'status' => FALSE,
                        'message' => 'You are not authorized'
                    ]]);
                }   
            }
            else
            {
                $this->response([[
                    'status' => FALSE,
                    'message' => 'Wrong username or password'
                ]]);
            }
        }
        else
        {
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function checkin_post()
    {
        $IDCR = $this->post('idCR');
        $IDOutlet = $this->post('idoutlet');
        $CheckInDate = $this->post('datetime');

        if($IDCR && $IDOutlet && $CheckInDate)
        {
            $result = $this->Services_model->updateCR($IDCR, $IDOutlet, $CheckInDate);

            if($result)
            {
                $this->response([[
                    'status' => TRUE,
                    'message' => 'Check in succeeded'
                ]], REST_Controller::HTTP_CREATED);
            }
            else
            {
                $this->response([[
                    'status' => FALSE,
                    'message' => 'Failed to check in'
                ]]);
            }
        }
        else
        {
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    
}