<?php
/**
 * Regression tests for getHash() and the OpenSSL replacement for the removed mcrypt login decryption.
 *
 * @package    Logiks
 * @subpackage Tests
 * @version    6.0
 */

use PHPUnit\Framework\TestCase;

class LegacyFixesTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		require_once ROOT . 'api/helpers/pwdhash.php';
	}

	// ---- getHash ----

	public function testGetHashUsesItsInputNotAnUndefinedVariable() {
		$this->assertSame(md5('secret'), getHash('secret', 'md5'));
		$this->assertSame(sha1('secret'), getHash('secret', 'sha1'));
		$this->assertNotSame(getHash('a', 'md5'), getHash('b', 'md5'), 'different inputs must give different hashes');
	}

	public function testGetHashSaltedMethodsDependOnInput() {
		foreach (['method1', 'blowfish', 'sha256', 'des'] as $method) {
			$this->assertNotSame(getHash('a', $method), getHash('b', $method), $method);
			$this->assertSame(getHash('a', $method), getHash('a', $method), $method . ' must be deterministic');
		}
	}

	// ---- decryptLoginPassword ----

	/** Encrypts the way the legacy client did: AES-CBC, zero padding, base64. */
	protected function legacyEncrypt(string $plain, string $keyHex, string $ivHex): string {
		$key = pack('H*', $keyHex);
		$iv = pack('H*', $ivHex);
		$plain .= str_repeat("\0", (16 - strlen($plain) % 16) % 16);
		return base64_encode(openssl_encrypt($plain, 'aes-' . (strlen($key) * 8) . '-cbc', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv));
	}

	public function testDecryptsLegacyClientPayload() {
		$keyHex = bin2hex(random_bytes(16));
		$ivHex = bin2hex(random_bytes(16));
		foreach (['p@ss', 'a much longer passphrase 123!', str_repeat('x', 32)] as $pwd) {
			$this->assertSame($pwd, decryptLoginPassword($this->legacyEncrypt($pwd, $keyHex, $ivHex), $keyHex, $ivHex));
		}
	}

	public function testDecryptsWithLongerKeys() {
		$ivHex = bin2hex(random_bytes(16));
		foreach ([24, 32] as $bytes) {
			$keyHex = bin2hex(random_bytes($bytes));
			$this->assertSame('hunter2', decryptLoginPassword($this->legacyEncrypt('hunter2', $keyHex, $ivHex), $keyHex, $ivHex));
		}
	}

	public function testBadInputIsRejectedNotMisread() {
		$keyHex = bin2hex(random_bytes(16));
		$ivHex = bin2hex(random_bytes(16));
		$good = $this->legacyEncrypt('pw', $keyHex, $ivHex);

		$this->assertFalse(decryptLoginPassword('not base64 !!!', $keyHex, $ivHex));
		$this->assertFalse(decryptLoginPassword($good, 'zz', $ivHex), 'non-hex key');
		$this->assertFalse(decryptLoginPassword($good, $keyHex, 'abcd'), 'iv of the wrong length');
		$this->assertFalse(decryptLoginPassword(base64_encode('short'), $keyHex, $ivHex), 'not a block multiple');
		$this->assertFalse(decryptLoginPassword($good, bin2hex(random_bytes(40)), $ivHex), 'key too long');
		$this->assertNotSame('pw', decryptLoginPassword($good, bin2hex(random_bytes(16)), $ivHex), 'wrong key must not yield the password');
	}
}
