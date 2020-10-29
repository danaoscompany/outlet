<?php

class User extends CI_Controller {
	
	public function login() {
		$email = $this->input->post('email');
		$password = $this->input->post('password');
		$users = $this->db->query("SELECT * FROM `users` WHERE `email`='" . $email . "'")->result_array();
		if (sizeof($users) > 0) {
			$user = $users[0];
			if (password_verify($password, $user['password'])) {
				$user['response_code'] = 1;
				$user['role'] = 'user';
				echo json_encode($user);
			} else {
				echo json_encode(array(
					'response_code' => -1
				));
			}
		} else {
			$admins = $this->db->query("SELECT * FROM `admins` WHERE `email`='" . $email . "'")->result_array();
			if (sizeof($admins) > 0) {
				$admin = $admins[0];
				if (password_verify($password, $admin['password'])) {
					$admin['response_code'] = 1;
					$admin['role'] = 'admin';
					echo json_encode($admin);
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
	
	public function give_attendance() {
		$userID = intval($this->input->post('user_id'));
		$lat = doubleval($this->input->post('lat'));
		$lng = doubleval($this->input->post('lng'));
		$date = $this->input->post('date');
		$config['upload_path'] = './userdata/';
		$config['allowed_types'] = '*';
		$config['max_size']     = '2048000';
		$config['max_width'] = '5000';
		$config['max_height'] = '5000';
		$this->load->library('upload', $config);
		if ($this->upload->do_upload('file')) {
			$this->db->insert('attendances', array(
				'user_id' => $userID,
				'image' => $this->upload->data()['file_name'],
				'lat' => $lat,
				'lng' => $lng,
				'date' => $date
			));
			echo json_encode(array('response_code' => 1));
		} else {
			echo json_encode(array('response_code' => -1));
		}
	}
	
	public function has_user_given_attendance() {
		$userID = intval($this->input->post('user_id'));
		$date = $this->input->post('date');
		$users = $this->db->query("SELECT * FROM `attendances` WHERE `user_id`=" . $userID . " AND DATE(`date`)='" . $date . "'")->result_array();
		if (sizeof($users) > 0) {
			echo 1;
		} else {
			echo 0;
		}
	}
	
	public function get_divisions() {
		echo json_encode($this->db->query("SELECT * FROM `divisions` ORDER BY `name` ASC")->result_array());
	}
	
	public function get_outlets_by_division_uuid() {
		$uuid = $this->input->post('uuid');
		echo json_encode($this->db->query("SELECT * FROM `outlets` WHERE `division_uuid`='" . $uuid . "' ORDER BY `name` ASC")->result_array());
	}
	
	public function get_outlet_users() {
		$outletUUID = $this->input->post('uuid');
		$users = $this->db->query("SELECT * FROM `users` WHERE `outlet_uuid`='" . $outletUUID . "' ORDER BY `name` ASC")->result_array();
		for ($i=0; $i<sizeof($users); $i++) {
			$userID = intval($users[$i]['id']);
			$attendances = $this->db->query("SELECT * FROM `attendances` WHERE `user_id`=" . $userID . " ORDER BY `date` DESC LIMIT 1")->result_array();
			if (sizeof($attendances) > 0) {
				$users[$i]['last_attendance_date'] = $attendances[0]['date'];
				$users[$i]['last_attendance_image'] = $attendances[0]['image'];
			}
		}
		echo json_encode($users);
	}
}
