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
            if($lat != NULL && $lng != NULL)
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

        if($IDCR != NULL && $DateFrom != NULL && $DateTo != NULL)
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

        if($IDCR != NULL && $date != NULL)
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

        if($IDCR != NULL && $SN != NULL && $IDOutlet != NULL && $RegDate != NULL){

            $searched_SN = $this->Services_model->getSN($SN);

            if($searched_SN)
            {
                $item = $this->Services_model->getItem($searched_SN[0]->ItemID);

                $data = array(
                    'CreateUserID' => $IDCR,
                    'SN' => $SN,
                    'OutletID' => $IDOutlet,
                    'RegDate' => $RegDate,
                    'ItemID' => $item[0]->ID,
                    'ItemDesc' => $item[0]->Description,
                    'InctvStatus' => 0,
                    'Status' => 1,
                    'DealerID' => 1
                );

                $result = $this->Services_model->postSalesOut($data);

                if($result)
                {
                    $update = $this->Services_model->updateSN($SN, 0);

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
                    'message' => 'SN not found or already submitted'
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

        if($username != NULL && $password != NULL)
        {
            $user = $this->Services_model->getUser($username, $password);

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

        if($IDCR != NULL && $IDOutlet != NULL && $CheckInDate != NULL)
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