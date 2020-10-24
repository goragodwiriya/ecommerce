<?php
	// class ftp โดย http://www.goragod.com (กรกฎ วิริยะ)
	// สงวนลิขสิทธ์ ห้ามซื้อขาย ให้นำไปใช้ได้ฟรีเท่านั้น
	class ftp {
		protected $connection;
		protected $host;
		protected $username;
		protected $password;
		protected $port;
		protected $ftp_absolute_path;
		// inintial class
		public function __construct($host = 'localhost', $username = 'Anonymous', $password = 'admin@localhost', $ftproot, $docroot, $port = '21') {
			$this->host = $host;
			$this->ftp_absolute_path = $ftproot;
			$this->username = $username;
			$this->password = $password;
			$this->port = $port;
			$this->connection = false;
		}
		// login และ คืนค่า connection
		public function connect() {
			return $this->login();
		}
		// destroy class
		public function __destruct() {
			@ftp_close($this->connection);
		}
		// Tempraly close ftp
		public function close() {
			@ftp_close($this->connection);
			$this->connection = false;
		}
		// login
		public function login() {
			if (function_exists('ftp_connect')) {
				if (!$this->connection) {
					$stream = @ftp_connect($this->host, $this->port, 10);
					if ($stream) {
						$login = @ftp_login($stream, $this->username, $this->password);
						if ($login) {
							$this->connection = $stream;
							return true;
						} else {
							ftp_close($stream);
						}
					}
				} else {
					return true;
				}
			}
			return false;
		}
		// Moves an uploaded file to a new location
		public function move_uploaded_file($filename, $destination) {
			return $this->rename($filename, $destination);
		}
		// Makes a copy of the file source to dest.
		public function copy($source, $dest) {
			if (!is_file($dest)) {
				$chk = dirname($dest);
				if (!is_writable($chk)) {
					$chk = '';
				}
			} elseif (!is_writable($dest)) {
				$chk = $dest;
			}
			if ($chk != '') {
				$chmod = fileperms($chk);
				$this->chmod($chk, 0757);
			}
			$f = @copy($source, $dest);
			if ($chk != '') {
				$this->chmod($chk, $chmod);
			}
			return $f;
		}
		// Download file from remote file to local file
		public function download($remote_file, $local_file, $mode = FTP_BINARY) {
			if ($this->login()) {
				return ftp_get($this->connection, $local_file, $remote_file, $mode);
			}
			return false;
		}
		// Upload file to ftp
		public function put($remote_file, $local_file, $mode = FTP_BINARY) {
			if ($this->login()) {
				return ftp_put($this->connection, $remote_file, $local_file, $mode);
			}
			return false;
		}
		// Writes the contents of string to the file
		public function fwrite($file, $mode, $string) {
			if (!is_file($file)) {
				$chk = dirname($file);
				if (is_writable($chk)) {
					$chk = '';
				}
			} elseif (!is_writable($file)) {
				$chk = $file;
			}
			if ($chk != '') {
				$chmod = fileperms($chk);
				$this->chmod($chk, 0757);
			}
			$f = @fopen($file, $mode);
			if ($f) {
				fwrite($f, $string);
				fclose($f);
			}
			if ($chk != '') {
				$this->chmod($chk, $chmod);
			}
			return $f;
		}
		// Read entry from directory
		public function readdir($dir = '.') {
			if ($this->login()) {
				return ftp_nlist($this->connection, $dir);
			}
			return false;
		}
		// Returns the current directory name
		public function getcwd() {
			if ($this->login()) {
				return ftp_pwd($this->connection);
			}
			return false;
		}
		// Creates the specified directory on the FTP server.
		function mkdir($dir, $mode = 0755) {
			if (!is_dir($dir)) {
				$pdir = dirname($dir);
				if (!is_writeable($pdir)) {
					$chmod = @fileperms($pdir);
					$this->chmod($pdir, 0757);
				} else {
					$chmod = 0;
				}
				$f = @mkdir($dir, $mode);
				if ($chmod > 0) {
					$this->chmod($pdir, $chmod);
				}
				return $f;
			} else {
				return $this->chmod($dir, $mode);
			}
		}
		// Tells whether the given filename is a directory.
		function is_dir($dir) {
			if ($this->login() && @ftp_chdir($this->connection, $dir)) {
				ftp_chdir($this->connection, '..');
				return true;
			} else {
				return false;
			}
		}
		// file or folder writeable
		function is_writeable($dir) {
			if (is_writeable($dir)) {
				return true;
			} else {
				$this->chmod($dir, 0755);
				return is_writeable($dir);
			}
		}
		// renames a file or a directory on the FTP server.
		function rename($old_file, $new_file) {
			if (!is_file($new_file)) {
				$chk = dirname($new_file);
				if (!is_writable($chk)) {
					$chk = '';
				}
			} elseif (!is_writable($new_file)) {
				$chk = $new_file;
			}
			if ($chk != '') {
				$chmod = fileperms($chk);
				$this->chmod($chk, 0757);
			}
			$f = @rename($old_file, $new_file);
			if (!$f && $this->login()) {
				$f = @ftp_rename($this->connection, $old_file, $new_file);
			}
			if ($chk != '') {
				$this->chmod($chk, $chmod);
			}
			return $f;
		}
		// Gets the size for the given file.
		function filesize($file) {
			$socket = fsockopen($this->host, $this->port);
			$t = fgets($socket, 128);
			fwrite($socket, "USER $this->username\r\n");
			$t = fgets($socket, 128);
			fwrite($socket, "PASS $this->password\r\n");
			$t = fgets($socket, 128);
			fwrite($socket, "SIZE $file\r\n");
			$t = fgets($socket, 128);
			if (preg_match('/^213\s(.*)$/', $t, $match)) {
				$size = floatval($match[1]);
			} else {
				$size = -1;
			}
			fwrite($socket, "QUIT\r\n");
			fclose($socket);
			return $size;
		}
		// Sets the permissions on the specified remote file to mode.
		function chmod($file, $mode) {
			if (!@chmod($file, $mode)) {
				if ($this->login()) {
					return @ftp_chmod($this->connection, $mode, $this->ftp_file($file));
				}
				return false;
			}
			return true;
		}
		// deletes the file specified by path from the FTP server.
		function unlink($path) {
			if (is_file($path)) {
				if (!@unlink($path)) {
					if ($this->login()) {
						return ftp_delete($this->connection, $this->ftp_file($path));
					}
					return false;
				}
			}
			return true;
		}
		// Removes the specified directory on the FTP server.
		function _rmdir($dir) {
			if (is_dir($dir)) {
				if (!@rmdir($dir)) {
					if ($this->login()) {
						return @ftp_rmdir($this->connection, $this->ftp_file($dir));
					}
					return false;
				}
			}
			return true;
		}
		// Remove directory and all contents
		function rmdir($dir) {
			if (is_dir($dir)) {
				$f = opendir($dir);
				while (false !== ($text = readdir($f))) {
					if ($text != '.' && $text != '..') {
						if (is_dir($dir.$text.'/')) {
							$this->rmdir($dir.$text.'/');
							$this->_rmdir($dir.$text.'/');
						} else {
							$this->unlink($dir.$text);
						}
					}
				}
				closedir($f);
				$this->_rmdir($dir);
			}
		}
		// get ftp path
		function ftp_file($file) {
			list($a, $b) = explode($this->ftp_absolute_path, $file);
			return $this->ftp_absolute_path.$b;
		}
	}
