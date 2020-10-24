<?php
	class gcmsCache {
		protected $cache_used = false;
		protected $cache_file;
		protected $cache_dir;
		protected $cache_expire;
		protected $cache_error;
		// $expire ช่วงเลาการเก็บ cache
		public function __construct($dir, $expire = 10) {
			if (gcms::testDir($dir)) {
				$this->cache_dir = $dir;
				$this->cache_expire = (int)$expire;
			} else {
				$this->cache_dir = false;
				$this->cache_expire = 0;
			}
			// clear old cache every day
			$d = is_file($dir.'index.php') ? file_get_contents($dir.'index.php') : 0;
			if ($d != date('d')) {
				$this->clear();
				$f = @fopen($dir.'index.php', 'wb');
				if ($f) {
					fwrite($f, date('d'));
					fclose($f);
				} else {
					$this->cache_error = 'CACHE_INDEX_READONLY';
				}
			}
		}
		// $key ชื่อของ cache
		public function get($key) {
			if ($this->cache_dir == false || $this->cache_expire == 0) {
				return false;
			} else {
				$file = $this->cache_dir.md5($key).'.php';
				if (file_exists($file) && filemtime($file) > (time() - $this->cache_expire)) {
					$this->cache_used = true;
					return unserialize(preg_replace('/^<\?php\sexit\?>(.*)$/isu', '\\1', file_get_contents($file)));
				} else {
					return false;
				}
			}
		}
		// กำหนดเวลาหมดอายุของ cache
		public function set_expire($value) {
			$this->cache_expire = $value;
		}
		// คืนค่า true ถ้ากำลังใช้งาน cache อยู่
		public function is_cache() {
			return $this->cache_used;
		}
		// $key ชื่อของ cache
		// $data ข้อมูล (array)
		public function save($key, $datas) {
			if ($this->cache_dir == false || $this->cache_expire == 0) {
				return false;
			} else {
				$file = $this->cache_dir.md5($key).'.php';
				$f = @fopen($file, 'wb');
				if ($f) {
					fwrite($f, '<?php exit?>'.serialize($datas));
					fclose($f);
					return true;
				} else {
					$this->cache_error = 'CACHE_DIRECTORY_PREMISSION';
					return false;
				}
			}
		}
		// ลบแคช
		public function remove($key) {
			if ($this->cache_dir == false || $this->cache_expire == 0) {
				return false;
			} else {
				$file = $this->cache_dir.md5($key).'.php';
				if (is_file($file)) {
					return @unlink($file);
				} else {
					return true;
				}
			}
		}
		// ลบไฟล์ทั้งหมดในไดเร็คทอรี่ (cache)
		private function _clear($dir, &$error) {
			$f = @opendir($dir);
			if ($f) {
				while (false !== ($text = readdir($f))) {
					if ($text != "." && $text != ".." && $text != 'index.php') {
						if (is_dir($dir.$text)) {
							$this->_clear($dir.$text.'/', $error);
						} elseif (!@unlink($dir.$text)) {
							$error[] = $dir.$text;
						}
					}
				}
				closedir($f);
			}
		}
		// ลบแคชทั้งไดเร็คทอรี่
		public function clear() {
			if ($this->cache_dir == false || $this->cache_expire == 0) {
				return false;
			} else {
				$error = array();
				$this->_clear($this->cache_dir, $error);
				return sizeof($error) == 0 ? true : $error;
			}
		}
		// error
		public function Error() {
			return $this->cache_error;
		}
	}
