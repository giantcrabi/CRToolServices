<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Services_model extends CI_Model {
	public function __construct()
    {
        $this->load->database('default');
    }

    function getUser($username, $password)
    {
        $result = $this->db->get_where('dbo.mUser', array('userid' => $username, 'password' => $password))->result();

        return $result;
    }

    function getOutlet($IDOutlet = NULL, $lat = NULL, $lng = NULL) {
    	if($IDOutlet === NULL || $IDOutlet == 0)
    	{
            if($lat != NULL && $lng != NULL)
            {
                $result = $this->db->query("WITH GreatCircleDistance AS
                (
                    SELECT ID, Code, Name, ( 6371 * acos( cos( radians(".$lat.") ) * cos( radians( Lat ) ) 
                    * cos( radians( Lng ) - radians(".$lng.") ) + sin( radians(".$lat.") ) * sin(radians(Lat)) ) ) AS distance
                    FROM dbo.mOutlet
                )
                SELECT ID, Code, Name
                FROM GreatCircleDistance
                WHERE distance < 0.5")->result();
            }
            else
            {
                $result = $this->db->get('dbo.mOutlet')->result();
            }
    	}
        else
        {
        	$result = $this->db->get_where('dbo.mOutlet', array('ID' => $IDOutlet))->result();
        }

        return $result;
    }

    function getReport($IDCR, $DateFrom, $DateTo) {
        $result = $this->db->query("
            SELECT SN, OutletName, RegDate, ItemDesc, InctvStatus
            FROM dbo.tSNRegistration
            WHERE RegDate >= "."'".$DateFrom."'"." AND RegDate <= "."'".$DateTo."'"." AND CreateUserID = ".$IDCR."")->result();

        return $result;
    }

    function getAchievement($IDCR, $date) {
        $result = $this->db->query("
            SELECT SalesInPrice, RegDate, InctvStatus
            FROM dbo.tSNRegistration
            WHERE RegDate >= DATEADD(month, -6, "."'".$date."'".") AND RegDate <= "."'".$date."'"." AND CreateUserID = ".$IDCR."")->result();

        return $result;
    }

    function getSN($SN) {
        $result = $this->db->get_where('dbo.vSN', array('SN' => $SN, 'SalesOutStatus' => NULL))->result();

        return $result;
    }

    function getItem($ItemID) {
        $result = $this->db->get_where('dbo.vItem', array('ID' => $ItemID))->result();

        return $result;
    }

    function postSalesOut($data) 
    {
        if($this->db->insert('dbo.tSNRegistration', $data))
        {
            return true;
        }
    }

    function updateCR($IDCR, $IDOutlet, $CheckInDate)
    {
        $result = $this->db->query("
            UPDATE dbo.mCR
            SET CheckInPlace=".$IDOutlet.", CheckInDate="."'".$CheckInDate."'"."
            WHERE ID=".$IDCR."");

        if($result)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

}