<?php
/* 
 * The MIT License (MIT)
 * 
 * Copyright (c) 2014 Anthony Neal Schneider Jr
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

//It's recommended to protect roomdata_cache.json with a .htaccess

class PlugData
{
	function __construct($email, $password) {
		if (file_exists('roomdata_cache.json')) {
			$this->cache = json_decode(file_get_contents('roomdata_cache.json'), true);
		} else {
			$this->cache = array();
		}
		
		$this->email = $email;
		$this->password = $password;
	}
	
	function _doLogin() {
		$request = curl_init();
		curl_setopt($request, CURLOPT_URL, 'https://plug.dj/');
		curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($request, CURLOPT_HEADER, 1);
		$result = curl_exec($request);
		
		preg_match('/^Set-Cookie: session=([0-9a-fA-F-|]+);/mi', $result, $match);
		$this->cache['session'] = $match[1];
		
		preg_match('/_csrf = "([0-9a-fA-F]+)"/', $result, $match);
		$csrf = $match[1];
		
		if ($csrf) {
			curl_setopt($request, CURLOPT_URL, 'https://plug.dj/_/auth/login');
			curl_setopt($request, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Cookie: session=' . $this->cache['session']));
			
			$postdata = array(
				'csrf'=>$csrf,
				'email'=>$this->email,
				'password'=>$this->password
			);
			
			curl_setopt($request,CURLOPT_POST, true);
			curl_setopt($request,CURLOPT_POSTFIELDS, json_encode($postdata));
			
			$result = curl_exec($request);
			
			if (curl_getinfo($request, CURLINFO_HTTP_CODE) == 200) return true;
		}
		return false;
	}
	
	function _fetchData($slug) {
		$request = curl_init();
		curl_setopt($request, CURLOPT_URL, 'https://plug.dj/_/rooms/favorites');
		curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($request, CURLOPT_COOKIE, 'session=' . $this->cache['session']);
		$result = curl_exec($request);
		
		$this->cache['rooms'][$slug]['time'] = time();
		
		if (curl_getinfo($request, CURLINFO_HTTP_CODE) == 200) {
			$json = json_decode($result, true);
			
			foreach ($json['data'] as $room) {
				if ($room['slug'] == $slug) {
					unset($room['favorite']);
					
					$this->cache['rooms'][$slug]['data'] = $room;
					return true;
				}
			}
		}
		return false;
	}
	
	function getRoomData($slug) {
		if (isset($this->cache['rooms'][$slug]['time']) && time() - $this->cache['rooms'][$slug]['time'] < 90)
			return array('data'=>(isset($this->cache['rooms'][$slug]['data']) ? $this->cache['rooms'][$slug]['data'] : null), 'success'=>(isset($this->cache['rooms'][$slug]['success']) ? $this->cache['rooms'][$slug]['success'] : false));
		
		$this->cache['rooms'][$slug]['success'] = false;
		
		if (isset($this->cache['session']) && $this->_fetchData($slug)) {
			$this->cache['rooms'][$slug]['success'] = true;
		} else {
			if ($this->_doLogin()) {
				if ($this->_fetchData($slug)) {
					$this->cache['rooms'][$slug]['success'] = true;
				}
			}
		}
		
		file_put_contents('roomdata_cache.json', json_encode($this->cache));
		return array('data'=>(isset($this->cache['rooms'][$slug]['data']) ? $this->cache['rooms'][$slug]['data'] : null), 'success'=>(isset($this->cache['rooms'][$slug]['success']) ? $this->cache['rooms'][$slug]['success'] : false));
	}
}
$plugdata = new PlugData('admin@swordling.com', 'charli27');
header('Content-Type: application/json');
print json_encode($plugdata->getRoomData('friendshipismagic'));
?>