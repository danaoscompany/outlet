<?php

class Admin extends CI_Controller {
	
	public function login() {
		$email = $this->input->post('email');
		$password = $this->input->post('password');
		$admins = $this->db->query("SELECT * FROM `admins` WHERE `email`='" . $email . "'")->result_array();
		if (sizeof($admins) > 0) {
			$admin = $admins[0];
			if (password_verify($password, $admin['password'])) {
				echo json_encode(array(
					'response_code' => 1,
					'id' => intval($admin['id'])
				));
			} else {
				echo json_encode(array(
					'response_code' => -1
				));
			}
		} else {
			echo json_encode(array(
				'response_code' => -2
			));
		}
	}
}
